import { extractNextEpisodeSchedule } from '../extractor/extractNextEpisodeSchedule';
import { axiosInstance } from '../services/axiosInstance';
import { AppError, validationError } from '../utils/errors';
const nextEpisodeSchaduleController = async (c) => {
    const id = c.req.param('id');
    if (!id)
        throw new validationError('id is required');
    const data = await axiosInstance('/anime/' + id);
    if (!data.success || !data.data)
        throw new AppError(data.message || 'Failed to fetch next episode schedule', 502, data.details);
    const response = extractNextEpisodeSchedule(data.data);
    return response;
};
export default nextEpisodeSchaduleController;
