<?php

declare(strict_types=1);

namespace App\Services\Subscribe;

use App\Models\Config;
use App\Services\Subscribe;
use function base64_encode;
use function is_array;
use function json_decode;
use function json_last_error;
use function rawurlencode;
use function trim;
use const JSON_ERROR_NONE;
use const PHP_EOL;

final class SS extends Base
{
    public function getContent($user): string
    {
        $links = '';
        // 判断是否开启SS订阅
        if (! Config::obtain('enable_ss_sub')) {
            return $links;
        }

        $nodes_raw = Subscribe::getUserNodes($user);

        foreach ($nodes_raw as $node_raw) {
            $server = trim((string) ($node_raw->server ?? ''));
            if ($server === '') {
                continue;
            }

            $node_custom_config = [];
            if (! empty($node_raw->custom_config)) {
                $decoded = json_decode((string) $node_raw->custom_config, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $node_custom_config = $decoded;
                }
            }

            if ((int) $node_raw->sort === 0) {
                $port = (int) ($node_custom_config['offset_port_user'] ??
                    ($node_custom_config['offset_port_node'] ?? $user->port));
                $method = (string) ($user->method ?? '');
                $passwd = (string) ($user->passwd ?? '');

                if ($port <= 0 || $method === '' || $passwd === '') {
                    continue;
                }

                $tag = '#' . rawurlencode((string) $node_raw->name);

                $links .= 'ss://' . base64_encode($method . ':' . $passwd . '@' . $server . ':' . $port) . $tag . PHP_EOL;
            }
        }

        return $links;
    }
}
