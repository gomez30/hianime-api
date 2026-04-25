import { axiosInstance } from '../services/axiosInstance';
import { validationError } from '../utils/errors';
import { extractHomepage } from '../extractor/extractHomepage';
const homepageController = async () => {
    console.log('Fetching homepage data from external API...');
    const result = await axiosInstance('/home');
    if (!result.success || !result.data) {
        console.error('Homepage fetch failed:', result.message);
        throw new validationError(result.message || 'Failed to fetch homepage');
    }
    return extractHomepage(result.data);
};
export default homepageController;
