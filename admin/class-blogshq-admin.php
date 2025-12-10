<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    BlogsHQ
 * @subpackage BlogsHQ/admin
 * @since      1.0.0
 */

if (! defined('WPINC')) {
	die;
}

class BlogsHQ_Admin
{
	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->init_ajax_handlers();
		
		add_filter('heartbeat_settings', array($this, 'optimize_heartbeat'));
	}

	public function optimize_heartbeat($settings)
	{
		if (is_admin() && function_exists('get_current_screen')) {
			$screen = get_current_screen();
			if ($screen && strpos($screen->id, 'blogshq') !== false) {
				$settings['interval'] = 60;
			}
		}
		return $settings;
	}

	public function init_ajax_handlers()
	{
		add_action('wp_ajax_blogshq_load_tab', array($this, 'ajax_load_tab'));
		add_action('wp_ajax_blogshq_save_settings', array($this, 'ajax_save_settings'));
	}

	public function ajax_load_tab()
	{
		check_ajax_referer('blogshq_admin_nonce', 'nonce');

		if (! current_user_can('manage_options')) {
			wp_send_json_error(array(
				'message' => __('You do not have permission to perform this action.', 'blogshq')
			));
		}

		$tab = isset($_POST['tab']) ? sanitize_key($_POST['tab']) : 'logos';
		// FAQ removed from valid tabs
		$valid_tabs = array('logos', 'toc', 'ai-share');

		if (! in_array($tab, $valid_tabs, true)) {
			wp_send_json_error(array(
				'message' => __('Invalid tab selected.', 'blogshq')
			));
		}

		ob_start();
		blogshq_get_template($tab);
		$content = ob_get_clean();

		wp_send_json_success(array(
			'content' => $content,
			'tab' => $tab
		));
	}

	public function ajax_save_settings()
	{
		$nonce_actions = array(
			'blogshq_logos_nonce' => 'blogshq_logos_settings',
			'blogshq_toc_nonce' => 'blogshq_toc_settings',
		);

		$nonce_verified = false;
		foreach ($nonce_actions as $nonce_field => $nonce_action) {
			if (isset($_POST[$nonce_field]) && wp_verify_nonce($_POST[$nonce_field], $nonce_action)) {
				$nonce_verified = true;
				break;
			}
		}

		if (! $nonce_verified) {
			wp_send_json_error(array(
				'message' => __('Security check failed.', 'blogshq')
			));
		}

		if (! current_user_can('manage_options')) {
			wp_send_json_error(array(
				'message' => __('You do not have permission to perform this action.', 'blogshq')
			));
		}

		try {
			if (isset($_POST['form_type'])) {
				switch ($_POST['form_type']) {
					case 'category_logo':
						$this->process_logos_save();
						$message = __('Logos saved successfully.', 'blogshq');
						break;
					case 'toc':
						$this->process_toc_save();
						$message = __('TOC settings saved successfully.', 'blogshq');
						break;
					default:
						wp_send_json_error(array(
							'message' => __('Unknown form type.', 'blogshq')
						));
				}
			} else {
				wp_send_json_error(array(
					'message' => __('Unknown form type.', 'blogshq')
				));
			}

			wp_send_json_success(array(
				'message' => $message
			));
		} catch (Exception $e) {
			wp_send_json_error(array(
				'message' => $e->getMessage()
			));
		}
	}

	private function process_logos_save()
	{
		$categories = get_categories(array('hide_empty' => false));
		
		$category_ids = wp_list_pluck($categories, 'term_id');
		update_meta_cache('term', $category_ids);

		foreach ($categories as $cat) {
			$cat_id = absint($cat->term_id);

			$light_url = '';
			if (isset($_POST['logo_url_light'][$cat_id])) {
				$url = esc_url_raw($_POST['logo_url_light'][$cat_id]);
				if ($url && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $url)) {
					$light_url = $url;
				}
			}

			$dark_url = '';
			if (isset($_POST['logo_url_dark'][$cat_id])) {
				$url = esc_url_raw($_POST['logo_url_dark'][$cat_id]);
				if ($url && preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $url)) {
					$dark_url = $url;
				}
			}

			update_term_meta($cat_id, 'blogshq_logo_url_light', $light_url);
			update_term_meta($cat_id, 'blogshq_logo_url_dark', $dark_url);
		}

		delete_transient('blogshq_categories');
	}

	private function process_toc_save()
	{
		$allowed_headings = array('h2', 'h3', 'h4', 'h5', 'h6');
		
		$headings_raw = isset($_POST['toc_headings']) && is_array($_POST['toc_headings'])
			? array_map('sanitize_key', $_POST['toc_headings'])
			: array();
		$headings = array_intersect($headings_raw, $allowed_headings);
		update_option('blogshq_toc_headings', $headings);

		$link_icon_enabled = isset($_POST['link_icon_enabled']);
		update_option('blogshq_toc_link_icon_enabled', $link_icon_enabled);

		$icon_headings_raw = isset($_POST['link_icon_headings']) && is_array($_POST['link_icon_headings'])
			? array_map('sanitize_key', $_POST['link_icon_headings'])
			: array();
		$icon_headings = array_intersect($icon_headings_raw, $allowed_headings);
		update_option('blogshq_toc_link_icon_headings', $icon_headings);

		$color = isset($_POST['link_icon_color']) ? sanitize_hex_color($_POST['link_icon_color']) : '#2E62E9';
		update_option('blogshq_toc_link_icon_color', $color);

		wp_cache_delete('blogshq_toc_settings');
		wp_cache_delete('blogshq_toc_settings_cache');
	}

	public function enqueue_styles()
	{
		if (! $this->is_plugin_page()) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_name,
			BLOGSHQ_PLUGIN_URL . 'admin/css/admin.min.css',
			array(),
			$this->version,
			'all'
		);

		wp_enqueue_style('wp-color-picker');
	}

	public function enqueue_scripts()
	{
		if (! $this->is_plugin_page()) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			BLOGSHQ_PLUGIN_URL . 'admin/js/admin.min.js',
			array('jquery', 'wp-color-picker'),
			$this->version,
			false
		);

		wp_localize_script(
			$this->plugin_name,
			'blogshqAdmin',
			array(
				'ajaxurl' => admin_url('admin-ajax.php'),
				'nonce'   => wp_create_nonce('blogshq_admin_nonce'),
				'strings' => array(
					'confirm_delete' => __('Are you sure you want to delete this?', 'blogshq'),
					'saved'          => __('Settings saved successfully.', 'blogshq'),
					'saving'         => __('Saving...', 'blogshq'),
					'error'          => __('An error occurred. Please try again.', 'blogshq'),
				),
			)
		);
	}

	private function is_plugin_page()
	{
		if (!is_admin() || !function_exists('get_current_screen')) {
			return false;
		}
		
		$screen = get_current_screen();
		if (!$screen) {
			return false;
		}

		return strpos($screen->id, 'blogshq') !== false;
	}

	public function add_plugin_admin_menu()
	{
		add_menu_page(
			__('BlogsHQ Admin Toolkit', 'blogshq'),
			__('BlogsHQ', 'blogshq'),
			'manage_options',
			$this->plugin_name,
			array($this, 'display_plugin_dashboard'),
			'dashicons-admin-generic',
			26
		);

		add_submenu_page(
			$this->plugin_name,
			__('Dashboard', 'blogshq'),
			__('Dashboard', 'blogshq'),
			'manage_options',
			$this->plugin_name,
			array($this, 'display_plugin_dashboard')
		);
	}

	public function display_plugin_dashboard()
	{
		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'blogshq'));
		}

		$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'logos';

		// FAQ removed from valid tabs
		$valid_tabs = array('logos', 'toc', 'ai-share');
		if (! in_array($active_tab, $valid_tabs, true)) {
			$active_tab = 'logos';
		}

		blogshq_get_template('dashboard', null, array('active_tab' => $active_tab));
	}

	public function add_action_links($links)
	{
		$settings_link = array(
			'<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Settings', 'blogshq') . '</a>',
		);

		return array_merge($settings_link, $links);
	}

	public function process_form_submission()
	{
		if (! isset($_POST['blogshq_action'])) {
			return;
		}

		if (! isset($_POST['blogshq_nonce']) || ! wp_verify_nonce($_POST['blogshq_nonce'], 'blogshq_settings')) {
			wp_die(esc_html__('Security check failed.', 'blogshq'));
		}

		if (! current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions.', 'blogshq'));
		}

		$action = sanitize_key($_POST['blogshq_action']);

		do_action('blogshq_before_form_processing', $action);

		switch ($action) {
			case 'save_logos':
				$this->handle_logos_save();
				break;
			case 'save_toc':
				$this->handle_toc_save();
				break;
			default:
				do_action('blogshq_handle_form_action', $action);
				break;
		}
	}

	private function handle_logos_save()
	{
		do_action('blogshq_save_logos');
	}

	private function handle_toc_save()
	{
		do_action('blogshq_save_toc');
	}

	private function handle_ai_share_save()
	{
		do_action('blogshq_save_ai_share');
	}
}