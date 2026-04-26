import { Context } from 'hono';
import { AppError, validationError } from '../utils/errors';
import { extractCharacters, CharactersResponse } from '../extractor/extractCharacters';
import { axiosInstance } from '../services/axiosInstance';

const charactersController = async (c: Context): Promise<CharactersResponse> => {
  try {
    const id = c.req.param('id');
    if (!id) throw new validationError('id is required');

    // Character block is available in anime detail HTML on current upstream.
    const result = await axiosInstance(`/anime/${id}`);

    if (!result.success || !result.data) {
      throw new AppError(result.message || 'Failed to fetch characters', 502, result.details ?? null);
    }

    const response = extractCharacters(result.data);
    if (!response.response.length) {
      throw new AppError(
        'Characters data is unavailable from current upstream HTML/API for this title',
        502,
        { id }
      );
    }

    return response;
  } catch (err: unknown) {
    if (err instanceof Error) {
      console.log(err.message);
    } else {
      console.log(err);
    }

    throw new AppError('characters not found', 502);
  }
};

export default charactersController;
