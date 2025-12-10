<?php

/**
 * FILE: includes/class-blogshq-i18n.php
 * Define internationalization functionality.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class BlogsHQ_I18n {

	/**
	 * Load plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			BLOGSHQ_TEXT_DOMAIN,
			false,
			dirname( BLOGSHQ_BASENAME ) . '/languages/'
		);
	}
}
