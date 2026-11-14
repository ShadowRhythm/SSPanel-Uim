<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\Auth as AuthService;
use App\Utils\Tools;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use function time;

final class Admin implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = AuthService::getUser();

        if (! $user->isLogin) {
            return AppFactory::determineResponseFactory()->createResponse(302)->withHeader('Location', '/auth/login');
        }

        if (! $user->is_admin) {
            return AppFactory::determineResponseFactory()->createResponse(302)->withHeader('Location', '/user');
        }

        // Generate CSRF token if not present
        if (empty($_COOKIE['_csrf'])) {
            $token = Tools::genRandomChar(32);
            setcookie('_csrf', $token, [
                'expires' => time() + 7200,
                'path' => '/',
                'secure' => (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (($_SERVER['SERVER_PORT'] ?? '') == 443)
                    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
                'httponly' => false,
                'samesite' => 'Strict',
            ]);
            $_COOKIE['_csrf'] = $token;
        }

        return $handler->handle($request);
    }
}
