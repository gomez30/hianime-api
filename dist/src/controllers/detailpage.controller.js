import { extractDetailpage } from '../extractor/extractDetailpage';
import { axiosInstance } from '../services/axiosInstance';
import { validationError } from '../utils/errors';
const detailpageController = async (c) => {
    const id = c.req.param('id');
    const result = await axiosInstance(`/${id}`);
    if (!result.success || !result.data) {
        throw new validationError(result.message || 'Failed to fetch detail page', 'maybe id is incorrect : ' + id);
    }
    return extractDetailpage(result.data);
};
export default detailpageController;
