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

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            event_code varchar(100) NOT NULL,
            object_type varchar(50) NOT NULL,
            object_name varchar(255) NOT NULL,
            object_id bigint(20) unsigned DEFAULT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            user_name varchar(100) DEFAULT NULL,
            user_role varchar(100) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            message text NOT NULL,
            meta longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY idx_event_code (event_code),
            KEY idx_object_type (object_type),
            KEY idx_user_id (user_id),
            KEY idx_created_at (created_at)
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
