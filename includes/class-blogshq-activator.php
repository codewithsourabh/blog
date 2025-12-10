<?php
/**
 * FILE: includes/class-blogshq-activator.php
 * Fired during plugin activation.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class BlogsHQ_Activator {

	/**
	 * Activation tasks.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		// Set default options
		self::set_default_options();

		// Create custom database tables if needed
		// self::create_tables();

		// Flush rewrite rules
		flush_rewrite_rules();

		// Set activation timestamp
		update_option( 'blogshq_activated_time', time() );
		update_option( 'blogshq_version', BLOGSHQ_VERSION );
	}

	/**
	 * Set default plugin options.
	 *
	 * @since 1.0.0
	 */
	private static function set_default_options() {
		// TOC default settings
		if ( false === get_option( 'blogshq_toc_headings' ) ) {
			update_option( 'blogshq_toc_headings', array( 'h2', 'h3', 'h4', 'h5', 'h6' ) );
		}

		if ( false === get_option( 'blogshq_toc_link_icon_enabled' ) ) {
			update_option( 'blogshq_toc_link_icon_enabled', false );
		}

		if ( false === get_option( 'blogshq_toc_link_icon_headings' ) ) {
			update_option( 'blogshq_toc_link_icon_headings', array( 'h2' ) );
		}

		if ( false === get_option( 'blogshq_toc_link_icon_color' ) ) {
			update_option( 'blogshq_toc_link_icon_color', '#2E62E9' );
		}
	}
}