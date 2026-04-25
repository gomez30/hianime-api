import { axiosInstance } from '../services/axiosInstance';
import { AppError } from '../utils/errors';
import { extractHomepage } from '../extractor/extractHomepage';
import { HomePage } from '../types/anime';

const HOMEPAGE_CACHE_TTL_MS = Number(process.env.HOMEPAGE_CACHE_TTL_MS) || 5 * 60 * 1000;
let cachedHomepage: { data: HomePage; createdAt: number } | null = null;

const getCachedHomepage = (): HomePage | null => {
  if (!cachedHomepage) return null;

  const ageMs = Date.now() - cachedHomepage.createdAt;
  if (ageMs > HOMEPAGE_CACHE_TTL_MS) {
    cachedHomepage = null;
    return null;
  }

  return cachedHomepage.data;
};

const homepageController = async (): Promise<HomePage> => {
  const startedAt = Date.now();
  console.log('Fetching homepage data from external API...');
  const result = await axiosInstance('/home');

  if (!result.success) {
    console.error('Homepage fetch failed:', result.message);
    const staleData = getCachedHomepage();
    if (staleData) {
      console.warn('Returning stale homepage cache due to upstream fetch failure');
      return staleData;
    }
    throw new AppError(result.message || 'Failed to fetch homepage', 502, result.details ?? null);
  }

  const parseStartedAt = Date.now();
  const parsed = extractHomepage(result.data);
  const parseDurationMs = Date.now() - parseStartedAt;
  const totalDurationMs = Date.now() - startedAt;

  const hasAnyData =
    parsed.spotlight.length > 0 ||
    parsed.trending.length > 0 ||
    parsed.topAiring.length > 0 ||
    parsed.mostPopular.length > 0 ||
    parsed.mostFavorite.length > 0 ||
    parsed.latestCompleted.length > 0 ||
    parsed.latestEpisode.length > 0 ||
    parsed.newAdded.length > 0 ||
    parsed.topUpcoming.length > 0 ||
    parsed.genres.length > 0 ||
    (parsed.top10.today?.length ?? 0) > 0 ||
    (parsed.top10.week?.length ?? 0) > 0 ||
    (parsed.top10.month?.length ?? 0) > 0;

  console.log(
    `Homepage scrape timings: total=${totalDurationMs}ms, upstream=${result.meta.durationMs}ms, parse=${parseDurationMs}ms`
  );

  if (!hasAnyData) {
    const staleData = getCachedHomepage();
    if (staleData) {
      console.warn('Returning stale homepage cache due to empty upstream parse');
      return staleData;
    }

    throw new AppError('Upstream HTML structure changed or response was blocked', 502, {
      endpoint: '/home',
      upstreamMeta: result.meta,
      parseDurationMs,
      totalDurationMs,
    });
  }

  cachedHomepage = {
    data: parsed,
    createdAt: Date.now(),
  };

  return parsed;
};

export default homepageController;
