<?php

declare(strict_types=1);

namespace App\Controllers\ServerAPI;

use App\Controllers\BaseController;
use App\Models\Node;
use App\Utils\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use function json_decode;
use function max;
use function time;
use const JSON_UNESCAPED_SLASHES;

final class NodeController extends BaseController
{
    /**
     * GET /api/server/v1/info
     */
    public function getInfo(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        /** @var Node|null $node */
        $node = $request->getAttribute('node');

        if ($node === null) {
            return $response->withStatus(401)->withJson([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Invalid token.',
                ],
            ]);
        }

        $data = [
            'node_speedlimit' => $node->node_speedlimit,
            'sort' => $node->sort,
            'server' => $node->server,
            'custom_config' => json_decode((string) $node->custom_config, true, 512, JSON_UNESCAPED_SLASHES),
        ];

        return ResponseHelper::successWithDataEtag($request, $response, $data);
    }

    /**
     * PUT /api/server/v1/heartbeat
     */
    public function heartbeat(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        /** @var Node|null $node */
        $node = $request->getAttribute('node');

        if ($node === null) {
            return $response->withStatus(401)->withJson([
                'error' => [
                    'code' => 'unauthorized',
                    'message' => 'Invalid token.',
                ],
            ]);
        }

        $body = json_decode($request->getBody()->__toString(), true);
        $online_user = isset($body['online_user']) ? (int) $body['online_user'] : 0;

        $update = [
            'online_user'    => max(0, $online_user),
            'node_heartbeat' => time(),
        ];

        if (isset($body['version']) && $body['version'] !== '') {
            $update['backend_version'] = (string) $body['version'];
        }

        $node->update($update);

        return $response->withJson([
            'data' => 'ok',
        ]);
    }
}
