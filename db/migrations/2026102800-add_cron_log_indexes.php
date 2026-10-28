<?php

declare(strict_types=1);

use App\Interfaces\MigrationInterface;
use App\Services\DB;

return new class() implements MigrationInterface {
    public function up(): int
    {
        DB::getPdo()->exec('
        ALTER TABLE subscribe_log ADD KEY IF NOT EXISTS `request_time` (`request_time`);
        ALTER TABLE detect_log ADD KEY IF NOT EXISTS `datetime` (`datetime`);');

        return 2026102800;
    }

    public function down(): int
    {
        DB::getPdo()->exec('
        ALTER TABLE subscribe_log DROP KEY IF EXISTS `request_time`;
        ALTER TABLE detect_log DROP KEY IF EXISTS `datetime`;');

        return 2025080100;
    }
};
