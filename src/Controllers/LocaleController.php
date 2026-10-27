<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\I18n;
use App\Utils\Cookie;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use function str_contains;
use function time;

final class LocaleController extends BaseController
{
    public function switch(ServerRequest $request, Response $response, array $args): Response
    {
        $lang = $args['lang'] ?? '';

        if (I18n::isValidLocale($lang)) {
            // Cookie::set uses secure:true; also call setcookie without secure for HTTP environments
            Cookie::set(['locale' => $lang], time() + 86400 * 365);
            // Ensure current request also sees the new locale immediately
            $_COOKIE['locale'] = $lang;

            // If user is logged in, persist to DB as well
            if ($this->user->isLogin) {
                $this->user->locale = $lang;
                $this->user->save();
            }
        }

        $referer = $request->getHeaderLine('Referer');
        $redirectUrl = ($referer !== '' && ! str_contains($referer, '/locale/')) ? $referer : '/';

        return $response->withStatus(302)->withHeader('Location', $redirectUrl);
    }
}
