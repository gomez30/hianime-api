import * as cheerio from 'cheerio';
export const extractTopSearch = (html) => {
    const $ = cheerio.load(html);
    const topSearch = [];
    $('.xhashtag .item').each((i, el) => {
        const link = $(el).attr('href') || null;
        const id = link ? link.split('/').pop()?.split('?')[0] || null : null;
        topSearch.push({
            title: $(el).text().trim() || null,
            link: link,
            id: id,
        });
    });
    return topSearch;
};
