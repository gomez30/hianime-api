import { Context } from 'hono';
import { AppError, validationError } from '../utils/errors';
import { extractEpisodes, Episode } from '../extractor/extractEpisodes';
import { axiosInstance } from '../services/axiosInstance';
import { extractAnimeInternalIdCandidatesFromHtml, extractAnimeInternalIdFromHtml, extractAnimeId } from '../utils/helpers';
import { extractListPage } from '../extractor/extractListpage';

type EpisodeCandidate = {
  internalId: string;
  episodes: Episode[];
  slugMatches: number;
  resolvedSlug: string;
  source: 'anime-detail' | 'episode-page';
};

const normalizeSlug = (value: string): string => value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-');
const tokenize = (value: string): string[] =>
  normalizeSlug(value)
    .split('-')
    .map(v => v.trim())
    .filter(Boolean);

const isEpisodeLikeSlug = (slug: string): boolean =>
  /-episode-\d+/i.test(slug) || /-english-(sub|dub)/i.test(slug) || /-ep-\d+/i.test(slug);

const scoreSlugSimilarity = (requested: string, candidate: string): number => {
  const req = normalizeSlug(requested);
  const cand = normalizeSlug(candidate);
  if (!req || !cand) return 0;
  if (req === cand) return 1000;

  const reqTokens = tokenize(req);
  const candTokens = new Set(tokenize(cand));
  let score = 0;

  for (const token of reqTokens) {
    if (candTokens.has(token)) score += 90;
    else if (cand.includes(token)) score += 20;
  }

  if (cand.includes(req)) score += 250;
  if (isEpisodeLikeSlug(cand)) score -= 300;
  return score;
};

const countSlugMatches = (episodes: Episode[], requestedId: string): number => {
  if (!episodes.length) return 0;
  const normalizedRequested = normalizeSlug(requestedId);
  return episodes.reduce((acc, episode) => {
    const raw = (episode.rawId || episode.id || '').toLowerCase();
    return raw.includes(normalizedRequested) ? acc + 1 : acc;
  }, 0);
};

