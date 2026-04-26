import { extractCharacterDetail } from '../extractor/extractCharacterDetail';
import { axiosInstance } from '../services/axiosInstance';
import { AppError, validationError } from '../utils/errors';
const characterDetailConroller = async (c) => {
    const id = c.req.param('id');
    if (!id)
        throw new validationError('id is required');
    const result = await axiosInstance(`/character/${id.replace(':', '/')}`);
    if (!result.success || !result.data) {
        throw new AppError(result.message || 'Failed to fetch character detail', 502, result.details ?? null);
    }
    const response = extractCharacterDetail(result.data);
    return response;
};
export default characterDetailConroller;
