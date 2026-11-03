<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Config;
use App\Models\EmailQueue;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Client\ClientExceptionInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Throwable;

final class Notification
{
    /**
     * @throws GuzzleException
     * @throws TelegramSDKException
     * @throws ClientExceptionInterface
     */
    public static function notifyAdmin($title = '', $msg = '', $template = 'warn.tpl'): void
    {
        $admins = (new User())->where('is_admin', 1)->get();

        foreach ($admins as $admin) {
            try {
                if ($admin->contact_method === 1 || $admin->im_type === 0) {
                    (new EmailQueue())->add(
                        $admin->email,
                        $title,
                        $template,
                        [
                            'user' => $admin,
                            'title' => $title,
                            'text' => $msg,
                        ]
                    );
                } else {
                    IM::send((int) $admin->im_value, $msg, $admin->im_type);
                }
            } catch (Throwable) {
                // Ignore failure for individual admin
            }
        }
    }

    /**
     * @throws GuzzleException
     * @throws TelegramSDKException
     * @throws ClientExceptionInterface
     */
    public static function notifyAdminTrans(string $titleKey, string $msgKey, array $params = [], string $template = 'warn.tpl'): void
    {
        $admins = (new User())->where('is_admin', 1)->get();

        foreach ($admins as $admin) {
            try {
                $locale = I18n::getCurrentLocale($admin);
                $title = I18n::trans($titleKey, $locale, $params);
                $msg = I18n::trans($msgKey, $locale, $params);
                self::notifyUser($admin, $title, $msg, $template);
            } catch (Throwable) {
                // Ignore failure for individual admin
            }
        }
    }

    /**
     * @throws GuzzleException
     * @throws TelegramSDKException
     * @throws ClientExceptionInterface
     */
    public static function notifyUser($user, $title = '', $msg = '', $template = 'warn.tpl'): void
    {
        if ($user->contact_method === 1 || $user->im_type === 0) {
            (new EmailQueue())->add(
                $user->email,
                $title,
                $template,
                [
                    'user' => $user,
                    'title' => $title,
                    'text' => $msg,
                ]
            );
        } else {
            IM::send((int) $user->im_value, $msg, $user->im_type);
        }
    }

    /**
     * @throws GuzzleException
     * @throws TelegramSDKException
     */
    public static function notifyAllUser($title = '', $msg = '', $template = 'warn.tpl'): void
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($user->contact_method === 1 || $user->im_type === 0) {
                (new EmailQueue())->add(
                    $user->email,
                    $title,
                    $template,
                    [
                        'user' => $user,
                        'title' => $title,
                        'text' => $msg,
                    ]
                );
            } else {
                IM::send((int) $user->im_value, $msg, $user->im_type);
            }
        }
    }

    /**
     * @throws GuzzleException
     * @throws TelegramSDKException
     */
    public static function notifyUserGroup(string $msg = ''): void
    {
        if (Config::obtain('enable_telegram_group_notify')) {
            IM::send((int) Config::obtain('telegram_chatid'), $msg, 0);
        }

        if (Config::obtain('enable_discord_channel_notify')) {
            IM::send((int) Config::obtain('discord_channel_id'), $msg, 1);
        }

        if (Config::obtain('enable_slack_channel_notify')) {
            IM::send((int) Config::obtain('slack_channel_id'), $msg, 2);
        }
    }
}
