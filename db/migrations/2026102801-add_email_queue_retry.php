<?php

declare(strict_types=1);

use App\Interfaces\MigrationInterface;
use App\Services\DB;

return new class() implements MigrationInterface {
    public function up(): int
    {
        DB::getPdo()->exec('
        ALTER TABLE email_queue
            ADD COLUMN IF NOT EXISTS `attempts` int unsigned NOT NULL DEFAULT 0,
            ADD COLUMN IF NOT EXISTS `next_attempt_at` int unsigned NOT NULL DEFAULT 0,
            ADD COLUMN IF NOT EXISTS `last_error` varchar(1000) NOT NULL DEFAULT "",
            ADD KEY IF NOT EXISTS `ready_queue` (`next_attempt_at`, `id`);');

        return 2026102801;
    }

    public function down(): int
    {
        DB::getPdo()->exec('
        ALTER TABLE email_queue
            DROP KEY IF EXISTS `ready_queue`,
            DROP COLUMN IF EXISTS `attempts`,
            DROP COLUMN IF EXISTS `next_attempt_at`,
            DROP COLUMN IF EXISTS `last_error`;');

        return 2026102800;
    }
};
