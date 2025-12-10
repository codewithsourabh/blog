<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/includes
 * @since      1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class BlogsHQ_Deactivator {

	/**
	 * Deactivation tasks.
	 */
	public static function deactivate() {
		self::clear_transients();
		self::clear_module_caches();
		
		wp_cache_flush();
		flush_rewrite_rules();
	}

	/**
	 * Clear all plugin transients.
	 */
	private static function clear_transients() {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_blogshq_' ) . '%',
				$wpdb->esc_like( '_transient_timeout_blogshq_' ) . '%'
			)
		);
	}

	/**
	 * Clear all module-specific caches.
	 */
	private static function clear_module_caches() {
		if ( class_exists( 'BlogsHQ_TOC' ) ) {
			$toc = new BlogsHQ_TOC();
			$toc->clear_all_caches();
		}

		wp_cache_delete( 'blogshq_toc_settings' );
		wp_cache_delete( 'blogshq_toc_settings_cache' );
		
		delete_transient( 'blogshq_categories' );
	}
}