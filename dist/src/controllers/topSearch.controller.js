import { axiosInstance } from '../services/axiosInstance';
import { validationError } from '../utils/errors';
import { extractTopSearch } from '../extractor/extractTopSearch';
const topSearchController = async (_c) => {
    console.log('Fetching top search data from external API...');
    const result = await axiosInstance('/');
    if (!result.success || !result.data) {
        console.error('Top search fetch failed:', result.message);
        throw new validationError(result.message || 'Failed to fetch top search');
    }
    return extractTopSearch(result.data);
};
export default topSearchController;
