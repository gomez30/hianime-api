import { load } from 'cheerio';

export interface Episode {
  title: string | null;
  alternativeTitle: string | null;
  id: string | null;
  rawId: string | null;
  legacyId: string | null;
  isFiller: boolean;
  episodeNumber: number;
}

const parseEpisodeNumber = (title: string | undefined, fallback: number): number => {
  if (!title) return fallback;
  const match = title.match(/(?:episode|ep)\s*\.?\s*(\d+)/i) || title.match(/\b(\d+)\b/);
  return Number(match?.[1]) || fallback;
};

export const extractEpisodes = (html: string): Episode[] => {
  const $ = load(html);

  const response: Episode[] = [];
  const seenIds = new Set<string>();
  const selectors = ['.ssl-item.ep-item', 'a.ep-item', '.ss-list a[href*="/watch/"]'];

  $(selectors.join(',')).each((i, el) => {
    const href = $(el).attr('href') || null;
    const rawId = href ? href.replace(/^\/watch\//i, '').replace(/^watch\//i, '') : null;
    if (!rawId || seenIds.has(rawId)) return;
    seenIds.add(rawId);

    const title = $(el).attr('title') || $(el).find('.ep-name').text() || null;
    const parsedEpisodeNumber = parseEpisodeNumber(title || undefined, i + 1);
    const obj: Episode = {
      title,
      alternativeTitle: null,
      id: rawId,
      rawId,
      legacyId: rawId.replace('?', '::'),
      isFiller: false,
      episodeNumber: parsedEpisodeNumber,
    };
    obj.isFiller = $(el).hasClass('ssl-item-filler');

    obj.alternativeTitle =
      $(el).find('.ep-name.e-dynamic-name').attr('data-jname') || $(el).find('.ep-name').attr('data-jname') || null;

    response.push(obj);
  });
  return response;
};
