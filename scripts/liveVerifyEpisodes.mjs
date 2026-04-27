import { load } from 'cheerio';

const base = 'https://aniwatch.co.at';
const normalize = value => value.trim().toLowerCase().replace(/\s+/g, '+');

const extractAnimeId = value => {
  const withoutHash = (value || '').split('#')[0] || '';
  const withoutQuery = withoutHash.split('?')[0] || '';
  const cleaned = withoutQuery.replace(/\/+$/, '');
  if (cleaned.includes('/anime/')) return cleaned.split('/').filter(Boolean).pop() || '';
  const slug = cleaned.split('/').filter(Boolean).pop() || '';
  if (!/\/watch\//i.test(cleaned)) return slug;
  return slug.replace(/-episode-\d+.*$/i, '').replace(/-ep-\d+.*$/i, '').replace(/-english-(sub|dub).*$/i, '');
};

const pickCandidates = html => {
  const $ = load(html);
  const scores = new Map();
  const add = (id, score) => {
    if (!id || !/^\d+$/.test(id)) return;
    scores.set(id, (scores.get(id) || 0) + score);
  };

  $('.anisc-detail [data-id], .anisc-poster[data-id], #ani_detail [data-id], .anis-content [data-id]').each((_, el) =>
    add($(el).attr('data-id'), 100)
  );
  $('.film-poster.item-qtip[data-id], .film-poster[data-id], .anisc-poster[data-id], [data-id][class*=item-qtip]').each(
    (_, el) => add($(el).attr('data-id'), 60)
  );
  $('[data-id]').each((_, el) => add($(el).attr('data-id'), 10));
  for (const match of html.matchAll(/\banime_id\s*[:=]\s*['"]?(\d+)/gi)) add(match[1], 300);

  return [...scores.entries()]
    .sort((a, b) => b[1] - a[1])
    .map(([id]) => id)
    .slice(0, 5);
};

const getSlugCandidates = async requestedSlug => {
  const result = new Set([requestedSlug]);
  const res = await fetch(`${base}/filter?keyword=${normalize(requestedSlug)}&page=1`, {
    headers: { 'user-agent': 'Mozilla/5.0', accept: 'text/html,*/*' },
  });
  if (res.status >= 400) return [...result];
  const html = await res.text();
  const $ = load(html);
  $('.flw-item .film-name a').each((_, el) => {
    const id = extractAnimeId($(el).attr('href') || '');
    if (id) result.add(id);
  });
  return [...result];
};

const extractEpisodeIds = html => {
  const $ = load(html);
  const ids = [];
  const seen = new Set();
  $('.ssl-item.ep-item, a.ep-item, .ss-list a[href*="/watch/"]').each((_, el) => {
    const href = $(el).attr('href') || '';
    const rawId = href.replace(/^\/watch\//i, '').replace(/^watch\//i, '');
    if (!rawId || seen.has(rawId)) return;
    seen.add(rawId);
    ids.push(rawId);
  });
  return ids;
};

const verifyAnime = async slug => {
  const slugCandidates = await getSlugCandidates(slug);
  const candidates = [];
  const results = [];

  for (const resolvedSlug of slugCandidates) {
    const detailRes = await fetch(`${base}/anime/${resolvedSlug}`, {
      headers: { 'user-agent': 'Mozilla/5.0', accept: 'text/html,*/*' },
    });
    if (detailRes.status >= 400) continue;
    const detailHtml = await detailRes.text();
    const internalCandidates = pickCandidates(detailHtml);
    candidates.push(...internalCandidates);

    for (const internalId of internalCandidates) {
      const epRes = await fetch(`${base}/wp-json/v1/episode/list/${internalId}`, {
        headers: { accept: 'application/json,text/plain,*/*', 'user-agent': 'Mozilla/5.0' },
      });
      const text = await epRes.text();
      let html = text;
      try {
        const parsed = JSON.parse(text);
        if (parsed?.html) html = parsed.html;
      } catch {
        // Keep raw response.
      }

      const episodeIds = extractEpisodeIds(html);
      const slugMatches = episodeIds.filter(id => id.toLowerCase().includes(slug.toLowerCase())).length;
      results.push({
        resolvedSlug,
        internalId,
        episodeCount: episodeIds.length,
        slugMatches,
        firstTwo: episodeIds.slice(0, 2),
        lastOne: episodeIds.at(-1) || null,
      });
    }
  }

  return { slug, slugCandidates, candidates, results };
};

const output = {
  naruto: await verifyAnime('naruto'),
  mhaVigilantes: await verifyAnime('my-hero-academia-vigilantes-19544'),
};

console.log(JSON.stringify(output, null, 2));
