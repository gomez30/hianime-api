<?php
require_once __DIR__ . '/tohost.php';

function api_request(string $endpoint, int $timeoutSeconds = 25): array
{
    $url = rtrim(BASE_API_URL, '/') . $endpoint;
    $raw = false;
    $httpCode = null;
    $transportError = null;

    // Prefer cURL on shared hosting, it is more reliable than file_get_contents for HTTPS.
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeoutSeconds),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: Animeobt-PHP-Client/1.0',
            ],
            // Keep verification enabled by default; shared hosts generally have valid CA bundle.
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $transportError = 'cURL error: ' . curl_error($ch);
        }
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    // Fallback for environments where cURL is disabled.
    if ($raw === false) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeoutSeconds,
                'ignore_errors' => true,
                'header' => "Accept: application/json\r\nUser-Agent: Animeobt-PHP-Client/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            $lastError = error_get_last();
            $transportError = $transportError ?: ('stream error: ' . ($lastError['message'] ?? 'unknown'));
        }

        // Best-effort HTTP status extraction from response headers.
        if (isset($http_response_header[0])) {
            if (preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
                $httpCode = (int)$m[1];
            }
        }
    }

    if ($raw === false || $raw === '') {
        return [
            'ok' => false,
            'error' => 'Network/host blocked API request. ' . ($transportError ?? 'Unable to reach API.'),
            'data' => ['url' => $url, 'httpCode' => $httpCode],
        ];
    }

    $json = json_decode($raw, true);
    if (!is_array($json)) {
        return [
            'ok' => false,
            'error' => 'Invalid JSON response from API.',
            'data' => ['url' => $url, 'httpCode' => $httpCode, 'bodySample' => substr($raw, 0, 180)],
        ];
    }

    if (!isset($json['success']) || $json['success'] !== true) {
        $message = $json['message'] ?? 'API returned an unsuccessful response.';
        return [
            'ok' => false,
            'error' => $message,
            'data' => ['details' => $json['details'] ?? null, 'url' => $url, 'httpCode' => $httpCode],
        ];
    }

    return ['ok' => true, 'error' => null, 'data' => $json['data'] ?? null];
}

function normalize_anime_card(array $anime): array
{
    $title = trim((string)($anime['title'] ?? ''));
    return [
        'id' => $anime['id'] ?? '',
        'name' => $title !== '' ? $title : 'Unknown title',
        'poster' => $anime['poster'] ?? '',
        'type' => $anime['type'] ?? '',
        'rating' => $anime['rating'] ?? '',
        'episodes' => $anime['episodes'] ?? ['sub' => null, 'dub' => null, 'eps' => null],
    ];
}

function animeobt_non_empty(...$values): string
{
    foreach ($values as $v) {
        $t = trim((string)$v);
        if ($t !== '') return $t;
    }
    return '';
}

function animeobt_extract_id_from_href(string $href): string
{
    $parts = parse_url($href);
    if (!$parts || empty($parts['path'])) return '';
    $path = trim($parts['path'], '/');
    $segments = explode('/', $path);
    if (count($segments) >= 2 && $segments[0] === 'anime') {
        return $segments[1];
    }
    return end($segments) ?: '';
}

function animeobt_load_dom_xpath(string $html): ?DOMXPath
{
    if (!class_exists('DOMDocument') || trim($html) === '') return null;
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $loaded = @$dom->loadHTML($html);
    libxml_clear_errors();
    if (!$loaded) return null;
    return new DOMXPath($dom);
}

function animeobt_fetch_html_url(string $url, int $timeoutSeconds = 25): string
{
    $result = api_request_raw_url($url, $timeoutSeconds);
    if (!$result['ok']) return '';
    return (string)$result['body'];
}

function api_request_raw_url(string $url, int $timeoutSeconds = 25): array
{
    $raw = false;
    $httpCode = null;
    $transportError = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => min(10, $timeoutSeconds),
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/json,*/*',
                'User-Agent: Animeobt-PHP-Client/1.0',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $raw = curl_exec($ch);
        if ($raw === false) {
            $transportError = 'cURL error: ' . curl_error($ch);
        }
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }

    if ($raw === false) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeoutSeconds,
                'ignore_errors' => true,
                'header' => "Accept: text/html,application/json,*/*\r\nUser-Agent: Animeobt-PHP-Client/1.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);
        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            $lastError = error_get_last();
            $transportError = $transportError ?: ('stream error: ' . ($lastError['message'] ?? 'unknown'));
        }
    }

    if ($raw === false || $raw === '') {
        return ['ok' => false, 'error' => $transportError ?: 'Unable to reach URL', 'body' => '', 'httpCode' => $httpCode];
    }

    return ['ok' => true, 'error' => null, 'body' => $raw, 'httpCode' => $httpCode];
}

