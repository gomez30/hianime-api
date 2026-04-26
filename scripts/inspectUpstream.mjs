const url = process.argv[2] || 'https://aniwatch.co.at/anime/one-piece-vss';

const response = await fetch(url, {
  headers: {
    'user-agent': 'Mozilla/5.0',
    accept: 'text/html,*/*',
  },
});
const html = await response.text();

const ajaxMatches = [...html.matchAll(/\/ajax\/[^"' ]+/g)].map(m => m[0]);
const watchMatches = [...html.matchAll(/\/watch\/[^"' ]+/g)].map(m => m[0]);
const characterMatches = [...html.matchAll(/\/character\/[^"' ]+/g)].map(m => m[0]);
const apiUrlMatches = [...html.matchAll(/https?:\/\/[^"' ]+/g)]
  .map(m => m[0])
  .filter(v => v.includes('wp-json') || v.includes('graphql') || v.includes('/api/'));
const idPatternMatches = {
  animeId: [...html.matchAll(/anime_id\s*[:=]\s*['"]?(\d+)/gi)].map(m => m[1]),
  dataId: [...html.matchAll(/data-id=['"](\d+)['"]/gi)].map(m => m[1]),
  postId: [...html.matchAll(/postid['"]?\s*[:=]\s*['"]?(\d+)/gi)].map(m => m[1]),
  classPostId: [...html.matchAll(/postid-(\d+)/gi)].map(m => m[1]),
  idJson: [...html.matchAll(/"id":(\d+)/gi)].map(m => m[1]).slice(0, 20),
  episodeListId: [...html.matchAll(/episode\/list\/(\d+)/gi)].map(m => m[1]),
};

console.log(
  JSON.stringify(
    {
      url,
      status: response.status,
      length: html.length,
      ajaxMatches: [...new Set(ajaxMatches)].slice(0, 30),
      watchMatches: [...new Set(watchMatches)].slice(0, 30),
      characterMatches: [...new Set(characterMatches)].slice(0, 30),
      apiUrlMatches: [...new Set(apiUrlMatches)].slice(0, 30),
      idPatternMatches,
      hasAniscDetail: html.includes('anisc-detail'),
      hasEpisodeWord: /episode/i.test(html),
      hasCharacterWord: /character/i.test(html),
    },
    null,
    2
  )
);
