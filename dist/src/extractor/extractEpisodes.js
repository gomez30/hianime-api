import { load } from 'cheerio';
export const extractEpisodes = (html) => {
    const $ = load(html);
    const response = [];
    $('.ssl-item.ep-item').each((i, el) => {
        const obj = {
            title: null,
            alternativeTitle: null,
            id: null,
            isFiller: false,
            episodeNumber: i + 1,
        };
        obj.title = $(el).attr('title') || null;
        obj.id = $(el).attr('href')?.replace('/watch/', '').replace('?', '::') || null;
        obj.isFiller = $(el).hasClass('ssl-item-filler');
        obj.alternativeTitle = $(el).find('.ep-name.e-dynamic-name').attr('data-jname') || null;
        response.push(obj);
    });
    return response;
};
