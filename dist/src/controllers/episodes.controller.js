import { AppError, validationError } from '../utils/errors';
import { extractEpisodes } from '../extractor/extractEpisodes';
import { axiosInstance } from '../services/axiosInstance';
import { extractAnimeInternalIdCandidatesFromHtml, extractAnimeInternalIdFromHtml } from '../utils/helpers';
const normalizeSlug = (value) => value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-');
const countSlugMatches = (episodes, requestedId) => {
    if (!episodes.length)
        return 0;
    const normalizedRequested = normalizeSlug(requestedId);
    return episodes.reduce((acc, episode) => {
        const raw = (episode.rawId || episode.id || '').toLowerCase();
        return raw.includes(normalizedRequested) ? acc + 1 : acc;
    }, 0);
};
const fetchEpisodeListByInternalId = async (internalId) => {
    const episodesResult = await axiosInstance(`/wp-json/v1/episode/list/${internalId}`, {
        headers: { Accept: 'application/json, text/plain, */*' },
        expectHtml: false,
        timeoutMs: 30000,
        retries: 1,
    });
    if (!episodesResult.success || !episodesResult.data)
        return [];
    let episodesHtml = episodesResult.data;
    try {
        const parsed = JSON.parse(episodesResult.data);
        if (parsed?.html)
            episodesHtml = parsed.html;
    }
    catch {
        // Keep raw HTML fallback.
    }
    return extractEpisodes(episodesHtml);
};
const episodesController = async (c) => {
    const id = c.req.param('id');
    if (!id)
        throw new validationError('id is required');
    const detailResult = await axiosInstance(`/anime/${id}`);
    if (!detailResult.success || !detailResult.data) {
        throw new AppError(detailResult.message || 'Failed to fetch anime page for episodes', 502, {
            validIdEX: 'one-piece-100',
            ...(detailResult.details ?? {}),
        });
    }
    const internalIdCandidates = extractAnimeInternalIdCandidatesFromHtml(detailResult.data);
    const fallbackInternalId = extractAnimeInternalIdFromHtml(detailResult.data);
    if (fallbackInternalId && !internalIdCandidates.includes(fallbackInternalId)) {
        internalIdCandidates.push(fallbackInternalId);
    }
    if (!internalIdCandidates.length) {
        throw new AppError('Failed to resolve upstream anime internal id for episode list', 502, {
            id,
        });
    }
    const maxCandidatesToProbe = 5;
    const candidateResults = [];
    const triedIds = [];
    for (const candidateId of internalIdCandidates.slice(0, maxCandidatesToProbe)) {
        triedIds.push(candidateId);
        const episodes = await fetchEpisodeListByInternalId(candidateId);
        if (!episodes.length)
            continue;
        candidateResults.push({
            internalId: candidateId,
            episodes,
            slugMatches: countSlugMatches(episodes, id),
        });
    }
    if (!candidateResults.length) {
        throw new AppError('Failed to fetch episode list', 502, {
            id,
            triedInternalIds: triedIds,
        });
    }
    candidateResults.sort((a, b) => {
        if (b.slugMatches !== a.slugMatches)
            return b.slugMatches - a.slugMatches;
        return b.episodes.length - a.episodes.length;
    });
    return candidateResults[0].episodes;
};
export default episodesController;
