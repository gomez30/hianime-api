import { describe, expect, it } from 'vitest';
import { extractAnimeId, extractAnimeInternalIdCandidatesFromHtml } from '../../../../src/utils/helpers';
import { extractEpisodes } from '../../../../src/extractor/extractEpisodes';

describe('Aniwatch regression extractors', () => {
  it('keeps modern anime slugs intact', () => {
    expect(extractAnimeId('/anime/one-piece-vss')).toBe('one-piece-vss');
    expect(extractAnimeId('https://aniwatch.co.at/anime/one-piece-vss/')).toBe('one-piece-vss');
  });

  it('extracts anime slug from episode/watch URLs only when needed', () => {
    expect(extractAnimeId('/watch/naruto-episode-220-english-subbed?ep=1')).toBe('naruto');
    expect(extractAnimeId('/watch/my-hero-academia-vigilantes-19544?ep=136197')).toBe(
      'my-hero-academia-vigilantes-19544'
    );
  });

  it('returns ordered internal id candidates with hero-biased scoring', () => {
    const html = `
      <div class="related">
        <a class="film-poster item-qtip" data-id="111"></a>
      </div>
      <div id="ani_detail">
        <div class="anisc-detail">
          <div class="anisc-poster" data-id="999"></div>
        </div>
      </div>
    `;
    const candidates = extractAnimeInternalIdCandidatesFromHtml(html);
    expect(candidates[0]).toBe('999');
    expect(candidates).toContain('111');
  });

  it('preserves raw watch ids and legacy ids in episode parser', () => {
    const html = `
      <a class="ssl-item ep-item" href="/watch/naruto-episode-220-english-subbed?ep=220" title="Episode 220">
        <span class="ep-name e-dynamic-name" data-jname="Naruto 220"></span>
      </a>
    `;
    const result = extractEpisodes(html);
    expect(result).toHaveLength(1);
    expect(result[0].id).toBe('naruto-episode-220-english-subbed?ep=220');
    expect(result[0].legacyId).toBe('naruto-episode-220-english-subbed::ep=220');
    expect(result[0].episodeNumber).toBe(220);
  });
});
