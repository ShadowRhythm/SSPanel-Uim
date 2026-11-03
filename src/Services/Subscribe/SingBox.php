<?php

declare(strict_types=1);

namespace App\Services\Subscribe;

use App\Services\Subscribe;
use App\Utils\Tools;
use function array_filter;
use function array_merge;
use function array_values;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function json_last_error;
use function trim;
use const JSON_ERROR_NONE;

final class SingBox extends Base
{
    public function getContent($user): string
    {
        $nodes = [];
        $singbox_config = is_array($_ENV['SingBox_Config'] ?? null) ? $_ENV['SingBox_Config'] : [];
        $singbox_config['outbounds'] ??= [];
        $singbox_group_indexes = is_array($_ENV['SingBox_Group_Indexes'] ?? null) ? $_ENV['SingBox_Group_Indexes'] : [0, 1];
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
                    $method = (string) ($user->method ?? '');
                    $password = (string) ($user->passwd ?? '');

                    if ($port <= 0 || $method === '' || $password === '') {
                        $node = [];
                        break;
                    }

                    $node = [
                        'type' => 'shadowsocks',
                        'tag' => (string) $node_raw->name,
                        'server' => $server,
                        'server_port' => $port,
                        'method' => $method,
                        'password' => $password,
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
                    $uot = $node_custom_config['uot'] ?? false;

                    if (! $user_pk) {
                        $node = [];
                        break;
                    }

                    $server_key = (string) ($node_custom_config['server_key'] ?? '');

                    $node = [
                        'type' => 'shadowsocks',
                        'tag' => (string) $node_raw->name,
                        'server' => $server,
                        'server_port' => $ss_2022_port,
                        'method' => $method,
                        'password' => $server_key === '' ? $user_pk : $server_key . ':' . $user_pk,
                        'udp_over_tcp' => (bool) $uot,
                    ];

                    break;
                case 2:
                    $tuic_port = (int) ($node_custom_config['offset_port_user'] ??
                        ($node_custom_config['offset_port_node'] ?? 443));
                    $host = (string) ($node_custom_config['host'] ?? ($node_custom_config['sni'] ?? $server));
                    $allow_insecure = (bool) ($node_custom_config['allow_insecure'] ?? false);
                    $congestion_control = (string) ($node_custom_config['congestion_control'] ?? 'bbr');
                    $uuid = (string) ($user->uuid ?? '');
                    $passwd = (string) ($user->passwd ?? '');

                    if ($tuic_port <= 0 || $uuid === '' || $passwd === '') {
                        $node = [];
                        break;
                    }

                    $node = [
                        'type' => 'tuic',
                        'tag' => (string) $node_raw->name,
                        'server' => $server,
                        'server_port' => $tuic_port,
                        'uuid' => $uuid,
                        'password' => $passwd,
                        'congestion_control' => $congestion_control,
                        'zero_rtt_handshake' => true,
                        'tls' => [
                            'enabled' => true,
                            'server_name' => $host,
                            'insecure' => $allow_insecure,
                        ],
                    ];

                    $node['tls'] = array_filter($node['tls'], static fn ($v) => $v !== '' && $v !== null);

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
                    $network = (string) ($node_custom_config['network'] ?? '');
                    $transport = $network === 'tcp' ? '' : $network;
                    if ($transport === 'httpupgrade') {
                        $transport = 'ws';
                    }

                    $ws_opts = is_array($node_custom_config['ws-opts'] ?? null)
                        ? $node_custom_config['ws-opts']
                        : (is_array($node_custom_config['ws_opts'] ?? null) ? $node_custom_config['ws_opts'] : []);
                    $ws_headers = is_array($ws_opts['headers'] ?? null) ? $ws_opts['headers'] : [];

                    $raw_host = $node_custom_config['header']['request']['headers']['Host']
                        ?? $node_custom_config['header']['request']['headers']['host']
                        ?? null;
                    $header_host = is_array($raw_host) ? (string) ($raw_host[0] ?? '') : (is_string($raw_host) ? $raw_host : '');
                    $host = $header_host !== '' ? $header_host :
                        (string) ($ws_headers['Host'] ?? ($ws_headers['host'] ?? ($node_custom_config['host'] ?? '')));

                    $raw_path = $node_custom_config['header']['request']['path'] ?? null;
                    $header_path = is_array($raw_path) ? (string) ($raw_path[0] ?? '') : (is_string($raw_path) ? $raw_path : '');
                    $path = $header_path !== '' ? $header_path :
                        (string) ($ws_opts['path'] ?? ($node_custom_config['path'] ?? ''));

                    $headers = is_array($node_custom_config['header']['request']['headers'] ?? null)
                        ? $node_custom_config['header']['request']['headers']
                        : $ws_headers;
                    if ($host !== '' && ! isset($headers['Host']) && ! isset($headers['host'])) {
                        $headers['Host'] = $host;
                    }
                    $clean_headers = [];
                    foreach ($headers as $hk => $hv) {
                        $clean_headers[(string) $hk] = is_array($hv) ? (string) ($hv[0] ?? '') : (string) $hv;
                    }
                    $headers = $clean_headers;

                    $service_name = (string) ($node_custom_config['servicename'] ??
                        ($node_custom_config['grpc-opts']['grpc_service_name'] ??
                        ($node_custom_config['grpc_opts']['grpc_service_name'] ?? '')));
                    $allow_insecure = (bool) ($node_custom_config['allow_insecure'] ?? false);
                    $tls_enabled = $security === 'tls' ||
                        (isset($node_custom_config['tls']) && (bool) $node_custom_config['tls']) ||
                        $host !== '';
                    $utls = (bool) ($node_custom_config['utls'] ?? false);
                    $method = (string) ($node_custom_config['method'] ?? '');
                    $max_early_data = (int) ($node_custom_config['max_early_data'] ??
                        ($ws_opts['max_early_data'] ?? 0));
                    $early_data_header_name = (string) ($node_custom_config['early_data_header_name'] ??
                        ($ws_opts['early_data_header_name'] ?? ''));

                    $node = [
                        'type' => 'vmess',
                        'tag' => (string) $node_raw->name,
                        'server' => $server,
                        'server_port' => $v2_port,
                        'uuid' => $uuid,
                        'security' => 'auto',
                        'alter_id' => 0,
                        'packet_encoding' => 'xudp',
                        'global_padding' => true,
                        'authenticated_length' => true,
                    ];

                    if ($tls_enabled) {
                        $tls = [
                            'enabled' => true,
                            'server_name' => $host !== '' ? $host : $server,
                            'insecure' => $allow_insecure,
                        ];
                        if ($utls) {
                            $tls['utls'] = [
                                'enabled' => true,
                                'fingerprint' => 'chrome',
                            ];
                        }
                        $node['tls'] = array_filter($tls, static fn ($v) => $v !== '' && $v !== null);
                    }

                    if ($transport !== '') {
                        $transport_config = [
                            'type' => $transport,
                            'path' => $path,
                            'method' => $method,
                            'headers' => $headers,
                            'service_name' => $service_name,
                            'max_early_data' => $max_early_data,
                            'early_data_header_name' => $early_data_header_name,
                        ];
                        $node['transport'] = array_filter($transport_config, static fn ($v) => $v !== '' && $v !== null && $v !== [] && $v !== 0);
                    }

                    break;
                case 14:
                    $trojan_port = (int) ($node_custom_config['offset_port_user'] ??
                        ($node_custom_config['offset_port_node'] ?? 443));
                    $uuid = (string) ($user->uuid ?? '');

                    if ($trojan_port <= 0 || $uuid === '') {
                        $node = [];
                        break;
                    }

                    $network = (string) ($node_custom_config['network'] ?? 'tcp');
                    $transport = $network === 'tcp' ? '' : $network;
                    if ($transport === 'httpupgrade') {
                        $transport = 'ws';
                    }

                    $ws_opts = is_array($node_custom_config['ws-opts'] ?? null)
                        ? $node_custom_config['ws-opts']
                        : (is_array($node_custom_config['ws_opts'] ?? null) ? $node_custom_config['ws_opts'] : []);
                    $ws_headers = is_array($ws_opts['headers'] ?? null) ? $ws_opts['headers'] : [];

                    $raw_host = $node_custom_config['header']['request']['headers']['Host']
                        ?? $node_custom_config['header']['request']['headers']['host']
                        ?? null;
                    $header_host = is_array($raw_host) ? (string) ($raw_host[0] ?? '') : (is_string($raw_host) ? $raw_host : '');
                    $host = $header_host !== '' ? $header_host :
                        (string) ($ws_headers['Host'] ??
                        ($ws_headers['host'] ??
                        ($node_custom_config['host'] ??
                        ($node_custom_config['sni'] ?? $server))));

                    $raw_path = $node_custom_config['header']['request']['path'] ?? null;
                    $header_path = is_array($raw_path) ? (string) ($raw_path[0] ?? '') : (is_string($raw_path) ? $raw_path : '');
                    $path = $header_path !== '' ? $header_path :
                        (string) ($ws_opts['path'] ?? ($node_custom_config['path'] ?? ''));

                    $headers = is_array($node_custom_config['header']['request']['headers'] ?? null)
                        ? $node_custom_config['header']['request']['headers']
                        : $ws_headers;
                    if ($host !== '' && ! isset($headers['Host']) && ! isset($headers['host'])) {
                        $headers['Host'] = $host;
                    }
                    $clean_headers = [];
                    foreach ($headers as $hk => $hv) {
                        $clean_headers[(string) $hk] = is_array($hv) ? (string) ($hv[0] ?? '') : (string) $hv;
                    }
                    $headers = $clean_headers;

                    $service_name = (string) ($node_custom_config['servicename'] ??
                        ($node_custom_config['grpc-opts']['grpc_service_name'] ??
                        ($node_custom_config['grpc_opts']['grpc_service_name'] ?? '')));
                    $allow_insecure = (bool) ($node_custom_config['allow_insecure'] ?? false);

                    $node = [
                        'type' => 'trojan',
                        'tag' => (string) $node_raw->name,
                        'server' => $server,
                        'server_port' => $trojan_port,
                        'password' => $uuid,
                        'tls' => [
                            'enabled' => true,
                            'server_name' => $host !== '' ? $host : $server,
                            'insecure' => $allow_insecure,
                        ],
                    ];

                    if ($transport !== '') {
                        $transport_config = [
                            'type' => $transport,
                            'path' => $path,
                            'headers' => $headers,
                            'service_name' => $service_name,
                        ];
                        $node['transport'] = array_filter($transport_config, static fn ($v) => $v !== '' && $v !== null && $v !== []);
                    }

                    $node['tls'] = array_filter($node['tls'], static fn ($v) => $v !== '' && $v !== null);

                    break;
                default:
                    $node = [];
                    break;
            }

            if ($node === []) {
                continue;
            }

            $nodes[] = $node;

            foreach ($singbox_group_indexes as $index) {
                if (isset($singbox_config['outbounds'][$index])) {
                    $singbox_config['outbounds'][$index]['outbounds'] ??= [];
                    $singbox_config['outbounds'][$index]['outbounds'][] = (string) $node_raw->name;
                }
            }
        }

        if ($nodes === []) {
            if (isset($singbox_config['outbounds']) && is_array($singbox_config['outbounds'])) {
                foreach ($singbox_config['outbounds'] as $key => $outbound) {
                    if (($outbound['type'] ?? '') === 'urltest') {
                        unset($singbox_config['outbounds'][$key]);
                    } elseif (($outbound['type'] ?? '') === 'selector' && empty($outbound['outbounds'])) {
                        $singbox_config['outbounds'][$key]['outbounds'] = ['🎯 Direct'];
                    }
                }
                $singbox_config['outbounds'] = array_values($singbox_config['outbounds']);
            }
        } else {
            $singbox_config['outbounds'] = array_merge($singbox_config['outbounds'] ?? [], $nodes);
        }

        $singbox_config['experimental']['cache_file']['cache_id'] = (string) ($_ENV['appName'] ?? 'SSPanel');

        return (string) json_encode($singbox_config);
    }
}
