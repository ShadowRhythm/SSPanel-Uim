<?php

declare(strict_types=1);

use App\Interfaces\MigrationInterface;
use App\Services\DB;

return new class() implements MigrationInterface {
    public function up(): int
    {
        DB::getPdo()->exec('
        ALTER TABLE `order` ADD KEY IF NOT EXISTS `status_product_type` (`status`, `product_type`);
        ALTER TABLE `order` ADD KEY IF NOT EXISTS `user_id_status` (`user_id`, `status`);
        ALTER TABLE `user` ADD KEY IF NOT EXISTS `class_class_expire` (`class`, `class_expire`);
        ALTER TABLE `user` ADD KEY IF NOT EXISTS `auto_reset_day_class` (`auto_reset_day`, `class`);
        ALTER TABLE `user_money_log` ADD KEY IF NOT EXISTS `user_id_create_time` (`user_id`, `create_time`);
        ALTER TABLE `detect_ban_log` ADD KEY IF NOT EXISTS `user_id_id` (`user_id`, `id`);
        ALTER TABLE `node` ADD KEY IF NOT EXISTS `type_node_class` (`type`, `node_class`);');

        return 2026110300;
    }

    public function down(): int
    {
        DB::getPdo()->exec('
        ALTER TABLE `order` DROP KEY IF EXISTS `status_product_type`;
        ALTER TABLE `order` DROP KEY IF EXISTS `user_id_status`;
        ALTER TABLE `user` DROP KEY IF EXISTS `class_class_expire`;
        ALTER TABLE `user` DROP KEY IF EXISTS `auto_reset_day_class`;
        ALTER TABLE `user_money_log` DROP KEY IF EXISTS `user_id_create_time`;
        ALTER TABLE `detect_ban_log` DROP KEY IF EXISTS `user_id_id`;
        ALTER TABLE `node` DROP KEY IF EXISTS `type_node_class`;');

        return 2026110200;
    }
};
