<?php

namespace App\Services;

use App\Exceptions\CacheException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class CacheService
{
    private $client;
    private $subscriber;

    public function __construct()
    {
        $this->client = Redis::connection();
        $this->subscriber = Redis::connection('default');
    }

    public function set(string $key, string $value, int $expirationInSecond = 1800): void
    {
        if ($expirationInSecond === -1) {
            $this->client->set($key, $value);
            return;
        }
        $this->client->setex($key, $expirationInSecond, $value);
    }

    public function lpush(string $key, string $value): void
    {
        $this->client->lpush($key, $value);
    }

    public function get(string $key): string
    {
        $result = $this->client->get($key);
        if ($result === null) {
            throw new CacheException("Cache not found");
        }
        return $result;
    }

    public function countByKeyPattern(string $keyPattern): int
    {
        $cursor = '0';
        $count = 0;

        do {
            $result = $this->client->scan($cursor, ['match' => $keyPattern, 'count' => 1000]);
            $cursor = $result[0];
            $count += count($result[1]);
        } while ($cursor !== '0');

        return $count;
    }

    public function firstByKeyPatternAndDelete(string $keyPattern): ?string
    {
        $cursor = '0';
        $keysData = [];

        do {
            $result = $this->client->scan($cursor, ['match' => $keyPattern, 'count' => 1000]);
            $cursor = $result[0];
            $keysData = array_merge($keysData, $result[1]);
        } while ($cursor !== '0');

        // Sort keys
        usort($keysData, function ($a, $b) {
            $partsA = explode(':', $a);
            $partsB = explode(':', $b);

            $firstNumberA = isset($partsA[1]) ? (int) $partsA[1] : 0;
            $firstNumberB = isset($partsB[1]) ? (int) $partsB[1] : 0;
            $timestampA = isset($partsA[2]) ? (int) $partsA[2] : 0;
            $timestampB = isset($partsB[2]) ? (int) $partsB[2] : 0;

            if ($firstNumberA !== $firstNumberB) {
                return $firstNumberA - $firstNumberB;
            }
            return $timestampA - $timestampB;
        });

        foreach ($keysData as $key) {
            $lockKey = "lock:{$key}";
            $lockSet = $this->client->setnx($lockKey, 'locked');

            if ($lockSet === 1) {
                try {
                    $value = $this->client->get($key);
                    if ($value === null) {
                        continue;
                    }
                    $this->client->del($key);
                    return $value;
                } finally {
                    $this->client->del($lockKey);
                }
            }
        }

        return null;
    }

    public function getByKeyPattern(string $keyPattern): array
    {
        $result = [];
        $cursor = '0';
        $keysData = [];

        do {
            $scanResult = $this->client->scan($cursor, ['match' => $keyPattern, 'count' => 1000]);
            $cursor = $scanResult[0];
            $keysData = array_merge($keysData, $scanResult[1]);
        } while ($cursor !== '0');

        // Sort keys
        usort($keysData, function ($a, $b) {
            $partsA = explode(':', $a);
            $partsB = explode(':', $b);

            $firstNumberA = isset($partsA[1]) ? (int) $partsA[1] : 0;
            $firstNumberB = isset($partsB[1]) ? (int) $partsB[1] : 0;
            $timestampA = isset($partsA[2]) ? (int) $partsA[2] : 0;
            $timestampB = isset($partsB[2]) ? (int) $partsB[2] : 0;

            if ($firstNumberA !== $firstNumberB) {
                return $firstNumberA - $firstNumberB;
            }
            return $timestampA - $timestampB;
        });

        // Fetch values for each key
        foreach ($keysData as $key) {
            $value = $this->client->get($key);
            $result[$key] = $value ?? '';
        }

        return $result;
    }

    public function delByKeyPattern(string $keyPattern): void
    {
        $cursor = '0';
        $keysData = [];

        do {
            $scanResult = $this->client->scan($cursor, ['match' => $keyPattern, 'count' => 1000]);
            $cursor = $scanResult[0];
            $keysData = array_merge($keysData, $scanResult[1]);
        } while ($cursor !== '0');

        // Delete keys
        foreach ($keysData as $key) {
            $this->client->del($key);
        }
    }

    public function delete(string $key): int
    {
        return $this->client->del($key);
    }

    public function publish(string $channel, string $message): void
    {
        $this->client->publish($channel, $message);
    }

    public function subscribe(string $channel, callable $callback): void
    {
        $this->subscriber->subscribe([$channel], function ($message, $subscribedChannel) use ($callback, $channel) {
            if ($channel === $subscribedChannel) {
                $callback($message, $channel);
            }
        });
    }

    public function acquireLock(string $lockKey, ?string $uniqueValue = null, int $lockTimeoutMs = 5000): bool
    {
        $uniqueValue = $uniqueValue ?? Str::ulid();

        try {
            $result = $this->client->set("lock-kyla-{$lockKey}", $uniqueValue, 'PX', $lockTimeoutMs, 'NX');
            return $result === 'OK';
        } catch (\Exception $error) {
            logger('Error acquiring lock: ' . $error->getMessage());
            throw new CacheException('Error acquiring lock');
        }
    }

    public function releaseLock(string $lockKey, string $uniqueValue): void
    {
        $releaseScript = '
            if redis.call("get", KEYS[1]) == ARGV[1] then
                return redis.call("del", KEYS[1])
            else
                return 0
            end
        ';

        try {
            $this->client->eval($releaseScript, 1, $lockKey, $uniqueValue);
        } catch (\Exception $error) {
            logger('Error releasing lock: ' . $error->getMessage());
            throw new CacheException('Error releasing lock');
        }
    }
}
