<?php
/**
 * Debugging utilities for BlogsHQ.
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/includes
 * @since      1.0.0
 */
class BlogsHQ_Debug {
    public static function log($message, $context = array()) {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        $timestamp = current_time('mysql');
        $formatted = sprintf(
            '[%s] BlogsHQ: %s %s',
            $timestamp,
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        
        error_log($formatted);
    }
    
    public static function query_monitor($query_count_before) {
        global $wpdb;
        $query_count_after = $wpdb->num_queries;
        self::log('Queries executed: ' . ($query_count_after - $query_count_before));
    }
}