<?php

declare(strict_types=1);

use App\Interfaces\MigrationInterface;
use App\Services\DB;

return new class() implements MigrationInterface {
    public function up(): int
    {
        DB::getPdo()->exec("
        ALTER TABLE `node` ADD COLUMN IF NOT EXISTS `restart_pending` tinyint(1) unsigned NOT NULL DEFAULT 0 COMMENT '待重启标志';");

        return 2026111102;
    }

    public function down(): int
    {
        DB::getPdo()->exec('
        ALTER TABLE `node` DROP COLUMN IF EXISTS `restart_pending`;');

        return 2026111101;
    }
};
