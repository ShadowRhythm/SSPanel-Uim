<?php

declare(strict_types=1);

namespace App\Services\Subscribe;

use App\Services\Subscribe;
use App\Utils\Tools;
use function array_filter;
use function array_merge;
use function in_array;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function json_last_error;
use function trim;
use const JSON_ERROR_NONE;

final class V2RayJson extends Base
{
    public function getContent($user): string
    {
        $nodes = [];
        $v2rayjson_config = is_array($_ENV['V2RayJson_Config'] ?? null) ? $_ENV['V2RayJson_Config'] : [];
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
                        'protocol' => 'shadowsocks',
                        'settings' => [
                            'address' => $server,
                            'port' => $port,
                            'method' => $method,
                            'password' => $password,
                        ],
                        'tag' => (string) $node_raw->name,
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

                    $server_key = (string) ($node_custom_config['server_key'] ?? '');

                    $node = [
                        'protocol' => 'shadowsocks2022',
                        'settings' => [
                            'address' => $server,
                            'port' => $ss_2022_port,
                            'method' => $method,
                            'psk' => $server_key === '' ? $user_pk : $server_key . ':' . $user_pk,
                        ],
                        'tag' => (string) $node_raw->name,
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
                    $transport = (string) ($node_custom_config['network'] ?? 'tcp');

                    $ws_opts = is_array($node_custom_config['ws-opts'] ?? null)
                        ? $node_custom_config['ws-opts']
                        : (is_array($node_custom_config['ws_opts'] ?? null) ? $node_custom_config['ws_opts'] : []);
                    $ws_headers = is_array($ws_opts['headers'] ?? null) ? $ws_opts['headers'] : [];

                    $request = is_array($node_custom_config['header']['request'] ?? null) ? $node_custom_config['header']['request'] : [];
                    $request_headers = is_array($request['headers'] ?? null) ? $request['headers'] : [];
                    $raw_host = $request_headers['Host'] ?? $request_headers['host'] ?? null;
                    $header_host = is_array($raw_host) ? (string) ($raw_host[0] ?? '') : (is_string($raw_host) ? $raw_host : '');
                    $host = $header_host !== '' ? $header_host :
                        (string) ($ws_headers['Host'] ?? ($ws_headers['host'] ?? ($node_custom_config['host'] ?? $server)));

                    $raw_path = $request['path'] ?? null;
                    $header_path = is_array($raw_path) ? (string) ($raw_path[0] ?? '') : (is_string($raw_path) ? $raw_path : '');
                    $path = $header_path !== '' ? $header_path :
                        (string) ($ws_opts['path'] ?? ($node_custom_config['path'] ?? ''));

                    $headers = $request_headers ?: $ws_headers;
                    $service_name = (string) ($node_custom_config['servicename'] ?? '');
                    $meek_url = (string) ($node_custom_config['meek_url'] ?? '');

                    $node = [
                        'protocol' => 'vmess',
                        'settings' => [
                            'address' => $server,
                            'port' => $v2_port,
                            'uuid' => $uuid,
                        ],
                        'tag' => (string) $node_raw->name,
                        'streamSettings' => [
                            'transport' => $transport,
                            'transportSettings' => [
                                'ws' => [
                                    'path' => $transport === 'ws' ? $path : '',
                                    'header' => $headers,
                                ],
                                'grpc' => [
                                    'host' => $transport === 'grpc' ? $host : '',
                                    'service_name' => $service_name,
                                ],
                                'meek' => [
                                    'url' => $meek_url,
                                ],
                                'httpupgrade' => [
                                    'path' => $transport === 'httpupgrade' ? $path : '',
                                    'host' => $transport === 'httpupgrade' ? $host : '',
                                ],
                            ],
                            'security' => $security,
                            'securitySettings' => [
                                'tls' => [
                                    'server_name' => in_array($security, ['tls', 'auto'], true) ? $host : '',
                                ],
                            ],
                        ],
                    ];

                    $node['streamSettings']['transportSettings']['ws'] = array_filter($node['streamSettings']['transportSettings']['ws']);
                    $node['streamSettings']['transportSettings']['grpc'] = array_filter($node['streamSettings']['transportSettings']['grpc']);
                    $node['streamSettings']['transportSettings']['meek'] = array_filter($node['streamSettings']['transportSettings']['meek']);
                    $node['streamSettings']['transportSettings']['httpupgrade'] = array_filter($node['streamSettings']['transportSettings']['httpupgrade']);
                    $node['streamSettings']['transportSettings'] = array_filter($node['streamSettings']['transportSettings']);
                    $node['streamSettings']['securitySettings']['tls'] = array_filter($node['streamSettings']['securitySettings']['tls']);
                    $node['streamSettings']['securitySettings'] = array_filter($node['streamSettings']['securitySettings']);

                    break;
                case 14:
                    $trojan_port = (int) ($node_custom_config['offset_port_user'] ??
                        ($node_custom_config['offset_port_node'] ?? 443));
                    $uuid = (string) ($user->uuid ?? '');

                    if ($trojan_port <= 0 || $uuid === '') {
                        $node = [];
                        break;
                    }

                    $ws_opts = is_array($node_custom_config['ws-opts'] ?? null)
                        ? $node_custom_config['ws-opts']
                        : (is_array($node_custom_config['ws_opts'] ?? null) ? $node_custom_config['ws_opts'] : []);
                    $ws_headers = is_array($ws_opts['headers'] ?? null) ? $ws_opts['headers'] : [];

                    $request = is_array($node_custom_config['header']['request'] ?? null) ? $node_custom_config['header']['request'] : [];
                    $request_headers = is_array($request['headers'] ?? null) ? $request['headers'] : [];
                    $raw_host = $request_headers['Host'] ?? $request_headers['host'] ?? null;
                    $header_host = is_array($raw_host) ? (string) ($raw_host[0] ?? '') : (is_string($raw_host) ? $raw_host : '');
                    $host = $header_host !== '' ? $header_host :
                        (string) ($ws_headers['Host'] ?? ($ws_headers['host'] ?? ($node_custom_config['host'] ?? ($node_custom_config['sni'] ?? $server))));

                    $allow_insecure = $node_custom_config['allow_insecure'] ?? '0';
                    $transport = (string) ($node_custom_config['network'] ?? '');

                    $raw_path = $request['path'] ?? null;
                    $header_path = is_array($raw_path) ? (string) ($raw_path[0] ?? '') : (is_string($raw_path) ? $raw_path : '');
                    $path = $header_path !== '' ? $header_path :
                        (string) ($ws_opts['path'] ?? ($node_custom_config['path'] ?? ''));

                    $headers = $request_headers ?: $ws_headers;
                    $service_name = (string) ($node_custom_config['servicename'] ?? '');

                    $node = [
                        'protocol' => 'trojan',
                        'settings' => [
                            'address' => $server,
                            'port' => $trojan_port,
                            'password' => $uuid,
                        ],
                        'tag' => (string) $node_raw->name,
                        'streamSettings' => [
                            'transport' => $transport,
                            'transportSettings' => [
                                'ws' => [
                                    'path' => $transport === 'ws' ? $path : '',
                                    'header' => $headers,
                                ],
                                'grpc' => [
                                    'host' => $transport === 'grpc' ? $host : '',
                                    'service_name' => $service_name,
                                ],
                                'httpupgrade' => [
                                    'path' => $transport === 'httpupgrade' ? $path : '',
                                    'host' => $transport === 'httpupgrade' ? $host : '',
                                ],
                            ],
                            'security' => 'tls',
                            'securitySettings' => [
                                'tls' => [
                                    'allow_insecure' => (bool) $allow_insecure,
                                    'server_name' => $host,
                                ],
                            ],
                        ],
                    ];

                    $node['streamSettings']['transportSettings']['ws'] = array_filter($node['streamSettings']['transportSettings']['ws']);
                    $node['streamSettings']['transportSettings']['grpc'] = array_filter($node['streamSettings']['transportSettings']['grpc']);
                    $node['streamSettings']['transportSettings']['httpupgrade'] = array_filter($node['streamSettings']['transportSettings']['httpupgrade']);
                    $node['streamSettings']['transportSettings'] = array_filter($node['streamSettings']['transportSettings']);
                    $node['streamSettings']['securitySettings'] = array_filter($node['streamSettings']['securitySettings']);

                    break;
                default:
                    $node = [];
                    break;
            }

            if ($node === []) {
                continue;
            }

            $nodes[] = $node;
        }

        $v2rayjson_config['outbounds'] = array_merge($v2rayjson_config['outbounds'] ?? [], $nodes);

        return (string) json_encode($v2rayjson_config);
    }
}
