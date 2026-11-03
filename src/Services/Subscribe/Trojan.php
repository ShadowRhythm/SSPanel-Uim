<?php

declare(strict_types=1);

namespace App\Services\Subscribe;

use App\Models\Config;
use App\Services\Subscribe;
use function is_array;
use function is_string;
use function json_decode;
use function json_last_error;
use function rawurlencode;
use function trim;
use const JSON_ERROR_NONE;
use const PHP_EOL;

final class Trojan extends Base
{
    public function getContent($user): string
    {
        $links = '';
        // 判断是否开启Trojan订阅
        if (! Config::obtain('enable_trojan_sub')) {
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

            if ((int) $node_raw->sort === 14) {
                $trojan_port = (int) ($node_custom_config['offset_port_user'] ??
                    ($node_custom_config['offset_port_node'] ?? 443));
                $uuid = (string) ($user->uuid ?? '');

                if ($trojan_port <= 0 || $uuid === '') {
                    continue;
                }

                $host = (string) ($node_custom_config['host'] ?? ($node_custom_config['sni'] ?? $server));
                $allow_insecure = (string) ($node_custom_config['allow_insecure'] ?? '0');
                $security = (string) ($node_custom_config['security'] ?? 'tls');
                $mux = (string) ($node_custom_config['mux'] ?? '0');
                $network = (string) ($node_custom_config['network'] ?? 'tcp');
                $transport_plugin = (string) ($node_custom_config['transport_plugin'] ?? '');
                $transport_method = (string) ($node_custom_config['transport_method'] ?? '');
                $servicename = (string) ($node_custom_config['servicename'] ?? '');

                $request = is_array($node_custom_config['header']['request'] ?? null) ? $node_custom_config['header']['request'] : [];
                $raw_path = $request['path'] ?? null;
                $header_path = is_array($raw_path) ? (string) ($raw_path[0] ?? '') : (is_string($raw_path) ? $raw_path : '');
                $path = $header_path !== '' ? $header_path : (string) ($node_custom_config['path'] ?? '');

                $tag = '#' . rawurlencode((string) $node_raw->name);

                $links .= 'trojan://' . $uuid . '@' . $server . ':' . $trojan_port
                    . '?peer=' . rawurlencode($host)
                    . '&sni=' . rawurlencode($host)
                    . '&obfs=' . rawurlencode($transport_plugin)
                    . '&path=' . rawurlencode($path)
                    . '&mux=' . rawurlencode($mux)
                    . '&allowInsecure=' . rawurlencode($allow_insecure)
                    . '&obfsParam=' . rawurlencode($transport_method)
                    . '&type=' . rawurlencode($network)
                    . '&security=' . rawurlencode($security)
                    . '&serviceName=' . rawurlencode($servicename)
                    . $tag . PHP_EOL;
            }
        }

        return $links;
    }
}
