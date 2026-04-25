export function extractAnimeId(url: string): string {
  if (!url) return '';
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
