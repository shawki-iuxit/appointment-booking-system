<?php

namespace App\Services;

use App\Contracts\CacheServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GlobalCacheService implements CacheServiceInterface
{
    private const DEFAULT_TTL = 3600; // 1 hour

    private const CACHE_PREFIX = 'app_cache';

    public function remember(string $entity, string $key, callable $callback, ?int $ttl = null): mixed
    {
        $cacheKey = $this->buildKey($entity, $key);
        $ttl = $ttl ?? self::DEFAULT_TTL;

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    public function put(string $entity, string $key, mixed $value, ?int $ttl = null): void
    {
        $cacheKey = $this->buildKey($entity, $key);
        $ttl = $ttl ?? self::DEFAULT_TTL;

        Cache::put($cacheKey, $value, $ttl);
    }

    public function forget(string $entity, string $key): void
    {
        $cacheKey = $this->buildKey($entity, $key);
        Cache::forget($cacheKey);
    }

    public function forgetPattern(string $entity, string $pattern): void
    {
        $fullPattern = $this->buildKey($entity, $pattern);

        try {
            $keys = $this->getKeysByPattern($fullPattern);

            foreach ($keys as $key) {
                Cache::forget($key);
            }
        } catch (\Exception $e) {
            Log::warning('Cache pattern forget failed', [
                'entity' => $entity,
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function invalidateEntity(string $entity): void
    {
        $this->forgetPattern($entity, '*');
    }

    public function buildKey(string $entity, string $key): string
    {
        return self::CACHE_PREFIX.':'.$entity.':'.$key;
    }

    public function flush(): void
    {
        Cache::flush();
    }

    public function getEntityStats(): array
    {
        try {
            if (config('cache.default') !== 'database') {
                return ['message' => 'Statistics only available for database cache driver'];
            }

            $pattern = self::CACHE_PREFIX.':%';
            $entries = DB::table(config('cache.stores.database.table', 'cache'))
                ->where('key', 'LIKE', $pattern)
                ->select('key')
                ->get();

            $entityStats = [];
            foreach ($entries as $entry) {
                $keyParts = explode(':', $entry->key);
                if (count($keyParts) >= 3) {
                    $entity = $keyParts[2];
                    $entityStats[$entity] = ($entityStats[$entity] ?? 0) + 1;
                }
            }

            return [
                'total_cache_entries' => $entries->count(),
                'entities' => $entityStats,
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function warmUpCache(string $entity, array $warmUpCallbacks): void
    {
        foreach ($warmUpCallbacks as $key => $callback) {
            try {
                if (! Cache::has($this->buildKey($entity, $key))) {
                    $this->remember($entity, $key, $callback);
                }
            } catch (\Exception $e) {
                Log::warning('Cache warm-up failed', [
                    'entity' => $entity,
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function setTags(array $tags): self
    {
        // For future implementation with cache tagging
        return $this;
    }

    private function getKeysByPattern(string $pattern): array
    {
        $keys = [];
        $cacheDriver = config('cache.default');

        if ($cacheDriver === 'database') {
            $dbPattern = str_replace('*', '%', $pattern);
            $keys = DB::table(config('cache.stores.database.table', 'cache'))
                ->where('key', 'LIKE', $dbPattern)
                ->pluck('key')
                ->toArray();
        } elseif ($cacheDriver === 'redis') {
            try {
                $redis = Cache::getStore()->getRedis();
                $redisPattern = str_replace('*', '*', $pattern); // Redis uses * for wildcards
                $keys = $redis->keys($redisPattern);
            } catch (\Exception $e) {
                Log::warning('Redis keys pattern matching failed', [
                    'pattern' => $pattern,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $keys;
    }
}
