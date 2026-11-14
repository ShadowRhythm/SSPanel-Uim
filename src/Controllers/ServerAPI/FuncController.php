<?php

declare(strict_types=1);

namespace App\Controllers\ServerAPI;

use App\Controllers\BaseController;
use App\Models\DetectRule;
use App\Utils\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

final class FuncController extends BaseController
{
    /**
     * GET /api/server/v1/detect_rules
     */
    public function getDetectRules(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $rules = DetectRule::all()->toArray();

        foreach ($rules as &$rule) {
            $rule['type'] = isset($rule['type']) && $rule['type'] !== null && $rule['type'] !== ''
                ? (int) $rule['type']
                : 1;
        }
        unset($rule);

        return ResponseHelper::successWithDataEtag($request, $response, $rules);
    }
}
