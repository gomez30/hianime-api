# Endpoint Matrix

| Endpoint | Controller | Upstream target pattern | Extractor | Expected shape |
| --- | --- | --- | --- | --- |
| `/api/v2/home` | `homepage.controller.ts` | `/` | `extractHomepage` | `HomePage` object |
| `/api/v2/top-search` | `topSearch.controller.ts` | `/` | `extractTopSearch` | `TopSearchAnime[]` |
| `/api/v2/search` | `search.controller.ts` | `/filter?keyword=:q&page=:page` | `extractListPage` | `ListPageResponse` |
| `/api/v2/anime/:id` | `detailpage.controller.ts` | `/anime/:id` | `extractDetailpage` | `DetailAnime` |
| `/api/v2/episodes/:id` | `episodes.controller.ts` | `/anime/:id` | `extractEpisodes` | `Episode[]` |
| `/api/v2/characters/:id` | `characters.controller.ts` | `/anime/:id` | `extractCharacters` | `CharactersResponse` |
| `/api/v2/character/:id` | `characterDetail.controller.ts` | `/character/:id` | `extractCharacterDetail` | `CharacterDetail` |
| `/api/v2/suggestion` | `suggestion.controller.ts` | `/filter?keyword=:q&page=1` | `extractSuggestions` | `Suggestion[]` |
| `/api/v2/filter` | `filter.controller.ts` | `/filter?...` or `/search?...` | `extractListPage` | `ListPageResponse` |
| `/api/v2/schedule/next/:id` | `nextEpisodeSchedule.controller.ts` | `/anime/:id` | `extractNextEpisodeSchedule` | next episode object |
| `/api/v2/schedules` | `schedules.controller.ts` | `/ajax/schedule/list?...` | `extractSchedule` | `ScheduleResponse` |
| `/api/v2/news` | `news.controller.ts` | `/news?page=:page` | `extractNews` | `NewsResponse` |
| `/api/v2/random` | `random.controller.ts` | `/` | inline cheerio parser | `{ id: string }` |
| `/api/v2/genres` | `allGenres.controller.ts` | static | none | `string[]` |

## Current known risks

- Upstream anti-bot responses can return HTML shells with 200 status.
- Some endpoints depend on upstream sections inside anime detail page instead of old AJAX APIs.
- Endpoint IDs must use current upstream slug format (for example `one-piece-vss`), not legacy numeric suffixes.
