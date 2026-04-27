const memoryCache = new Map();
export const getCache = (key) => {
    const entry = memoryCache.get(key);
    if (!entry)
        return null;
    if (Date.now() > entry.expiresAt) {
        memoryCache.delete(key);
        return null;
    }
    return entry.value;
};
export const setCache = (key, value, ttlMs) => {
    memoryCache.set(key, {
        value,
        expiresAt: Date.now() + ttlMs,
    });
};
