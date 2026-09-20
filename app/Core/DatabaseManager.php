<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Core;

if (!defined('ABSPATH')) exit;

class DatabaseManager
{
    private static ?self $instance = null;

    private function __construct() {}

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize DatabaseManager');
    }

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void
    {
    }

    public function get_table_name(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'wsal_activity_log';
    }

    public function table_exists(): bool
    {
        global $wpdb;
        $table = $this->get_table_name();
        return $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) === $table;
    }

    public function ensure_schema(): void
    {
        if (!$this->table_exists()) {
            $this->install();
        }
    }

    public function install(): void
    {
        global $wpdb;
        $table = $this->get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_code VARCHAR(100) NOT NULL,
            object_type VARCHAR(50) NOT NULL,
            object_name VARCHAR(255) NOT NULL,
            object_id BIGINT UNSIGNED DEFAULT NULL,
            user_id BIGINT UNSIGNED DEFAULT NULL,
            user_name VARCHAR(100) DEFAULT NULL,
            user_role VARCHAR(100) DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            message TEXT NOT NULL,
            meta LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_event_code (event_code),
            INDEX idx_object_type (object_type),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function insert(array $data): int|false
    {
        global $wpdb;
        $table = $this->get_table_name();

        $result = $wpdb->insert($table, $data, [
            '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
        ]);

        return $result ? $wpdb->insert_id : false;
    }
}
