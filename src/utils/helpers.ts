export function extractAnimeId(url: string): string {
  if (!url) return '';
  const cleaned = url.replace(/\/$/, '');
  if (url.includes('/anime/')) {
    return cleaned.split('/').filter(Boolean).pop() || '';
  }
  const slug = cleaned.split('/').filter(Boolean).pop() || '';
  return slug.replace(/-episode-\d+.*$/, '');
}
