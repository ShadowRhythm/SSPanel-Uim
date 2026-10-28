<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Config;
use App\Models\User;
use App\Services\Cache;
use App\Services\Captcha;
use App\Services\I18n;
use App\Services\Password;
use App\Services\RateLimit;
use App\Utils\Hash;
use App\Utils\ResponseHelper;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use RedisException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Smarty\Exception;
use Throwable;
use function strlen;
use function strtolower;

final class PasswordController extends BaseController
{
    /**
     * @throws Exception
     */
    public function reset(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $captcha = [];

        if (Config::obtain('enable_reset_password_captcha')) {
            $captcha = Captcha::generate();
        }

        return $response->write(
            $this->view()
                ->assign('captcha', $captcha)
                ->fetch('password/reset.tpl')
        );
    }

    public function handleReset(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $locale = I18n::getCurrentLocale();

        if (Config::obtain('enable_reset_password_captcha')) {
            $ret = Captcha::verify($request->getParams());

            if (! $ret) {
                return ResponseHelper::error($response, I18n::trans('user.captcha_failed', $locale));
            }
        }

        $email = strtolower($this->antiXss->xss_clean($request->getParam('email')));

        if ($email === '') {
            return ResponseHelper::error($response, I18n::trans('user.email_empty', $locale));
        }

        if (! (new RateLimit())->checkRateLimit('email_request_ip', $request->getServerParam('REMOTE_ADDR')) ||
            ! (new RateLimit())->checkRateLimit('email_request_address', $email)
        ) {
            return ResponseHelper::error($response, I18n::trans('user.request_too_frequent', $locale));
        }

        $user = (new User())->where('email', $email)->first();
        $msg = I18n::trans('user.reset_password_email_sent', $locale);

        if ($user !== null) {
            try {
                Password::sendResetEmail($email);
            } catch (Throwable $e) {
                $errorMsg = I18n::trans('user.email_send_failed', $locale);
                if (! empty($_ENV['debug'])) {
                    $errorMsg .= ': ' . $e->getMessage();
                }
                return ResponseHelper::error($response, $errorMsg);
            }
        }

        return ResponseHelper::success($response, $msg);
    }

    /**
     * @throws Exception
     */
    public function token(ServerRequest $request, Response $response, array $args)
    {
        $token = $this->antiXss->xss_clean($args['token']);
        $redis = (new Cache())->initRedis();

        try {
            $email = $redis->get('password_reset:' . $token);
        } catch (RedisException) {
            return $response->withStatus(302)->withHeader('Location', '/password/reset');
        }

        if (! $email) {
            return $response->withStatus(302)->withHeader('Location', '/password/reset');
        }

        return $response->write(
            $this->view()
                ->assign('token', $token)
                ->fetch('password/token.tpl')
        );
    }

    public function handleToken(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $locale = I18n::getCurrentLocale();
        $token = $args['token'] ?? '';
        if ($token === '' || str_contains($token, 'location.pathname') || str_contains($token, '{')) {
            $token = $request->getParam('token') ?? '';
        }
        $token = $this->antiXss->xss_clean($token);
        $password = $request->getParam('password');
        $confirm_password = $request->getParam('confirm_password');

        if ($password !== $confirm_password) {
            return ResponseHelper::error($response, I18n::trans('user.password_not_match', $locale));
        }

        if (strlen($password) < 8) {
            return ResponseHelper::error($response, I18n::trans('user.password_too_short', $locale));
        }

        $redis = (new Cache())->initRedis();

        try {
            $email = $redis->get('password_reset:' . $token);
        } catch (RedisException) {
            return ResponseHelper::error($response, I18n::trans('user.invalid_token', $locale));
        }

        if (! $email) {
            return ResponseHelper::error($response, I18n::trans('user.invalid_token', $locale));
        }

        $user = (new User())->where('email', $email)->first();

        if ($user === null) {
            return ResponseHelper::error($response, I18n::trans('user.invalid_token', $locale));
        }
        // reset password
        $hashPassword = Hash::passwordHash($password);
        $user->pass = $hashPassword;

        if (! $user->save()) {
            return ResponseHelper::error($response, I18n::trans('user.reset_failed_retry', $locale));
        }

        try {
            $redis->del('password_reset:' . $token);
        } catch (RedisException) {
            // ignore
        }

        if (Config::obtain('enable_forced_replacement')) {
            $user->removeLink();
        }

        return $response->withHeader('HX-Redirect', '/auth/login')->withJson([
            'ret' => 1,
            'msg' => I18n::trans('user.reset_success', $locale),
        ]);
    }
}
