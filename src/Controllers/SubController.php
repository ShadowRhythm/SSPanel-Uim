<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Config;
use App\Models\Link;
use App\Models\SubscribeLog;
use App\Services\I18n;
use App\Services\RateLimit;
use App\Services\Subscribe;
use App\Utils\ResponseHelper;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use RedisException;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Throwable;
use function in_array;
use function parse_url;
use function strtotime;
use const PHP_URL_HOST;
use const PHP_URL_PORT;

final class SubController extends BaseController
{
    /**
     * @throws ClientExceptionInterface
     * @throws GuzzleException
     * @throws RedisException
     * @throws TelegramSDKException
     */
    public function index($request, $response, $args): ResponseInterface
    {
        $err_msg = I18n::trans('user.invalid_sub_link');
        $subtype = (string) ($args['subtype'] ?? '');
        $subtype_list = ['json', 'clash', 'sip008', 'singbox', 'v2rayjson', 'sip002', 'ss', 'v2ray', 'trojan', 'qx'];

        $host = $request->getHeaderLine('Host');
        $sub_url = (string) ($_ENV['subUrl'] ?? '');
        $sub_host = (string) parse_url($sub_url, PHP_URL_HOST);
        $sub_port = parse_url($sub_url, PHP_URL_PORT);
        $expected_host = $sub_host . ($sub_port ? ':' . $sub_port : '');

        if (! ($_ENV['Subscribe'] ?? false) ||
            ! in_array($subtype, $subtype_list) ||
            ($host !== $expected_host && 'https://' . $host !== $sub_url && 'http://' . $host !== $sub_url)
        ) {
            return ResponseHelper::error($response, $err_msg);
        }

        $token = $this->antiXss->xss_clean($args['token'] ?? '');
        if ($token === '') {
            return ResponseHelper::error($response, $err_msg);
        }

        if ($_ENV['enable_rate_limit'] ?? false) {
            $rateLimit = new RateLimit();
            $remote_addr = (string) ($request->getServerParam('REMOTE_ADDR') ?? '127.0.0.1');
            if (! $rateLimit->checkRateLimit('sub_ip', $remote_addr) ||
                ! $rateLimit->checkRateLimit('sub_token', $token)
            ) {
                return ResponseHelper::error($response, $err_msg);
            }
        }

        $link = (new Link())->where('token', $token)->first();

        if ($link === null || ! $link->isValid()) {
            return ResponseHelper::error($response, $err_msg);
        }

        $user = $link->user();
        if ($user === null) {
            return ResponseHelper::error($response, $err_msg);
        }
        $err_msg = I18n::trans('user.invalid_sub_link', I18n::getCurrentLocale($user));

        $cacheKey = 'sub_content_' . $user->id . '_' . $token . '_' . $subtype;
        $sub_info = Cache::get($cacheKey);

        if ($sub_info === null || ! is_string($sub_info)) {
            try {
                $sub_info = Subscribe::getContent($user, $subtype);
                Cache::set($cacheKey, $sub_info, 60);
            } catch (Throwable) {
                return ResponseHelper::error($response, $err_msg);
            }
        }

        $content_type = match ($subtype) {
            'clash' => 'application/yaml',
            'json','sip008','singbox','v2rayjson' => 'application/json',
            default => 'text/plain',
        };

        $expire = ! empty($user->class_expire) ? (strtotime((string) $user->class_expire) ?: 0) : 0;
        $sub_details = ' upload=' . (int) ($user->u ?? 0)
            . '; download=' . (int) ($user->d ?? 0)
            . '; total=' . (int) ($user->transfer_enable ?? 0)
            . '; expire=' . $expire;

        // Clash specific
        $sub_content_disposition = 'attachment; filename=' . ($_ENV['appName'] ?? 'SSPanel');
        $sub_profile_update_interval = 6;
        $sub_profile_web_page_url = (string) ($_ENV['baseUrl'] ?? '');

        if (Config::obtain('subscribe_log')) {
            (new SubscribeLog())->add(
                $user,
                $subtype,
                $this->antiXss->xss_clean($request->getHeaderLine('User-Agent'))
            );
        }

        if ($subtype === 'clash') {
            return $response->withHeader('Subscription-Userinfo', $sub_details)
                ->withHeader('Content-Disposition', $sub_content_disposition)
                ->withHeader('Profile-Update-Interval', $sub_profile_update_interval)
                ->withHeader('Profile-Web-Page-Url', $sub_profile_web_page_url)
                ->withHeader('Content-Type', $content_type)
                ->write($sub_info);
        }

        return $response->withHeader('Subscription-Userinfo', $sub_details)
            ->withHeader('Content-Type', $content_type)
            ->write($sub_info);
    }
}