function animeobt_scrape_search_results(string $query): array
{
    $url = 'https://aniwatch.co.at/?s=' . urlencode($query);
    $html = animeobt_fetch_html_url($url, 25);
    if ($html === '') return [];

    $xpath = animeobt_load_dom_xpath($html);
    if (!$xpath) {
        return animeobt_scrape_search_results_regex($html);
    }

    $cards = [];
    $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' flw-item ')]");
    if (!$nodes) return [];

    foreach ($nodes as $node) {
        $animeLinks = $xpath->query(".//a[contains(@href, '/anime/')]", $node);
        if (!$animeLinks || $animeLinks->length === 0) continue;

        $href = (string)$animeLinks->item(0)->getAttribute('href');
        $id = animeobt_extract_id_from_href($href);
        if ($id === '') continue;

        $titleNode = $xpath->query(".//*[contains(@class,'film-name')]//a", $node);
        $title = $titleNode && $titleNode->length ? trim($titleNode->item(0)->textContent) : '';

        $imgNode = $xpath->query(".//img", $node);
        $poster = '';
        if ($imgNode && $imgNode->length) {
            $poster = animeobt_non_empty(
                $imgNode->item(0)->getAttribute('data-src'),
                $imgNode->item(0)->getAttribute('src')
            );
        }

        $typeNode = $xpath->query(".//*[contains(@class,'fd-infor')]//*[contains(@class,'fdi-item')]", $node);
        $type = $typeNode && $typeNode->length ? trim($typeNode->item(0)->textContent) : '';

        $cards[] = [
            'id' => $id,
            'name' => $title !== '' ? $title : $id,
            'poster' => $poster,
            'type' => $type,
            'rating' => '',
            'episodes' => ['sub' => null, 'dub' => null, 'eps' => null],
        ];
    }

    if (!empty($cards)) return $cards;
    return animeobt_scrape_search_results_regex($html);
}

function animeobt_scrape_search_results_regex(string $html): array
{
    $cards = [];
    if (trim($html) === '') return $cards;

    if (preg_match_all('/<a[^>]+href="https?:\/\/aniwatch\.co\.at\/anime\/([^"\/]+)\/"[^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER)) {
        $seen = [];
        foreach ($matches as $m) {
            $id = trim($m[1] ?? '');
            if ($id === '' || isset($seen[$id])) continue;
            $seen[$id] = true;

            $anchorHtml = $m[0];
            $title = '';
            if (preg_match('/data-en="([^"]+)"/i', $anchorHtml, $tm)) {
                $title = html_entity_decode(trim($tm[1]), ENT_QUOTES | ENT_HTML5);
            } else {
                $plain = trim(strip_tags($m[2] ?? ''));
                $title = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5);
            }
            if ($title === '') {
                $title = ucwords(str_replace('-', ' ', $id));
            }

            $cards[] = [
                'id' => $id,
                'name' => $title,
                'poster' => '',
                'type' => '',
                'rating' => '',
                'episodes' => ['sub' => null, 'dub' => null, 'eps' => null],
            ];
            if (count($cards) >= 60) break;
        }
    }

    return $cards;
}

function animeobt_search(string $query, int $page = 1): array
{
    // API-only mode to keep shared hosting fast and avoid direct scrape overhead.
    $result = api_request('/api/v2/search?keyword=' . urlencode($query) . '&page=' . max(1, $page));
    if (!$result['ok']) {
        return ['animes' => [], 'currentPage' => 1, 'totalPages' => 1, 'hasNextPage' => false];
    }
    return normalize_search_payload($result['data'] ?? []);
}

