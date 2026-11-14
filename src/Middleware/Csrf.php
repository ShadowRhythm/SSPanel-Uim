<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use function in_array;

final class Csrf implements MiddlewareInterface
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();

        if (in_array($method, self::SAFE_METHODS, true)) {
            return $handler->handle($request);
        }

        $cookieToken = $_COOKIE['_csrf'] ?? '';
        $headerToken = $request->getHeaderLine('X-CSRF-Token');

        if ($cookieToken === '' || $headerToken === '' || ! hash_equals($cookieToken, $headerToken)) {
            $response = AppFactory::determineResponseFactory()->createResponse(403);
            $response->getBody()->write('{"ret":0,"msg":"CSRF verification failed"}');
            return $response->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}
