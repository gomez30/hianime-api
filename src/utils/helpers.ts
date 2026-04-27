import { load } from 'cheerio';

const isEpisodePath = (path: string): boolean => {
  return /\/watch\//i.test(path) || /-episode-\d+/i.test(path) || /-ep-\d+/i.test(path);
};

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
  const path = cleaned.startsWith('http') ? new URL(cleaned).pathname : cleaned;
  if (!isEpisodePath(path)) return slug;

  return slug.replace(/-episode-\d+.*$/i, '').replace(/-ep-\d+.*$/i, '').replace(/-english-(sub|dub).*$/i, '');
}

export function extractAnimeInternalIdCandidatesFromHtml(html: string): string[] {
  if (!html) return [];

  const candidateScores = new Map<string, number>();
  const addCandidate = (candidate: string | undefined, score: number) => {
    if (!candidate) return;
    const existing = candidateScores.get(candidate) || 0;
    candidateScores.set(candidate, existing + score);
  };

  const $ = load(html);

  // Prefer IDs in the hero/detail region.
  $('.anisc-detail [data-id], .anisc-poster[data-id], #ani_detail [data-id], .anis-content [data-id]').each((_, el) => {
    const id = $(el).attr('data-id');
    if (id) addCandidate(id, 100);
  });

  // Common poster/tooltip patterns used by upstream templates.
  $('.film-poster.item-qtip[data-id], .film-poster[data-id], .anisc-poster[data-id], [data-id][class*="item-qtip"]').each(
    (_, el) => {
      const id = $(el).attr('data-id');
      if (id) addCandidate(id, 60);
    }
  );

  // Generic fallback for any numeric data-id.
  $('[data-id]').each((_, el) => {
    const id = $(el).attr('data-id');
    if (id && /^\d+$/.test(id)) addCandidate(id, 10);
  });

  // Regex fallback for layout drifts where classes move around.
  const explicitAnimeIdMatch = [...html.matchAll(/\banime_id\s*[:=]\s*['"]?(\d+)/gi)];
  for (const match of explicitAnimeIdMatch) addCandidate(match[1], 300);

  const directMainPosterMatch = html.match(/class=["'][^"']*anisc-poster[^"']*["'][\s\S]*?data-id=["'](\d+)["']/i);
  addCandidate(directMainPosterMatch?.[1], 120);

  const fallbackPosterMatch = html.match(/class=["'][^"']*film-poster[^"']*item-qtip[^"']*["'][\s\S]*?data-id=["'](\d+)["']/i);
  addCandidate(fallbackPosterMatch?.[1], 80);

  return [...candidateScores.entries()]
    .sort((a, b) => b[1] - a[1])
    .map(([id]) => id);
}

export function extractAnimeInternalIdFromHtml(html: string): string | null {
  if (!html) return null;
  return extractAnimeInternalIdCandidatesFromHtml(html)[0] || null;
}
