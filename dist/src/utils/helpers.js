export function extractAnimeId(url) {
    if (!url)
        return '';
    const cleaned = url.replace(/\/$/, '');
    if (url.includes('/anime/')) {
        return cleaned.split('/').filter(Boolean).pop() || '';
    }
    const slug = cleaned.split('/').filter(Boolean).pop() || '';
    return slug.replace(/-episode-\d+.*$/, '');
}
