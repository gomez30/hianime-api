import { Context } from 'hono';
import { axiosInstance } from '../services/axiosInstance';
import { AppError, validationError } from '../utils/errors';
import * as cheerio from 'cheerio';

const randomController = async (_c: Context): Promise<{ id: string }> => {
  console.log('Fetching random anime...');
  const result = await axiosInstance('/');

  if (!result.success || !result.data) {
    console.error('Random anime fetch failed:', result.message);
    throw new AppError(
      result.message || 'Failed to fetch homepage for random selection',
      502,
      result.details ?? null
    );
  }

  const $ = cheerio.load(result.data);

  const animes: string[] = [];
  $('.flw-item').each((i, el) => {
    const link = $(el).find('.film-name a').attr('href');
    const id = link?.split('/').filter(Boolean).pop();
    if (id) animes.push(id);
  });

  if (animes.length === 0) {
    throw new validationError('No anime found');
  }

  const randomId = animes[Math.floor(Math.random() * animes.length)];

  return { id: randomId };
};

export default randomController;
