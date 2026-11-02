<?php

declare(strict_types=1);

use App\Interfaces\MigrationInterface;
use App\Services\DB;

return new class() implements MigrationInterface {
    public function up(): int
    {
        DB::getPdo()->exec('
        ALTER TABLE stream_media ADD KEY IF NOT EXISTS `created_at` (`created_at`);
        ALTER TABLE stream_media ADD KEY IF NOT EXISTS `node_id` (`node_id`);
        ALTER TABLE login_ip ADD KEY IF NOT EXISTS `userid_datetime` (`userid`, `datetime`);');

        return 2026110200;
    }

    public function down(): int
    {
        DB::getPdo()->exec('
        ALTER TABLE stream_media DROP KEY IF EXISTS `created_at`;
        ALTER TABLE stream_media DROP KEY IF EXISTS `node_id`;
        ALTER TABLE login_ip DROP KEY IF EXISTS `userid_datetime`;');

        return 2026102802;
    }
};
