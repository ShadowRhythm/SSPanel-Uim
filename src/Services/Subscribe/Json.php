<?php

declare(strict_types=1);

namespace App\Services\Subscribe;

use App\Services\Subscribe;
use function json_encode;

final class Json extends Base
{
    public function getContent($user): string
    {
        $sub_url = Subscribe::getUniversalSubLink($user);

        return (string) json_encode([
            'version' => 4,
            'sub_name' => (string) ($_ENV['appName'] ?? 'SSPanel'),
            'email' => (string) ($user->email ?? ''),
            'user_name' => (string) ($user->user_name ?? ''),
            'class' => (int) ($user->class ?? 0),
            'class_expire_date' => (string) ($user->class_expire ?? ''),
            'total_traffic' => (int) ($user->transfer_enable ?? 0),
            'used_upload_traffic' => (int) ($user->u ?? 0),
            'used_download_traffic' => (int) ($user->d ?? 0),
            'sub_url' => [
                'sing-box' => $sub_url . '/singbox',
                'clash' => $sub_url . '/clash',
            ],
        ]);
    }
}
