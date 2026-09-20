<?php
declare(strict_types=1);

namespace WPSiteActivityLog\Log;

if (!defined('ABSPATH')) exit;

use WPSiteActivityLog\Core\DatabaseManager;

class LogQuery
{
    private static ?self $instance = null;
    private DatabaseManager $db;

    private function __construct()
    {
        $this->db = DatabaseManager::get_instance();
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize LogQuery');
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

    public function get_logs(array $args = []): array
    {
        global $wpdb;
        $table = $this->db->get_table_name();

        $event_code = $args['event_code'] ?? null;
        $object_type = $args['object_type'] ?? null;
        $user_id = $args['user_id'] ?? null;
        $date_from = $args['date_from'] ?? null;
        $date_to = $args['date_to'] ?? null;
        $search = $args['search'] ?? null;
        $per_page = (int)($args['per_page'] ?? 20);
        $page = (int)($args['page'] ?? 1);
        $orderby = $args['orderby'] ?? 'created_at';
        $order = $args['order'] ?? 'DESC';

        $allowed_orderby = ['id', 'created_at', 'event_code', 'object_type', 'object_name', 'user_name', 'ip_address'];
        if (!in_array($orderby, $allowed_orderby, true)) {
            $orderby = 'created_at';
        }
        $order = strtoupper((string)$order) === 'ASC' ? 'ASC' : 'DESC';

        $where = ['1=1'];

        if ($event_code) {
            $where[] = $wpdb->prepare('event_code = %s', $event_code);
        }

        if ($object_type) {
            $where[] = $wpdb->prepare('object_type = %s', $object_type);
        }

        if ($user_id) {
            $where[] = $wpdb->prepare('user_id = %d', $user_id);
        }

        if ($date_from) {
            $where[] = $wpdb->prepare('created_at >= %s', $date_from);
        }

        if ($date_to) {
            $where[] = $wpdb->prepare('created_at <= %s', $date_to);
        }

        if ($search) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where[] = $wpdb->prepare('(message LIKE %s OR user_name LIKE %s OR object_name LIKE %s)', $search_term, $search_term, $search_term);
        }

        $where_clause = implode(' AND ', $where);
        $offset = ($page - 1) * $per_page;

        $query = $wpdb->prepare(
            "SELECT * FROM {$table} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );

        return $wpdb->get_results($query);
    }

    public function get_total(array $args = []): int
    {
        global $wpdb;
        $table = $this->db->get_table_name();

        $event_code = $args['event_code'] ?? null;
        $object_type = $args['object_type'] ?? null;
        $user_id = $args['user_id'] ?? null;
        $date_from = $args['date_from'] ?? null;
        $date_to = $args['date_to'] ?? null;
        $search = $args['search'] ?? null;

        $where = ['1=1'];

        if ($event_code) {
            $where[] = $wpdb->prepare('event_code = %s', $event_code);
        }

        if ($object_type) {
            $where[] = $wpdb->prepare('object_type = %s', $object_type);
        }

        if ($user_id) {
            $where[] = $wpdb->prepare('user_id = %d', $user_id);
        }

        if ($date_from) {
            $where[] = $wpdb->prepare('created_at >= %s', $date_from);
        }

        if ($date_to) {
            $where[] = $wpdb->prepare('created_at <= %s', $date_to);
        }

        if ($search) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where[] = $wpdb->prepare('(message LIKE %s OR user_name LIKE %s OR object_name LIKE %s)', $search_term, $search_term, $search_term);
        }

        $where_clause = implode(' AND ', $where);
        return (int)$wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE {$where_clause}");
    }

    public function get_log(int $id): ?object
    {
        global $wpdb;
        $table = $this->db->get_table_name();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));
    }

    public function delete_log(int $id): bool
    {
        global $wpdb;
        $table = $this->db->get_table_name();
        return (bool)$wpdb->delete($table, ['id' => $id], ['%d']);
    }

    public function delete_old_logs(int $days): int
    {
        global $wpdb;
        $table = $this->db->get_table_name();

        if (!$this->db->table_exists()) {
            return 0;
        }

        $now_db = (string)$wpdb->get_var('SELECT NOW()');
        if ($now_db === '') {
            $now_db = gmdate('Y-m-d H:i:s');
        }

        $cutoff = gmdate('Y-m-d H:i:s', strtotime($now_db) - ($days * DAY_IN_SECONDS));
        return (int)$wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE created_at < %s", $cutoff));
    }

    public function export_csv(array $args = []): string
    {
        $logs = $this->get_logs($args);
        $csv = "ID,Event Code,Object Type,Object Name,User Name,IP Address,Created At,Message\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $log->id,
                $log->event_code,
                $log->object_type,
                $log->object_name,
                $log->user_name,
                $log->ip_address,
                $log->created_at,
                str_replace('"', '""', $log->message)
            );
        }

        return $csv;
    }
}
