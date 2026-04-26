import { AppError, validationError } from '../utils/errors';
import { extractEpisodes } from '../extractor/extractEpisodes';
import { axiosInstance } from '../services/axiosInstance';
import { extractAnimeInternalIdFromHtml } from '../utils/helpers';
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
    const internalId = extractAnimeInternalIdFromHtml(detailResult.data);
    if (!internalId) {
        throw new AppError('Failed to resolve upstream anime internal id for episode list', 502, {
            id,
        });
    }
    const episodesResult = await axiosInstance(`/wp-json/v1/episode/list/${internalId}`, {
        headers: { Accept: 'application/json, text/plain, */*' },
    });
    if (!episodesResult.success || !episodesResult.data) {
        throw new AppError(episodesResult.message || 'Failed to fetch episode list', 502, {
            id,
            internalId,
            ...(episodesResult.details ?? {}),
        });
    }
    let episodesHtml = episodesResult.data;
    try {
        const parsed = JSON.parse(episodesResult.data);
        if (parsed?.html)
            episodesHtml = parsed.html;
    }
    catch {
        // Keep raw HTML fallback.
    }
    const response = extractEpisodes(episodesHtml);
    return response;
};
export default episodesController;
