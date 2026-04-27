import config from '../config/config';
const DEFAULT_MAX_RETRIES = config.isVercel ? 2 : 3;
const DEFAULT_RETRY_DELAY = 1000;
const DEFAULT_TIMEOUT = config.isVercel ? 20000 : 10000;
const MAX_RETRIES = Number(process.env.EXTERNAL_API_RETRIES) || DEFAULT_MAX_RETRIES;
const RETRY_DELAY = Number(process.env.EXTERNAL_API_RETRY_DELAY_MS) || DEFAULT_RETRY_DELAY;
const TIMEOUT = Number(process.env.EXTERNAL_API_TIMEOUT_MS) || DEFAULT_TIMEOUT;
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));
const normalizeBaseUrl = (url) => url.replace(/\/+$/, '');
const buildBaseUrlCandidates = () => {
    const candidates = [
        config.baseurl,
        config.baseurl2,
        process.env.UPSTREAM_BASEURL,
        process.env.UPSTREAM_BASEURL_2,
    ].filter(Boolean);
    const normalized = candidates.map(normalizeBaseUrl);
    return [...new Set(normalized)];
};
const classifyBlockedPage = (data) => {
    const lowered = data.toLowerCase();
    if (lowered.includes('just a moment') || lowered.includes('checking your browser')) {
        return 'Upstream anti-bot challenge page detected';
    }
    if (lowered.includes('attention required') && lowered.includes('cloudflare')) {
        return 'Cloudflare protection page detected from upstream';
    }
    if (lowered.includes('access denied') || lowered.includes('forbidden')) {
        return 'Upstream access denied page detected';
    }
    return null;
};
export const axiosInstance = async (endpoint, options = {}) => {
    const { headers: customHeaders = {}, retries = MAX_RETRIES, timeoutMs = TIMEOUT, expectHtml = true, } = options;
    const baseUrls = buildBaseUrlCandidates();
    const maxAttempts = Math.max(1, retries);
    let lastError = null;
    let attemptsTried = 0;
    const attemptDiagnostics = [];
    for (const baseUrl of baseUrls) {
        for (let attempt = 0; attempt < maxAttempts; attempt++) {
            attemptsTried++;
            const url = baseUrl + endpoint;
            const startedAt = Date.now();
            try {
                if (attempt > 0) {
                    const delay = RETRY_DELAY * Math.pow(2, attempt - 1);
                    console.log(`Retry attempt ${attempt + 1}/${maxAttempts} on ${baseUrl} after ${delay}ms delay...`);
                    await sleep(delay);
                }
                console.log(`Fetching (attempt ${attempt + 1}/${maxAttempts}) from ${url}`);
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), timeoutMs);
                let response;
                try {
                    response = await fetch(url, {
                        headers: {
                            ...(config.headers || {}),
                            ...customHeaders,
                            Accept: 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                            'Accept-Language': 'en-US,en;q=0.5',
                            'Accept-Encoding': 'gzip, deflate, br',
                            Connection: 'keep-alive',
                            'Upgrade-Insecure-Requests': '1',
                            'Cache-Control': 'max-age=0',
                        },
                        signal: controller.signal,
                    });
                }
                finally {
                    clearTimeout(timeoutId);
                }
                const durationMs = Date.now() - startedAt;
                console.log(`Response status: ${response.status} in ${durationMs}ms`);
                if (response.status === 429) {
                    const retryAfter = response.headers.get('retry-after');
                    const waitTime = retryAfter ? parseInt(retryAfter) * 1000 : RETRY_DELAY * 2;
                    console.warn(`Rate limited by upstream. Waiting ${waitTime}ms before retry...`);
                    await sleep(waitTime);
                    continue;
                }
                if (response.status >= 500 && response.status < 600) {
                    throw new Error(`Upstream server error: HTTP ${response.status}`);
                }
                if (!response.ok) {
                    throw new Error(`Upstream HTTP ${response.status}: ${response.statusText}`);
                }
                const data = await response.text();
                const trimmed = data.trim();
                const blockReason = classifyBlockedPage(data);
                if (!trimmed) {
                    throw new Error('Upstream returned an empty body (possible temporary block or bad upstream)');
                }
                if (blockReason) {
                    throw new Error(blockReason);
                }
                if (expectHtml && !trimmed.includes('<html') && !trimmed.includes('<!DOCTYPE html')) {
                    throw new Error('Unexpected non-HTML upstream response');
                }
                console.log(`Success: Received data length: ${data.length}`);
                return {
                    success: true,
                    data,
                    message: 'ok',
                    meta: {
                        baseUrl,
                        endpoint,
                        finalUrl: response.url || url,
                        status: response.status,
                        attempt: attempt + 1,
                        durationMs,
                        contentLength: data.length,
                        contentType: response.headers.get('content-type'),
                    },
                };
            }
            catch (error) {
                if (error instanceof Error) {
                    const durationMs = Date.now() - startedAt;
                    lastError = error;
                    attemptDiagnostics.push({
                        baseUrl,
                        endpoint,
                        attempt: attempt + 1,
                        durationMs,
                        message: error.message,
                        name: error.name,
                    });
                    console.error(`Fetch error (attempt ${attempt + 1}/${maxAttempts}) for ${endpoint} via ${baseUrl}:`, error.message);
                    if (error.name === 'AbortError') {
                        lastError = new Error(`Request timeout after ${timeoutMs}ms while contacting upstream: ${baseUrl}${endpoint}`);
                    }
                    if (error.message.includes('HTTP 40') && !error.message.includes('429')) {
                        break;
                    }
                }
                if (attempt === maxAttempts - 1) {
                    break;
                }
            }
        }
    }
    return {
        success: false,
        message: lastError?.message || 'Unknown upstream fetch error',
        details: {
            endpoint,
            baseUrlsTried: baseUrls,
            attemptsTried,
            timeoutMs,
            diagnostics: attemptDiagnostics,
        },
    };
};
