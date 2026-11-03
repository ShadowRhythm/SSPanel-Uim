<?php

declare(strict_types=1);

namespace App\Services\Subscribe;

use App\Models\Config;
use App\Services\Subscribe;
use function is_array;
use function json_decode;
use function json_encode;
use function json_last_error;
use function max;
use function trim;
use const JSON_ERROR_NONE;

final class SIP008 extends Base
{
    public function getContent($user): string
    {
        $bytes_used = (int) ($user->u + $user->d);
        $bytes_remaining = (int) max(0, $user->transfer_enable - $bytes_used);

        // 判断是否开启SS订阅
        if (! Config::obtain('enable_ss_sub')) {
            return (string) json_encode([
                'version' => 1,
                'servers' => [],
                'bytes_used' => $bytes_used,
                'bytes_remaining' => $bytes_remaining,
            ]);
        }

        $nodes = [];
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
                $plugin = (string) ($node_custom_config['plugin'] ?? '');
                $plugin_option = (string) ($node_custom_config['plugin_option'] ?? '');
                $port = (int) ($node_custom_config['offset_port_user'] ??
                    ($node_custom_config['offset_port_node'] ?? $user->port));

                $node = [
                    'id' => $node_raw->id,
                    'remarks' => (string) $node_raw->name,
                    'server' => $server,
                    'server_port' => $port,
                    'password' => (string) $user->passwd,
                    'method' => (string) $user->method,
                    'plugin' => $plugin,
                    'plugin_opts' => $plugin_option,
                ];

                $nodes[] = $node;
            }
        }

        return (string) json_encode([
            'version' => 1,
            'servers' => $nodes,
            'bytes_used' => $bytes_used,
            'bytes_remaining' => $bytes_remaining,
        ]);
    }
}
