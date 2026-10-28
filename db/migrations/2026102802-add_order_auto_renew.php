<?php

declare(strict_types=1);

use App\Interfaces\MigrationInterface;
use App\Services\DB;

return new class() implements MigrationInterface {
    public function up(): int
    {
        DB::getPdo()->exec('
        ALTER TABLE `order`
            ADD COLUMN IF NOT EXISTS `auto_renew` tinyint(1) NOT NULL DEFAULT 0,
            ADD KEY IF NOT EXISTS `auto_renew_status` (`auto_renew`, `status`);');

        return 2026102802;
    }

    public function down(): int
    {
        DB::getPdo()->exec('
        ALTER TABLE `order`
            DROP KEY IF EXISTS `auto_renew_status`,
            DROP COLUMN IF EXISTS `auto_renew`;');

        return 2026102801;
    }
};