const getAnimeSlugCandidates = async (requestedId: string): Promise<string[]> => {
  const normalizedKeyword = requestedId.trim().toLowerCase().replace(/\s+/g, '+');
  const candidates = new Set<string>([requestedId]);

  const addAnimeLinksFromHtml = (html: string) => {
    for (const match of html.matchAll(/href=["']([^"']*\/anime\/[^"']+)["']/gi)) {
      const slug = extractAnimeId(match[1]);
      if (slug && !isEpisodeLikeSlug(slug)) candidates.add(slug);
    }
  };

  const searchResult = await axiosInstance(`/?s=${encodeURIComponent(requestedId)}&page=1`, {
    timeoutMs: 30000,
    retries: 1,
  });
  if (searchResult.success && searchResult.data) {
    addAnimeLinksFromHtml(searchResult.data);
  }

  const filterResult = await axiosInstance(`/filter?keyword=${normalizedKeyword}&page=1`, {
    timeoutMs: 30000,
    retries: 1,
  });
  if (filterResult.success && filterResult.data) {
    addAnimeLinksFromHtml(filterResult.data);
    const parsed = extractListPage(filterResult.data);
    for (const item of parsed.response.slice(0, 5)) {
      if (!item.id) continue;
      const slug = extractAnimeId(item.id);
      if (slug && !isEpisodeLikeSlug(slug)) candidates.add(slug);
    }
  }

  return [...candidates]
    .filter(Boolean)
    .sort((a, b) => scoreSlugSimilarity(requestedId, b) - scoreSlugSimilarity(requestedId, a))
    .slice(0, 10);
};

type EpisodePageCandidate = {
  pagePath: string;
  derivedSlug: string;
  episodeNumber: number;
};

const getEpisodePageCandidates = (html: string, requestedId: string): EpisodePageCandidate[] => {
  const requested = normalizeSlug(requestedId);
  const candidates = new Map<string, EpisodePageCandidate>();

  for (const match of html.matchAll(/href=["']([^"']+)["']/gi)) {
    const href = match[1];
    const withoutOrigin = href.replace(/^https?:\/\/[^/]+/i, '');
    const episodeMatch = withoutOrigin.match(/\/([^/"']+)-episode-(\d+)[^/"']*\/?$/i);
    if (!episodeMatch?.[1] || !episodeMatch?.[2]) continue;

    const derivedSlug = normalizeSlug(episodeMatch[1]);
    const episodeNumber = Number(episodeMatch[2]) || 0;
    if (!derivedSlug || !episodeNumber) continue;

    // Require at least one token overlap to avoid broad noisy matches.
    const overlap = tokenize(requested).some(token => derivedSlug.includes(token));
    if (!overlap) continue;

    const path = withoutOrigin.startsWith('/') ? withoutOrigin : `/${withoutOrigin}`;
    const existing = candidates.get(derivedSlug);
    if (!existing || episodeNumber > existing.episodeNumber) {
      candidates.set(derivedSlug, { pagePath: path, derivedSlug, episodeNumber });
    }
  }

  return [...candidates.values()].sort((a, b) => b.episodeNumber - a.episodeNumber).slice(0, 5);
};

const fetchEpisodeListByInternalId = async (internalId: string): Promise<Episode[]> => {
  const episodesResult = await axiosInstance(`/wp-json/v1/episode/list/${internalId}`, {
    headers: { Accept: 'application/json, text/plain, */*' },
    expectHtml: false,
    timeoutMs: 30000,
    retries: 1,
  });

  if (!episodesResult.success || !episodesResult.data) return [];

  let episodesHtml = episodesResult.data;
  try {
    const parsed = JSON.parse(episodesResult.data) as { html?: string };
    if (parsed?.html) episodesHtml = parsed.html;
  } catch {
    // Keep raw HTML fallback.
  }

  return extractEpisodes(episodesHtml);
};

const episodesController = async (c: Context): Promise<Episode[]> => {
  const id = c.req.param('id');

  if (!id) throw new validationError('id is required');

  const animeSlugCandidates = await getAnimeSlugCandidates(id);
  const maxCandidatesToProbe = 5;
  const candidateResults: EpisodeCandidate[] = [];
  const triedIds: string[] = [];
  const triedEpisodePages: string[] = [];

  for (const animeSlug of animeSlugCandidates) {
    const detailResult = await axiosInstance(`/anime/${animeSlug}`);
    if (!detailResult.success || !detailResult.data) continue;

    const internalIdCandidates = extractAnimeInternalIdCandidatesFromHtml(detailResult.data);
    const fallbackInternalId = extractAnimeInternalIdFromHtml(detailResult.data);
    if (fallbackInternalId && !internalIdCandidates.includes(fallbackInternalId)) {
      internalIdCandidates.push(fallbackInternalId);
    }

    for (const candidateId of internalIdCandidates.slice(0, maxCandidatesToProbe)) {
      triedIds.push(candidateId);
      const episodes = await fetchEpisodeListByInternalId(candidateId);
      if (!episodes.length) continue;
      candidateResults.push({
        internalId: candidateId,
        episodes,
        slugMatches: countSlugMatches(episodes, animeSlug),
        resolvedSlug: animeSlug,
        source: 'anime-detail',
      });
    }

    // Stop early when we already found a candidate that strongly matches requested anime slug.
    if (candidateResults.some(result => result.slugMatches > 0)) {
      break;
    }
  }

  // Fallback: resolve anime_id from episode pages discovered via search endpoint.
  if (!candidateResults.some(result => result.slugMatches > 0)) {
    const searchResult = await axiosInstance(`/?s=${encodeURIComponent(id)}&page=1`, {
      timeoutMs: 30000,
      retries: 1,
    });
    if (searchResult.success && searchResult.data) {
      const episodePageCandidates = getEpisodePageCandidates(searchResult.data, id);
      for (const pageCandidate of episodePageCandidates) {
        triedEpisodePages.push(pageCandidate.pagePath);
        const pageResult = await axiosInstance(pageCandidate.pagePath, {
          timeoutMs: 30000,
          retries: 1,
        });
        if (!pageResult.success || !pageResult.data) continue;

        const internalIdCandidates = extractAnimeInternalIdCandidatesFromHtml(pageResult.data).slice(0, maxCandidatesToProbe);
        for (const candidateId of internalIdCandidates) {
          triedIds.push(candidateId);
          const episodes = await fetchEpisodeListByInternalId(candidateId);
          if (!episodes.length) continue;
          candidateResults.push({
            internalId: candidateId,
            episodes,
            slugMatches: countSlugMatches(episodes, pageCandidate.derivedSlug),
            resolvedSlug: pageCandidate.derivedSlug,
            source: 'episode-page',
          });
        }

        if (candidateResults.some(result => result.slugMatches > 0)) break;
      }
    }
  }

  if (!candidateResults.length) {
    throw new AppError('Failed to fetch episode list', 404, {
      id,
      triedInternalIds: triedIds,
      triedAnimeSlugs: animeSlugCandidates,
      triedEpisodePages,
    });
  }

  candidateResults.sort((a, b) => {
    if (b.slugMatches !== a.slugMatches) return b.slugMatches - a.slugMatches;
    return b.episodes.length - a.episodes.length;
  });

  const best = candidateResults[0];
  const hasStrongSlugMatch = best.slugMatches > 0;
  const suspiciouslyTiny = best.episodes.length < 12;

  if (!hasStrongSlugMatch && suspiciouslyTiny) {
    throw new AppError('Ambiguous episode mapping: upstream candidates did not match requested anime confidently', 502, {
      id,
      bestCandidate: {
        internalId: best.internalId,
        source: best.source,
        resolvedSlug: best.resolvedSlug,
        episodeCount: best.episodes.length,
        slugMatches: best.slugMatches,
      },
      triedInternalIds: triedIds,
      triedAnimeSlugs: animeSlugCandidates,
      triedEpisodePages,
    });
  }

  return best.episodes;
};

export default episodesController;
