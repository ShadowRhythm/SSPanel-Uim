<?php

declare(strict_types=1);

namespace App\Utils;

use function explode;

final class Cookie
{
    public static function set(array $arg, int $time): void
    {
        $isHttps = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? '') == 443)
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        foreach ($arg as $key => $value) {
            setcookie((string) $key, (string) $value, [
                'expires' => $time,
                'path' => '/',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }

    public static function setWithDomain(array $arg, int $time, string $domain): void
    {
        $isHttps = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? '') == 443)
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        $cleanDomain = trim(explode(':', $domain)[0]);

        $cookieOptions = [
            'expires' => $time,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        if ($cleanDomain !== '' && ! filter_var($cleanDomain, FILTER_VALIDATE_IP) && $cleanDomain !== 'localhost') {
            $cookieOptions['domain'] = $cleanDomain;
        }

        foreach ($arg as $key => $value) {
            setcookie((string) $key, (string) $value, $cookieOptions);
        }
    }

    public static function get(string $key): ?string
    {
        return $_COOKIE[$key] ?? null;
    }
}
