import { AppError, validationError } from '../utils/errors';
import { extractSuggestions } from '../extractor/extractSuggestions';
import { axiosInstance } from '../services/axiosInstance';
const suggestionController = async (c) => {
    const keyword = c.req.query('keyword') || null;
    if (!keyword)
        throw new validationError('query is required');
    const noSpaceKeyword = keyword.trim().toLowerCase().replace(/\s+/g, '+');
    const endpoint = `/wp-json/hianime/v1/search/suggestions?keyword=${noSpaceKeyword}`;
    const result = await axiosInstance(endpoint, {
        headers: { Accept: 'application/json, text/plain, */*' },
    });
    if (!result.success || !result.data) {
        throw new AppError(result.message || 'suggestion not found', 502, result.details ?? null);
    }
    let html = result.data;
    try {
        const parsed = JSON.parse(result.data);
        if (parsed?.html)
            html = parsed.html;
    }
    catch {
        // keep plain html fallback
    }
    const response = extractSuggestions(html).slice(0, 12);
    return response;
};
export default suggestionController;
