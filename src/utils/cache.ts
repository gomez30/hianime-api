type CacheEntry<T> = {
  value: T;
  expiresAt: number;
};

const memoryCache = new Map<string, CacheEntry<unknown>>();

export const getCache = <T>(key: string): T | null => {
  const entry = memoryCache.get(key);
  if (!entry) return null;

  if (Date.now() > entry.expiresAt) {
    memoryCache.delete(key);
    return null;
  }

  return entry.value as T;
};

export const setCache = <T>(key: string, value: T, ttlMs: number): void => {
  memoryCache.set(key, {
    value,
    expiresAt: Date.now() + ttlMs,
  });
};

