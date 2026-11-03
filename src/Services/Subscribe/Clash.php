<?php

declare(strict_types=1);

namespace App\Services\Subscribe;

use App\Services\Subscribe;
use App\Utils\Tools;
use function array_merge;
use function is_array;
use function is_string;
use function json_decode;
use function json_last_error;
use function trim;
use function yaml_emit;
use const JSON_ERROR_NONE;
use const YAML_UTF8_ENCODING;

final class Clash extends Base
{
    public function getContent($user): string
    {
        $nodes = [];
        $clash_config = is_array($_ENV['Clash_Config'] ?? null) ? $_ENV['Clash_Config'] : [];
        $clash_group_indexes = is_array($_ENV['Clash_Group_Indexes'] ?? null) ? $_ENV['Clash_Group_Indexes'] : [];
        $clash_group_config = is_array($_ENV['Clash_Group_Config'] ?? null) ? $_ENV['Clash_Group_Config'] : [];
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

            switch ((int) $node_raw->sort) {
                case 0:
                    $port = (int) ($node_custom_config['offset_port_user'] ??
                        ($node_custom_config['offset_port_node'] ?? $user->port));
                    $cipher = (string) ($user->method ?? '');
                    $password = (string) ($user->passwd ?? '');

                    if ($port <= 0 || $cipher === '' || $password === '') {
                        $node = [];
                        break;
                    }

                    $plugin = (string) ($node_custom_config['plugin'] ?? '');
                    $plugin_option = $node_custom_config['plugin_option'] ?? null;
                    $udp = $node_custom_config['udp'] ?? true;

                    $node = [
                        'name' => (string) $node_raw->name,
                        'type' => 'ss',
                        'server' => $server,
                        'port' => $port,
                        'password' => $password,
                        'cipher' => $cipher,
                        'udp' => (bool) $udp,
                        'plugin' => $plugin,
                        'plugin-opts' => $plugin_option,
                    ];

                    break;
                case 1:
                    $ss_2022_port = (int) ($node_custom_config['offset_port_user'] ??
                        ($node_custom_config['offset_port_node'] ?? 443));
                    $method = (string) ($node_custom_config['method'] ?? '2022-blake3-aes-128-gcm');
                    $passwd = (string) ($user->passwd ?? '');

                    if ($ss_2022_port <= 0 || $passwd === '') {
                        $node = [];
                        break;
                    }

                    $user_pk = Tools::genSs2022UserPk($passwd, $method);

                    if (! $user_pk) {
                        $node = [];
                        break;
                    }

                    $udp = $node_custom_config['udp'] ?? true;
                    $server_key = (string) ($node_custom_config['server_key'] ?? '');
                    $uot = $node_custom_config['uot'] ?? false;

                    $node = [
                        'name' => (string) $node_raw->name,
                        'type' => 'ss',
                        'server' => $server,
                        'port' => $ss_2022_port,
                        'password' => $server_key === '' ? $user_pk : $server_key . ':' . $user_pk,
                        'cipher' => $method,
                        'udp' => (bool) $udp,
                        'udp_over_tcp' => (bool) $uot,
                    ];

                    break;
                case 2:
                    $tuic_port = (int) ($node_custom_config['offset_port_user'] ??
                        ($node_custom_config['offset_port_node'] ?? 443));
                    $host = (string) ($node_custom_config['host'] ?? ($node_custom_config['sni'] ?? $server));
                    $congestion_control = (string) ($node_custom_config['congestion_control'] ?? 'bbr');
                    $uuid = (string) ($user->uuid ?? '');
                    $passwd = (string) ($user->passwd ?? '');

                    if ($tuic_port <= 0 || $uuid === '' || $passwd === '') {
                        $node = [];
                        break;
                    }

                    $node = [
                        'name' => (string) $node_raw->name,
                        'type' => 'tuic',
                        'server' => $server,
                        'port' => $tuic_port,
                        'password' => $passwd,
                        'uuid' => $uuid,
                        'sni' => $host,
                        'congestion-controller' => $congestion_control,
                        'reduce-rtt' => true,
                    ];

                    break;
                case 11:
                    $v2_port = (int) ($node_custom_config['offset_port_user'] ??
                        ($node_custom_config['offset_port_node'] ?? 443));
                    $uuid = (string) ($user->uuid ?? '');

                    if ($v2_port <= 0 || $uuid === '') {
                        $node = [];
                        break;
                    }

                    $security = (string) ($node_custom_config['security'] ?? 'none');
                    $encryption = (string) ($node_custom_config['encryption'] ?? 'auto');
                    $network = (string) ($node_custom_config['network'] ?? '');

                    $ws_opts = is_array($node_custom_config['ws-opts'] ?? null)
                        ? $node_custom_config['ws-opts']
                        : (is_array($node_custom_config['ws_opts'] ?? null) ? $node_custom_config['ws_opts'] : null);
                    $h2_opts = is_array($node_custom_config['h2-opts'] ?? null)
                        ? $node_custom_config['h2-opts']
                        : (is_array($node_custom_config['h2_opts'] ?? null) ? $node_custom_config['h2_opts'] : null);
                    $http_opts = is_array($node_custom_config['http-opts'] ?? null)
                        ? $node_custom_config['http-opts']
                        : (is_array($node_custom_config['http_opts'] ?? null) ? $node_custom_config['http_opts'] : null);
                    $grpc_opts = is_array($node_custom_config['grpc-opts'] ?? null)
                        ? $node_custom_config['grpc-opts']
                        : (is_array($node_custom_config['grpc_opts'] ?? null) ? $node_custom_config['grpc_opts'] : null);

                    $request = is_array($node_custom_config['header']['request'] ?? null)
                        ? $node_custom_config['header']['request'] : [];
                    $request_headers = is_array($request['headers'] ?? null) ? $request['headers'] : [];
                    $raw_host = $request_headers['Host'] ?? $request_headers['host'] ?? null;
                    $header_host = is_array($raw_host) ? (string) ($raw_host[0] ?? '') : (is_string($raw_host) ? $raw_host : '');
                    $host = $header_host !== '' ? $header_host : (string) ($node_custom_config['host'] ?? ($node_custom_config['sni'] ?? ''));

                    $allow_insecure = (bool) ($node_custom_config['allow_insecure'] ?? false);
                    $tls = $security === 'tls';
                    $udp = $node_custom_config['udp'] ?? true;

                    if ($network === 'httpupgrade') {
                        $network = 'ws';
                    }

                    $node = [
                        'name' => (string) $node_raw->name,
                        'type' => 'vmess',
                        'server' => $server,
                        'port' => $v2_port,
                        'uuid' => $uuid,
                        'alterId' => 0,
                        'cipher' => $encryption,
                        'udp' => (bool) $udp,
                        'tls' => $tls,
                        'skip-cert-verify' => $allow_insecure,
                        'servername' => $host,
                        'network' => $network,
                        'ws-opts' => $ws_opts,
                        'h2-opts' => $h2_opts,
                        'http-opts' => $http_opts,
                        'grpc-opts' => $grpc_opts,
                    ];

                    break;
                case 14:
                    $trojan_port = (int) ($node_custom_config['offset_port_user'] ??
                        ($node_custom_config['offset_port_node'] ?? 443));
                    $uuid = (string) ($user->uuid ?? '');

                    if ($trojan_port <= 0 || $uuid === '') {
                        $node = [];
                        break;
                    }

                    $network = (string) ($node_custom_config['header']['type'] ?? ($node_custom_config['network'] ?? 'tcp'));
                    $host = (string) ($node_custom_config['host'] ?? ($node_custom_config['sni'] ?? $server));
                    $allow_insecure = (bool) ($node_custom_config['allow_insecure'] ?? false);
                    $udp = $node_custom_config['udp'] ?? true;

                    $ws_opts = is_array($node_custom_config['ws-opts'] ?? null)
                        ? $node_custom_config['ws-opts']
                        : (is_array($node_custom_config['ws_opts'] ?? null) ? $node_custom_config['ws_opts'] : null);
                    $grpc_opts = is_array($node_custom_config['grpc-opts'] ?? null)
                        ? $node_custom_config['grpc-opts']
                        : (is_array($node_custom_config['grpc_opts'] ?? null) ? $node_custom_config['grpc_opts'] : null);

                    if ($network === 'httpupgrade') {
                        $network = 'ws';
                    }

                    $node = [
                        'name' => (string) $node_raw->name,
                        'type' => 'trojan',
                        'server' => $server,
                        'sni' => $host,
                        'port' => $trojan_port,
                        'password' => $uuid,
                        'network' => $network,
                        'udp' => (bool) $udp,
                        'skip-cert-verify' => $allow_insecure,
                        'ws-opts' => $ws_opts,
                        'grpc-opts' => $grpc_opts,
                    ];

                    break;
                default:
                    $node = [];
                    break;
            }

            if ($node === []) {
                continue;
            }

            $nodes[] = $node;

            foreach ($clash_group_indexes as $index) {
                if (isset($clash_group_config['proxy-groups'][$index])) {
                    $clash_group_config['proxy-groups'][$index]['proxies'] ??= [];
                    $clash_group_config['proxy-groups'][$index]['proxies'][] = (string) $node_raw->name;
                }
            }
        }

        $clash_nodes = [
            'proxies' => $nodes,
        ];

        foreach ($clash_group_indexes as $index) {
            if (isset($clash_group_config['proxy-groups'][$index]) && empty($clash_group_config['proxy-groups'][$index]['proxies'])) {
                $clash_group_config['proxy-groups'][$index]['proxies'] = ['DIRECT'];
            }
        }

        return yaml_emit(
            array_merge($clash_config, $clash_nodes, $clash_group_config),
            YAML_UTF8_ENCODING
        );
    }
}
