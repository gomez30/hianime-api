import { describe, expect, it, vi, Mock } from 'vitest';
import { Context } from 'hono';
import episodesController from '../../../../src/controllers/episodes.controller';

vi.mock('../../../../src/services/axiosInstance', () => ({
  axiosInstance: vi.fn(),
}));

import { axiosInstance } from '../../../../src/services/axiosInstance';

const createMockContext = (params: Record<string, string>) =>
  ({
    req: {
      param: (name?: string) => (name ? params[name] : params),
      query: () => undefined,
    },
    json: vi.fn(data => data),
  }) as unknown as Context;

describe('episodesController regression', () => {
  it('chooses best internal id candidate using slug match and count', async () => {
    (axiosInstance as Mock).mockResolvedValueOnce({
      success: true,
      data: `
        <a href="/anime/naruto">Naruto</a>
      `,
    });

    (axiosInstance as Mock).mockResolvedValueOnce({
      success: true,
      data: `
        <div class="flw-item">
          <h3 class="film-name">
            <a class="dynamic-name" href="/anime/naruto">Naruto</a>
          </h3>
        </div>
      `,
    });

    (axiosInstance as Mock).mockResolvedValueOnce({
      success: true,
      data: `
        <div id="ani_detail">
          <div class="anisc-detail">
            <div class="anisc-poster" data-id="1000"></div>
          </div>
        </div>
        <div class="related">
          <a class="film-poster item-qtip" data-id="2000"></a>
        </div>
      `,
    });

    (axiosInstance as Mock).mockResolvedValueOnce({
      success: true,
      data: `
        <a class="ssl-item ep-item" href="/watch/other-anime?ep=1" title="Episode 1"></a>
        <a class="ssl-item ep-item" href="/watch/other-anime?ep=2" title="Episode 2"></a>
      `,
    });

    (axiosInstance as Mock).mockResolvedValueOnce({
      success: true,
      data: `
        <a class="ssl-item ep-item" href="/watch/naruto-episode-1-english-subbed?ep=1" title="Episode 1"></a>
        <a class="ssl-item ep-item" href="/watch/naruto-episode-2-english-subbed?ep=2" title="Episode 2"></a>
        <a class="ssl-item ep-item" href="/watch/naruto-episode-3-english-subbed?ep=3" title="Episode 3"></a>
      `,
    });

    const response = await episodesController(createMockContext({ id: 'naruto' }));
    expect(response).toHaveLength(3);
    expect(response[0].id).toContain('naruto-episode-1');
  });

  it('uses episode-page fallback when anime detail candidates are low confidence', async () => {
    (axiosInstance as Mock)
      // getAnimeSlugCandidates search
      .mockResolvedValueOnce({
        success: true,
        data: `<a href="/anime/naruto">Naruto</a>`,
      })
      // getAnimeSlugCandidates filter
      .mockResolvedValueOnce({
        success: true,
        data: `<div class="flw-item"><h3 class="film-name"><a href="/anime/naruto">Naruto</a></h3></div>`,
      })
      // /anime/naruto detail
      .mockResolvedValueOnce({
        success: true,
        data: `<div class="anisc-poster" data-id="1000"></div>`,
      })
      // episode list for 1000 (low confidence unrelated)
      .mockResolvedValueOnce({
        success: true,
        data: `
          <a class="ssl-item ep-item" href="/watch/other-anime?ep=1" title="Episode 1"></a>
          <a class="ssl-item ep-item" href="/watch/other-anime?ep=2" title="Episode 2"></a>
        `,
      })
      // fallback search for episode pages
      .mockResolvedValueOnce({
        success: true,
        data: `<a href="/naruto-episode-220-english-subbed/">Naruto ep</a>`,
      })
      // episode page detail includes strong anime_id
      .mockResolvedValueOnce({
        success: true,
        data: `var anime_id = 6698; <div class="anisc-poster" data-id="6698"></div>`,
      })
      // episode list for 6698
      .mockResolvedValueOnce({
        success: true,
        data: `
          <a class="ssl-item ep-item" href="/watch/naruto-episode-219-english-subbed?ep=219" title="Episode 219"></a>
          <a class="ssl-item ep-item" href="/watch/naruto-episode-220-english-subbed?ep=220" title="Episode 220"></a>
        `,
      });

    const response = await episodesController(createMockContext({ id: 'naruto' }));
    expect(response).toHaveLength(2);
    expect(response[1].id).toContain('naruto-episode-220');
  });
});
