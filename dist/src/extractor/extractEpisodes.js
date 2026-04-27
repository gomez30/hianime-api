import { load } from 'cheerio';
const parseEpisodeNumber = (title, fallback) => {
    if (!title)
        return fallback;
    const match = title.match(/(?:episode|ep)\s*\.?\s*(\d+)/i) || title.match(/\b(\d+)\b/);
    return Number(match?.[1]) || fallback;
};
export const extractEpisodes = (html) => {
    const $ = load(html);
    const response = [];
    const seenIds = new Set();
    const selectors = ['.ssl-item.ep-item', 'a.ep-item', '.ss-list a[href*="/watch/"]'];
    $(selectors.join(',')).each((i, el) => {
        const href = $(el).attr('href') || null;
        const rawId = href ? href.replace(/^\/watch\//i, '').replace(/^watch\//i, '') : null;
        if (!rawId || seenIds.has(rawId))
            return;
        seenIds.add(rawId);
        const title = $(el).attr('title') || $(el).find('.ep-name').text() || null;
        const parsedEpisodeNumber = parseEpisodeNumber(title || undefined, i + 1);
        const obj = {
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
