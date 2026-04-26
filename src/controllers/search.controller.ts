import { Context } from 'hono';
import { extractListPage, ListPageResponse } from '../extractor/extractListpage';
import { axiosInstance } from '../services/axiosInstance';
import { AppError, NotFoundError, validationError } from '../utils/errors';

const searchController = async (c: Context): Promise<ListPageResponse> => {
  const keyword = c.req.query('keyword') || null;
  const page = c.req.query('page') || '1';

  if (!keyword) throw new validationError('query is required');

  const noSpaceKeyword = keyword.trim().toLowerCase().replace(/\s+/g, '+');

  // New upstream now serves keyword search through /filter instead of /search
  const endpoint = `/filter?keyword=${noSpaceKeyword}&page=${page}`;
  const result = await axiosInstance(endpoint, { timeoutMs: 30000, retries: 1 });

  if (!result.success || !result.data) {
    throw new AppError(result.message || 'Failed to fetch search page', 502, result.details ?? null);
  }

  const response = extractListPage(result.data);

  if (response.response.length < 1) {
    throw new NotFoundError('page not found');
  }

  return response;
};

export default searchController;
