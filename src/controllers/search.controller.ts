import { Context } from 'hono';
import { extractListPage, ListPageResponse } from '../extractor/extractListpage';
import { axiosInstance } from '../services/axiosInstance';
import { AppError, NotFoundError, validationError } from '../utils/errors';
import { load } from 'cheerio';
import { getCache, setCache } from '../utils/cache';

const SEARCH_CACHE_TTL_MS = Number(process.env.SEARCH_CACHE_TTL_MS) || 3 * 60 * 1000;

const tokenize = (value: string): string[] =>
  value
    .toLowerCase()
    .split(/[^a-z0-9]+/g)
    .map(v => v.trim())
    .filter(Boolean);

const scoreAnime = (keyword: string, title: string | null, id: string | null): number => {
  const normalizedKeyword = keyword.trim().toLowerCase();
  const normalizedTitle = (title || '').trim().toLowerCase();
  const normalizedId = (id || '').trim().toLowerCase();
  if (!normalizedKeyword) return 0;

  let score = 0;
  if (normalizedTitle === normalizedKeyword) score += 1000;
  if (normalizedId === normalizedKeyword) score += 900;
  if (normalizedTitle.includes(normalizedKeyword)) score += 500;
  if (normalizedId.includes(normalizedKeyword)) score += 400;

  const queryTokens = tokenize(normalizedKeyword);
  const titleTokens = new Set(tokenize(normalizedTitle));
  const idTokens = new Set(tokenize(normalizedId));
  for (const token of queryTokens) {
    if (titleTokens.has(token)) score += 120;
    if (idTokens.has(token)) score += 80;
    if (normalizedTitle.includes(token)) score += 25;
    if (normalizedId.includes(token)) score += 15;
  }

  return score;
};

const filterAndRank = (keyword: string, payload: ListPageResponse): ListPageResponse => {
  const ranked = payload.response
    .map(anime => ({
      anime,
      score: scoreAnime(keyword, anime.title, anime.id),
    }))
    .filter(item => item.score > 0)
    .sort((a, b) => b.score - a.score)
    .map(item => item.anime);

  return {
    ...payload,
    response: ranked,
  };
};

const scrapeSearchFallback = async (keyword: string, page: string): Promise<ListPageResponse | null> => {
  const endpoint = `/?s=${encodeURIComponent(keyword)}&page=${page}`;
  const result = await axiosInstance(endpoint, { timeoutMs: 30000, retries: 1 });
  if (!result.success || !result.data) return null;

  const html = result.data;
  const $ = load(html);
  const response = extractListPage(html);

  // If extractor returned empty on search page due to layout variance, perform a lightweight fallback extraction.
  if (response.response.length === 0) {
    const lightweight = $('.flw-item')
      .map((_, el) => {
        const titleEl = $(el).find('.film-name a').first();
        const href = titleEl.attr('href') || '';
        const id = href.includes('/anime/') ? href.split('/anime/').pop()?.split('/')[0] || null : null;
        return {
          title: (titleEl.text() || '').trim() || null,
          alternativeTitle: titleEl.attr('data-jname') || null,
          id,
          poster: $(el).find('.film-poster img').attr('data-src') || null,
          type: ($(el).find('.fd-infor .fdi-item').first().text() || '').trim() || null,
          duration: ($(el).find('.fd-infor .fdi-duration').text() || '').trim() || null,
          episodes: {
            sub: Number($(el).find('.tick-sub').text()) || null,
            dub: Number($(el).find('.tick-dub').text()) || null,
            eps: Number($(el).find('.tick-eps').text()) || Number($(el).find('.tick-sub').text()) || null,
          },
        };
      })
      .get();

    return {
      pageInfo: {
        currentPage: Number(page) || 1,
        hasNextPage: false,
        totalPages: 1,
      },
      response: lightweight,
      top10: { today: [], week: [], month: [] },
      genres: [],
    };
  }

  return response;
};

const searchController = async (c: Context): Promise<ListPageResponse> => {
  const keyword = c.req.query('keyword') || null;
  const page = c.req.query('page') || '1';

  if (!keyword) throw new validationError('query is required');

  const noSpaceKeyword = keyword.trim().toLowerCase().replace(/\s+/g, '+');
  const cacheKey = `search:${noSpaceKeyword}:${page}`;
  const cached = getCache<ListPageResponse>(cacheKey);
  if (cached) return cached;

  // Primary path: /filter search page on upstream
  const endpoint = `/filter?keyword=${noSpaceKeyword}&page=${page}`;
  const result = await axiosInstance(endpoint, { timeoutMs: 30000, retries: 1 });

  let response: ListPageResponse | null = null;

  if (result.success && result.data) {
    response = extractListPage(result.data);
  }

  let ranked = response ? filterAndRank(keyword, response) : null;

  // Controlled fallback path when primary result is empty/low confidence.
  if (!ranked || ranked.response.length === 0) {
    const fallback = await scrapeSearchFallback(keyword, page);
    ranked = fallback ? filterAndRank(keyword, fallback) : null;
  }

  if (!ranked || ranked.response.length < 1) {
    if (!result.success) {
      throw new AppError(result.message || 'Failed to fetch search page', 502, result.details ?? null);
    }
    throw new NotFoundError('page not found');
  }

  setCache(cacheKey, ranked, SEARCH_CACHE_TTL_MS);
  return ranked;
};

export default searchController;
