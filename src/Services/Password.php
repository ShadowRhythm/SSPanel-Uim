<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Config;
use App\Models\User;
use App\Services\I18n;
use App\Utils\Tools;
use Psr\Http\Client\ClientExceptionInterface;
use RedisException;

final class Password
{
    /**
     * @throws ClientExceptionInterface
     * @throws RedisException
     */
    public static function sendResetEmail($email): void
    {
        $redis = (new Cache())->initRedis();
        $token = Tools::genRandomChar(64);

        $redis->setex('password_reset:' . $token, Config::obtain('email_password_reset_ttl'), $email);

        $user = (new User())->where('email', $email)->first();
        $locale = $user?->locale ?? I18n::getCurrentLocale();
        $subject = I18n::trans('email.password_reset_subject', $locale, ['%app_name%' => $_ENV['appName']]);
        $resetUrl = $_ENV['baseUrl'] . '/password/token/' . $token;

        Mail::send(
            $email,
            $subject,
            'password_reset.tpl',
            [
                'resetUrl' => $resetUrl,
                'user' => $user,
            ],
            $locale
        );
    }
}
