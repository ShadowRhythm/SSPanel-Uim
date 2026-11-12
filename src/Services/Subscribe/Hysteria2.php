<?php

declare(strict_types=1);

namespace App\Services\Subscribe;

use App\Services\Subscribe;
use function http_build_query;
use function json_decode;
use function rawurlencode;
use const PHP_EOL;

final class Hysteria2 extends Base
{
    public static function buildUri($user, $node_raw, array $node_custom_config): string
    {
        $hy2_port = $node_custom_config['offset_port_user'] ?? ($node_custom_config['offset_port_node'] ?? 443);
        $host = $node_custom_config['host'] ?? ($node_custom_config['sni'] ?? '');
        $allow_insecure = $node_custom_config['allow_insecure'] ?? false;
        $obfs_type = $node_custom_config['obfs'] ?? ($node_custom_config['obfs_type'] ?? '');
        $obfs_password = $node_custom_config['obfs_password'] ?? ($node_custom_config['obfs-password'] ?? '');
        $ports = $node_custom_config['ports'] ?? ($node_custom_config['server_ports'] ?? '');
        $pin_sha256 = $node_custom_config['pin_sha256'] ?? ($node_custom_config['pinSHA256'] ?? '');

        $port_part = $ports !== '' ? (string) $ports : (string) $hy2_port;

        $params = [];
        if ($host !== '') {
            $params['sni'] = $host;
        }
        if ($allow_insecure) {
            $params['insecure'] = '1';
        }
        if ($obfs_type !== '' && $obfs_password !== '') {
            $params['obfs'] = $obfs_type;
            $params['obfs-password'] = $obfs_password;
        }
        if ($pin_sha256 !== '') {
            $params['pinSHA256'] = $pin_sha256;
        }

        $query = $params !== [] ? '?' . http_build_query($params) : '';

        return 'hysteria2://' . rawurlencode((string) $user->passwd) . '@' . $node_raw->server . ':' . $port_part . '/' . $query . '#' . rawurlencode((string) $node_raw->name) . PHP_EOL;
    }

    public function getContent($user): string
    {
        $links = '';
        $nodes_raw = Subscribe::getUserNodes($user);

        foreach ($nodes_raw as $node_raw) {
            $node_custom_config = json_decode((string) $node_raw->custom_config, true) ?? [];

            if ((int) $node_raw->sort === 4) {
                $links .= self::buildUri($user, $node_raw, $node_custom_config);
            }
        }

        return $links;
    }
}
