<?php
/**
 * FILE: uninstall.php
 * Fired when the plugin is uninstalled.
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Verify that the current user has the capability to delete plugins
if ( ! current_user_can( 'delete_plugins' ) ) {
	exit;
}

/**
 * Delete plugin options.
 */
function blogshq_delete_options() {
	delete_option( 'blogshq_toc_headings' );
	delete_option( 'blogshq_toc_link_icon_enabled' );
	delete_option( 'blogshq_toc_link_icon_headings' );
	delete_option( 'blogshq_toc_link_icon_color' );
	delete_option( 'blogshq_activated_time' );
	delete_option( 'blogshq_version' );
}

/**
 * Delete plugin transients.
 */
function blogshq_delete_transients() {
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
 * Delete term meta (category logos).
 */
function blogshq_delete_term_meta() {
	global $wpdb;

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->termmeta} 
			WHERE meta_key LIKE %s",
			$wpdb->esc_like( 'blogshq_' ) . '%'
		)
	);
}

/**
 * Delete post meta (if any).
 */
function blogshq_delete_post_meta() {
	global $wpdb;

	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta} 
			WHERE meta_key LIKE %s",
			$wpdb->esc_like( 'blogshq_' ) . '%'
		)
	);
}

// Execute cleanup
blogshq_delete_options();
blogshq_delete_transients();
blogshq_delete_term_meta();
blogshq_delete_post_meta();

// Flush rewrite rules
flush_rewrite_rules();

// ============================================================================
