import { load } from 'cheerio';
import { extractAnimeId } from '../utils/helpers';
export const extractSuggestions = (html) => {
    const $ = load(html);
    const response = [];
    const allEl = $('.nav-item');
    const items = allEl.toArray().splice(0, allEl.length - 2);
    $(items).each((i, el) => {
        const obj = {
            title: null,
            alternativeTitle: null,
            poster: null,
            id: null,
            aired: null,
            type: null,
            duration: null,
        };
        const href = $(el).attr('href') || '';
        obj.id = extractAnimeId(href) || null;
        obj.poster = $(el).find('.film-poster img').attr('data-src') || null;
        const titleEL = $(el).find('.film-name');
        obj.title = titleEL.text() || null;
        obj.alternativeTitle = titleEL.attr('data-jname') || null;
        const infoEl = $(el).find('.film-infor');
        obj.aired = infoEl.find('span').first().text() || null;
        obj.type = infoEl
            .contents()
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            .filter((i, el) => {
            return el.type === 'text' && $(el).text().trim() !== '';
        })
            .text()
            .trim();
        obj.duration = infoEl.find('span').last().text() || null;
        response.push(obj);
    });
    return response;
};