function animeobt_fetch_anime_meta_fallback(string $animeId): array
{
    $html = animeobt_fetch_html_url('https://aniwatch.co.at/anime/' . rawurlencode($animeId), 25);
    if ($html === '') return [];

    $xpath = animeobt_load_dom_xpath($html);
    if (!$xpath) return [];

    $meta = [
        'poster' => '',
        'type' => '',
        'rating' => '',
        'duration' => '',
        'synopsis' => '',
    ];

    $ogImage = $xpath->query("//meta[@property='og:image']/@content");
    if ($ogImage && $ogImage->length) {
        $meta['poster'] = trim($ogImage->item(0)->nodeValue);
    }

    if ($meta['poster'] === '') {
        $posterNode = $xpath->query("//*[contains(@class,'film-poster')]//img");
        if ($posterNode && $posterNode->length) {
            $meta['poster'] = animeobt_non_empty(
                $posterNode->item(0)->getAttribute('data-src'),
                $posterNode->item(0)->getAttribute('src')
            );
        }
    }

    $typeNode = $xpath->query("//*[contains(@class,'film-stats')]//*[contains(@class,'item')]");
    if ($typeNode && $typeNode->length) {
        $meta['type'] = trim($typeNode->item(0)->textContent);
    }

    $ratingNode = $xpath->query("//*[contains(@class,'tick-pg')]");
    if ($ratingNode && $ratingNode->length) {
        $meta['rating'] = trim($ratingNode->item(0)->textContent);
    }

    $durationNode = $xpath->query("//div[contains(@class,'item')][.//span[contains(@class,'item-head') and contains(.,'Duration')]]//*[contains(@class,'name')]");
    if ($durationNode && $durationNode->length) {
        $meta['duration'] = trim($durationNode->item(0)->textContent);
    }

    $descNode = $xpath->query("//*[contains(@class,'film-description')]//*[contains(@class,'text')]");
    if ($descNode && $descNode->length) {
        $meta['synopsis'] = trim($descNode->item(0)->textContent);
    }

    return $meta;
}

function normalize_home_payload(array $payload): array
{
    $spotlight = array_map('normalize_anime_card', $payload['spotlight'] ?? []);
    $trending = array_map('normalize_anime_card', $payload['trending'] ?? []);
    $latestEpisode = array_map('normalize_anime_card', $payload['latestEpisode'] ?? []);

    return [
        'spotlightAnimes' => $spotlight,
        'trendingAnimes' => $trending,
        'latestEpisodeAnimes' => $latestEpisode,
        'raw' => $payload,
    ];
}

function normalize_search_payload(array $payload): array
{
    return [
        'animes' => array_map('normalize_anime_card', $payload['response'] ?? []),
        'currentPage' => (int)($payload['pageInfo']['currentPage'] ?? 1),
        'totalPages' => (int)($payload['pageInfo']['totalPages'] ?? 1),
        'hasNextPage' => (bool)($payload['pageInfo']['hasNextPage'] ?? false),
    ];
}

function normalize_detail_payload(array $payload): array
{
    $name = trim((string)($payload['title'] ?? ''));
    $description = trim((string)($payload['synopsis'] ?? ''));
    $animeId = (string)($payload['id'] ?? '');
    $fallbackMeta = [];
    if (
        $animeId !== '' &&
        (trim((string)($payload['poster'] ?? '')) === '' ||
            trim((string)($payload['type'] ?? '')) === '' ||
            trim((string)($payload['duration'] ?? '')) === '' ||
            $description === '')
    ) {
        $fallbackMeta = animeobt_fetch_anime_meta_fallback($animeId);
    }
    $recommended = array_map('normalize_anime_card', $payload['recommended'] ?? []);
    $related = array_map('normalize_anime_card', $payload['related'] ?? []);

    return [
        'info' => [
            'id' => $payload['id'] ?? '',
            'name' => $name !== '' ? $name : 'Unknown title',
            'poster' => animeobt_non_empty($payload['poster'] ?? '', $fallbackMeta['poster'] ?? ''),
            'description' => animeobt_non_empty($description, $fallbackMeta['synopsis'] ?? ''),
            'stats' => [
                'type' => animeobt_non_empty($payload['type'] ?? '', $fallbackMeta['type'] ?? ''),
                'rating' => animeobt_non_empty($payload['rating'] ?? '', $fallbackMeta['rating'] ?? ''),
                'quality' => $payload['quality'] ?? '',
                'duration' => animeobt_non_empty($payload['duration'] ?? '', $fallbackMeta['duration'] ?? ''),
                'episodes' => $payload['episodes'] ?? ['sub' => null, 'dub' => null, 'eps' => null],
            ],
        ],
        'recommendedAnimes' => $recommended,
        'relatedAnimes' => $related,
    ];
}

