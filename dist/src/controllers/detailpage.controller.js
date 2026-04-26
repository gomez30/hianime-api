import { extractDetailpage } from '../extractor/extractDetailpage';
import { axiosInstance } from '../services/axiosInstance';
import { AppError } from '../utils/errors';
const detailpageController = async (c) => {
    const id = c.req.param('id');
    const result = await axiosInstance(`/anime/${id}`);
    if (!result.success || !result.data) {
        throw new AppError(result.message || 'Failed to fetch detail page', 502, {
            id,
            ...(result.details ?? {}),
        });
    }
    return extractDetailpage(result.data, `/anime/${id}`);
};
export default detailpageController;
