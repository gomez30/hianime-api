export function extractAnimeId(url) {
    if (!url)
        return '';
    const normalizedUrl = url.trim();
    const withoutHash = normalizedUrl.split('#')[0] || '';
    const withoutQuery = withoutHash.split('?')[0] || '';
    const cleaned = withoutQuery.replace(/\/+$/, '');
    if (cleaned.includes('/anime/')) {
        return cleaned.split('/').filter(Boolean).pop() || '';
    }
    const slug = cleaned.split('/').filter(Boolean).pop() || '';
    return slug
        .replace(/-episode-\d+.*$/i, '')
        .replace(/-ep-\d+.*$/i, '')
        .replace(/-english-(sub|dub).*$/i, '');
}
export function extractAnimeInternalIdFromHtml(html) {
    if (!html)
        return null;
    // Main anime poster block usually carries internal numeric data-id used by wp-json/v1 APIs.
    const directMainPosterMatch = html.match(/class=["'][^"']*anisc-poster[^"']*["'][\s\S]*?data-id=["'](\d+)["']/i);
    if (directMainPosterMatch?.[1])
        return directMainPosterMatch[1];
    const fallbackPosterMatch = html.match(/class=["'][^"']*film-poster[^"']*item-qtip[^"']*["'][\s\S]*?data-id=["'](\d+)["']/i);
    if (fallbackPosterMatch?.[1])
        return fallbackPosterMatch[1];
    return null;
}
