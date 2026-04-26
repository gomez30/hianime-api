const BASE_URL = process.env.SMOKE_BASE_URL || 'http://localhost:5000';
const API_PREFIX = '/api/v2';

const endpointChecks = [
  { path: '/home', requiredDataKey: 'spotlight' },
  { path: '/top-search', requiredArrayData: true },
  { path: '/search?keyword=one+piece&page=1', requiredDataKey: 'response' },
  { path: '/anime/one-piece-vss', requiredDataKey: 'title' },
  { path: '/episodes/one-piece-vss', requiredArrayData: true },
  { path: '/characters/one-piece-vss', requiredDataKey: 'response' },
  { path: '/suggestion?keyword=one', requiredArrayData: true },
  { path: '/news?page=1', requiredDataKey: 'news' },
  { path: '/filter?keyword=one+piece&page=1', requiredDataKey: 'response' },
  { path: '/schedule/next/one-piece-vss', requiredStringData: true },
  { path: '/schedules', requiredDataKey: 'data' },
  { path: '/random', requiredDataKey: 'id' },
  { path: '/genres', requiredArrayData: true },
];

const checkShape = (json, check) => {
  if (!json || typeof json !== 'object') return 'invalid JSON object';
  if (!('success' in json) || !json.success) return json.message || 'request failed';
  if (!('data' in json)) return 'missing data field';

  if (check.requiredArrayData && !Array.isArray(json.data)) return 'data is not an array';
  if (check.requiredStringData && typeof json.data !== 'string') return 'data is not a string';
  if (check.requiredDataKey && (!json.data || !(check.requiredDataKey in json.data)))
    return `missing data.${check.requiredDataKey}`;

  return null;
};

const run = async () => {
  const results = [];

  for (const check of endpointChecks) {
    const url = `${BASE_URL}${API_PREFIX}${check.path}`;
    const startedAt = Date.now();

    try {
      const response = await fetch(url);
      const text = await response.text();
      let json;
      try {
        json = JSON.parse(text);
      } catch {
        json = null;
      }

      const shapeError = checkShape(json, check);
      results.push({
        path: check.path,
        status: response.status,
        durationMs: Date.now() - startedAt,
        ok: !shapeError,
        error: shapeError,
      });
    } catch (error) {
      results.push({
        path: check.path,
        status: 0,
        durationMs: Date.now() - startedAt,
        ok: false,
        error: error instanceof Error ? error.message : 'unknown fetch error',
      });
    }
  }

  const failed = results.filter(r => !r.ok);
  console.log(JSON.stringify({ baseUrl: BASE_URL, results }, null, 2));

  if (failed.length > 0) {
    process.exitCode = 1;
  }
};

run();
