<?php

declare(strict_types=1);

namespace App\Services\Subscribe;

use App\Models\Config;
use App\Services\Subscribe;
use function base64_encode;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function json_last_error;
use function trim;
use const JSON_ERROR_NONE;
use const PHP_EOL;

final class V2Ray extends Base
{
    public function getContent($user): string
    {
        $links = '';
        // 判断是否开启V2Ray订阅
        if (! Config::obtain('enable_v2_sub')) {
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

            if ((int) $node_raw->sort === 11) {
                $v2_port = (int) ($node_custom_config['offset_port_user'] ??
                    ($node_custom_config['offset_port_node'] ?? 443));
                $uuid = (string) ($user->uuid ?? '');

                if ($v2_port <= 0 || $uuid === '') {
                    continue;
                }

                $security = (string) ($node_custom_config['security'] ?? 'none');
                $network = (string) ($node_custom_config['network'] ?? '');
                $header = is_array($node_custom_config['header'] ?? null)
                    ? $node_custom_config['header']
                    : ['type' => 'none'];
                $header_type = (string) ($header['type'] ?? '');

                $raw_host = $node_custom_config['header']['request']['headers']['Host']
                    ?? $node_custom_config['header']['request']['headers']['host']
                    ?? null;
                $header_host = is_array($raw_host) ? (string) ($raw_host[0] ?? '') : (is_string($raw_host) ? $raw_host : '');
                $host = $header_host !== '' ? $header_host : (string) ($node_custom_config['host'] ?? '');

                $raw_path = $node_custom_config['header']['request']['path'] ?? null;
                $header_path = is_array($raw_path) ? (string) ($raw_path[0] ?? '') : (is_string($raw_path) ? $raw_path : '');
                $path = $header_path !== '' ? $header_path : (string) ($node_custom_config['path'] ?? '/');

                $v2rayn_array = [
                    'v' => '2',
                    'ps' => (string) $node_raw->name,
                    'add' => $server,
                    'port' => $v2_port,
                    'id' => $uuid,
                    'aid' => 0,
                    'net' => $network,
                    'type' => $header_type,
                    'host' => $host,
                    'path' => $path,
                    'tls' => $security,
                ];

                $links .= 'vmess://' . base64_encode((string) json_encode($v2rayn_array)) . PHP_EOL;
            }
        }

        return $links;
    }
}
