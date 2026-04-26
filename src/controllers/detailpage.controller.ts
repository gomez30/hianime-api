import { Context } from 'hono';
import { extractDetailpage } from '../extractor/extractDetailpage';
import { axiosInstance } from '../services/axiosInstance';
import { AppError, validationError } from '../utils/errors';
import { DetailAnime } from '../types/anime';

const detailpageController = async (c: Context): Promise<DetailAnime> => {
  const id = c.req.param('id');

  const result = await axiosInstance(`/anime/${id}`, { timeoutMs: 30000, retries: 1 });
  if (!result.success || !result.data) {
    throw new AppError(result.message || 'Failed to fetch detail page', 502, {
      id,
      ...((result.details as Record<string, unknown>) ?? {}),
    });
  }
  return extractDetailpage(result.data, `/anime/${id}`);
};

export default detailpageController;
