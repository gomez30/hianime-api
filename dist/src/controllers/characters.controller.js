import { AppError, validationError } from '../utils/errors';
import { extractCharacters } from '../extractor/extractCharacters';
import { axiosInstance } from '../services/axiosInstance';
const charactersController = async (c) => {
    try {
        const id = c.req.param('id');
        if (!id)
            throw new validationError('id is required');
        // Character block is available in anime detail HTML on current upstream.
        const result = await axiosInstance(`/anime/${id}`);
        if (!result.success || !result.data) {
            throw new AppError(result.message || 'Failed to fetch characters', 502, result.details ?? null);
        }
        const response = extractCharacters(result.data);
        return response;
    }
    catch (err) {
        if (err instanceof Error) {
            console.log(err.message);
        }
        else {
            console.log(err);
        }
        throw new AppError('characters not found', 502);
    }
};
export default charactersController;