function normalize_episodes_payload(array $payload): array
{
    $episodes = [];
    foreach ($payload as $entry) {
        $episodes[] = [
            'number' => (int)($entry['episodeNumber'] ?? 0),
            'title' => trim((string)($entry['title'] ?? 'Episode')),
            'episodeId' => $entry['id'] ?? '',
            'isFiller' => (bool)($entry['isFiller'] ?? false),
        ];
    }

    return [
        'episodes' => $episodes,
        'totalEpisodes' => count($episodes),
    ];
}

function animeobt_extract_wp_episode_html(string $jsonText): string
{
    $json = json_decode($jsonText, true);
    if (!is_array($json)) return '';
    return (string)($json['html'] ?? '');
}

function animeobt_extract_data_ids(string $html): array
{
    if (!preg_match_all('/data-id="(\d+)"/i', $html, $m)) return [];
    $ids = array_values(array_unique($m[1]));
    return array_slice($ids, 0, 40);
}

function animeobt_slug_hint_from_id(string $animeId): string
{
    $slug = strtolower(trim($animeId));
    $slug = preg_replace('/-(vss|qwe|qaz|wsx|edc|rfv|tgb|yhn|ujm)$/', '', $slug);
    return $slug ?: strtolower($animeId);
}

function animeobt_parse_episode_items_from_html(string $html): array
{
    $episodes = [];
    if (trim($html) === '') return $episodes;

    if (preg_match_all('/<a[^>]*class="[^"]*ssl-item[^"]*ep-item[^"]*"[^>]*>/i', $html, $anchors)) {
        $items = $anchors[0];
        foreach ($items as $idx => $anchorTag) {
            $href = '';
            $title = '';
            $isFiller = stripos($anchorTag, 'ssl-item-filler') !== false;

            if (preg_match('/href="([^"]+)"/i', $anchorTag, $hm)) {
                $href = html_entity_decode(trim($hm[1]), ENT_QUOTES | ENT_HTML5);
            }
            if (preg_match('/title="([^"]+)"/i', $anchorTag, $tm)) {
                $title = html_entity_decode(trim($tm[1]), ENT_QUOTES | ENT_HTML5);
            }
            if ($title === '') $title = 'Episode ' . ($idx + 1);

            $episodes[] = [
                'number' => $idx + 1,
                'title' => $title,
                'episodeId' => $href,
                'isFiller' => $isFiller,
            ];
        }
    }

    return $episodes;
}

function animeobt_get_episodes(string $animeId): array
{
    // First try API endpoint.
    $api = api_request('/api/v2/episodes/' . urlencode($animeId));
    if ($api['ok']) {
        $normalized = normalize_episodes_payload($api['data'] ?? []);
        if (!empty($normalized['episodes'])) {
            // Keep API result unless it is clearly suspiciously tiny for long-running titles.
            if ($normalized['totalEpisodes'] >= 20 || stripos($animeId, 'one-piece') === false) {
                return $normalized;
            }
        }
    }

    // Fallback: infer correct internal anime id from source page and pull wp-json list.
    $animePageUrl = 'https://aniwatch.co.at/anime/' . rawurlencode($animeId);
    $animePageHtml = animeobt_fetch_html_url($animePageUrl, 30);
    if ($animePageHtml === '') {
        return ['episodes' => [], 'totalEpisodes' => 0];
    }

    $slugHint = animeobt_slug_hint_from_id($animeId);
    $candidateIds = animeobt_extract_data_ids($animePageHtml);
    if (empty($candidateIds)) {
        return ['episodes' => [], 'totalEpisodes' => 0];
    }

    $best = ['score' => -1, 'episodes' => []];
    foreach ($candidateIds as $cid) {
        $raw = api_request_raw_url('https://aniwatch.co.at/wp-json/v1/episode/list/' . $cid, 25);
        if (!$raw['ok']) continue;
        $listHtml = animeobt_extract_wp_episode_html((string)$raw['body']);
        if ($listHtml === '') continue;
        $eps = animeobt_parse_episode_items_from_html($listHtml);
        if (empty($eps)) continue;

        $firstHref = strtolower((string)($eps[0]['episodeId'] ?? ''));
        $score = count($eps);
        if ($slugHint !== '' && strpos($firstHref, $slugHint) !== false) $score += 10000;

        if ($score > $best['score']) {
            $best = ['score' => $score, 'episodes' => $eps];
        }
    }

    if (!empty($best['episodes'])) {
        return ['episodes' => $best['episodes'], 'totalEpisodes' => count($best['episodes'])];
    }

    return ['episodes' => [], 'totalEpisodes' => 0];
}

