<?php

declare(strict_types=1);

namespace App\Services;

use Redis;
use Throwable;

final class Cache
{
    private static ?Redis $client = null;

    public static function client(): ?Redis
    {
        if (self::$client !== null) {
            return self::$client;
        }

        try {
            self::$client = (new self())->initRedis();
            return self::$client;
        } catch (Throwable) {
            return null;
        }
    }

    public static function get(string $key): mixed
    {
        try {
            $client = self::client();
            if ($client === null) {
                return null;
            }
            $val = $client->get($key);
            if ($val === false || $val === null) {
                return null;
            }
            return unserialize($val);
        } catch (Throwable) {
            return null;
        }
    }

    public static function set(string $key, mixed $val, int $ttl = 300): bool
    {
        try {
            $client = self::client();
            if ($client === null) {
                return false;
            }
            return (bool) $client->setex($key, $ttl, serialize($val));
        } catch (Throwable) {
            return false;
        }
    }

    public static function forget(string $key): bool
    {
        try {
            $client = self::client();
            if ($client === null) {
                return false;
            }
            return (bool) $client->del($key);
        } catch (Throwable) {
            return false;
        }
    }

    public static function remember(string $key, int $ttl, callable $callback): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $val = $callback();
        self::set($key, $val, $ttl);
        return $val;
    }

    public function initRedis(): Redis
    {
        $config = self::getRedisConfig();
        $redis = new Redis();
        $ssl = $config['ssl'] ?? [];
        $host = $_ENV['redis_ssl'] ? 'tls://' . $config['host'] : $config['host'];
        $redis->connect(
            $host,
            $config['port'],
            $config['connectTimeout'],
            null,
            0,
            $config['readTimeout'],
            $ssl
        );
        // 认证
        if (($config['auth']['user'] ?? '') !== '' && ($config['auth']['pass'] ?? '') !== '') {
            $redis->auth([$config['auth']['user'], $config['auth']['pass']]);
        } elseif (($config['auth']['pass'] ?? '') !== '') {
            $redis->auth($config['auth']['pass']);
        }
        // 选择数据库
        if (isset($config['database'])) {
            $redis->select($config['database']);
        }
        return $redis;
    }

    public static function getRedisConfig(): array
    {
        $config = [
            'host' => $_ENV['redis_host'],
            'port' => $_ENV['redis_port'],
            'connectTimeout' => $_ENV['redis_connect_timeout'],
            'readTimeout' => $_ENV['redis_read_timeout'],
            'database' => $_ENV['redis_db'] ?? 0,
        ];

        if ($_ENV['redis_username'] !== '') {
            $config['auth']['user'] = $_ENV['redis_username'];
        }

        if ($_ENV['redis_password'] !== '') {
            $config['auth']['pass'] = $_ENV['redis_password'];
        }

        if ($_ENV['redis_ssl']) {
            $config['ssl'] = $_ENV['redis_ssl_context'];
        }

        return $config;
    }
}
