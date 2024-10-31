<?php

/**
 * File containing the class Proofratings.
 *
 * @package proofratings
 * @since   1.0.1
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Handles core plugin hooks and action setup.
 *
 * @since 1.0.0
 */
class Proofratings {
	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since  1.0.1
	 */
	private static $instance = null;

	/**
	 * Main WP Proofratings Instance.
	 * Ensures only one instance of WP Job Manager is loaded or can be loaded.
	 *
	 * @since  1.0.1
	 * @static
	 * @return self Main instance.
	 */
	public static function instance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->add_proofratings_tables();

		include_once PROOFRATINGS_PLUGIN_DIR . '/inc/helpers.php';
		include_once PROOFRATINGS_PLUGIN_DIR . '/inc/review-sites.php';
		include_once PROOFRATINGS_PLUGIN_DIR . '/inc/rating-badges.php';
		include_once PROOFRATINGS_PLUGIN_DIR . '/inc/class-proofratings-query.php';
		include_once PROOFRATINGS_PLUGIN_DIR . '/inc/class-proofratings-generate-style.php';
		include_once PROOFRATINGS_PLUGIN_DIR . '/inc/class-proofratings-ratings.php';
		include_once PROOFRATINGS_PLUGIN_DIR . '/inc/class-proofratings-ajax.php';
		include_once PROOFRATINGS_PLUGIN_DIR . '/inc/class-proofratings-shortcodes.php';

		if (defined('PROOFRATINGS_DEMO') && PROOFRATINGS_DEMO) {
			include_once PROOFRATINGS_PLUGIN_DIR . '/inc/class-proofratings-demo.php';
		}

		$this->query = Proofratings_Query::instance();
		$this->shortcodes = Proofratings_Shortcodes::instance();

		if (is_admin()) {
			include_once PROOFRATINGS_PLUGIN_DIR . '/inc/class-proofratings-admin.php';
			$this->admin = Proofratings_Admin::instance();
		}

		add_action('rest_api_init', [$this, 'register_rest_api']);

		// Actions.
		add_action('init', [$this, 'load_plugin_textdomain']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

		add_action('wp_footer', [$this, 'banner_badge']);
		add_action('wp_footer', [$this, 'add_schema_markup']);
		add_action('wp_footer', [$this, 'overall_ratings_float']);

		add_action('init', [$this, 'register_scripts']);

		add_action("proofrating_location_save_settings", [$this, 'clear_cache']);

		add_filter('login_redirect', [$this, 'demo_user_redirect']);
	}

	/**
	 * add proofratings table in $wpdb
	 * @since 1.0.7
	 */
	function add_proofratings_tables() {
		global $wpdb;
		$wpdb->proofratings = $wpdb->prefix . 'proofratings';
	}

	/**
	 * Register proofratings script
	 * @since 1.1.7
	 */
	public function register_scripts() {
		wp_register_style('proofratings-fonts', PROOFRATINGS_PLUGIN_URL . '/assets/webfonts/fonts.css', [], PROOFRATINGS_VERSION);
	}

	/**
	 * proofratings rest api for getting data
	 */
	public function register_rest_api() {
		register_rest_route('proofratings/v1', 'get_settings', array(
			'methods' => 'GET',
			'callback' => [$this, 'get_settings_api_callback'],
			'permission_callback' => '__return_true'
		));

		register_rest_route('proofratings/v1', 'save_settings', array(
			'methods' => 'POST',
			'callback' => [$this, 'save_settings_callback'],
			'permission_callback' => '__return_true'
		));

		register_rest_route('proofratings/v1', 'set_reviews', array(
			'methods' => 'POST',
			'callback' => [$this, 'set_reviews'],
			'permission_callback' => '__return_true'
		));

		register_rest_route('proofratings/v1', 'get_location_settings', array(
			'methods' => 'GET',
			'callback' => [$this, 'get_location_settings'],
			'permission_callback' => '__return_true'
		));
	}

	/**
	 * proofratings rest api callback
	 */
	public function set_reviews(WP_REST_Request $request) {
		$review_locations = $request->get_param('locations');
		if (!is_array($review_locations)) {
			$review_locations = [];
		}

		global $wpdb;

		// For old location only
		foreach ($review_locations as $id => &$location) {
			if (!isset($location['old_location_id'])) {
				continue;
			}

			$get_old_location = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->proofratings WHERE location_id = %s", $location['old_location_id']));
			if ($get_old_location) {
				$wpdb->update($wpdb->proofratings, ['location_id' => $id], ['id' => $get_old_location->id]);
			}

			unset($location['old_location_id']);
		}

		foreach ($review_locations as $id => $location) {
			$reviews = null;
			if (isset($location['reviews']) && is_array($location['reviews']) && sizeof($location['reviews']) > 0) {
				$reviews = maybe_serialize($location['reviews']);
			}

			unset($location['reviews']);

			$location_data = array(
				'location_id' => $id,
				'location' => maybe_serialize($location),
				'status' => @$location['status']
			);

			if (!empty($reviews)) {
				$location_data['reviews'] = $reviews;
			}

			$get_location = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->proofratings WHERE location_id = '%s'", $id));
			if ($get_location) {
				$wpdb->update($wpdb->proofratings, $location_data, ['id' => $get_location->id]);
				continue;
			}

			$wpdb->insert($wpdb->proofratings, $location_data);
		}

		$locations = array_keys($review_locations);
		$wpdb->query(sprintf("DELETE FROM $wpdb->proofratings WHERE location_id NOT IN ('%s')", implode("','", $locations)));

		$settings = (array) $request->get_param('settings');
		if (isset($settings['agency'])) {
			$settings['agency'] = boolval($settings['agency']);
		}

		if (!isset($settings['connections_approved'])) {
			$settings['connections_approved'] = [];
		}

		update_proofratings_settings($settings);
		$this->clear_cache();
		wp_send_json_success();
	}

	/**
	 * Clear cache
	 * @since 1.1.2
	 */
	public function clear_cache() {
		if (function_exists('wp_cache_flush')) {
			wp_cache_flush();
		}

		// Super Cache Plugin - https://wordpress.org/plugins/wp-super-cache/
		if (function_exists('wp_cache_clear_cache')) {
			wp_cache_clear_cache(is_multisite() && is_plugin_active_for_network(PROOFRATINGS_PLUGIN_BASENAME) ? get_current_blog_id() : 0);
		}

		// W3 Total Cache Plugin - https://wordpress.org/plugins/w3-total-cache/
		if (function_exists('w3tc_flush_all')) {
			w3tc_flush_all();
		}

		// WP Fastest Cache Plugin - https://wordpress.org/plugins/wp-fastest-cache/
		if (isset($GLOBALS['wp_fastest_cache']) && method_exists($GLOBALS['wp_fastest_cache'], 'deleteCache')) {
			$GLOBALS['wp_fastest_cache']->deleteCache(true);
		}

		// Cache Enabler Plugin - https://wordpress.org/plugins/cache-enabler/
		if (class_exists('Cache_Enabler') && method_exists('Cache_Enabler', 'clear_site_cache')) {
			Cache_Enabler::clear_site_cache();
		}

		// SG Optimizer Plugin - https://wordpress.org/plugins/sg-cachepress/
		if (class_exists('\\SiteGround_Optimizer\\Supercacher\\Supercacher') && method_exists('\\SiteGround_Optimizer\\Supercacher\\Supercacher', 'purge_cache')) {
			\SiteGround_Optimizer\Supercacher\Supercacher::purge_cache();
		}

		// LiteSpeed Cache Plugin - https://wordpress.org/plugins/litespeed-cache/
		if (class_exists('\\LiteSpeed\\Purge') && method_exists('\\LiteSpeed\\Purge', 'purge_all')) {
			\LiteSpeed\Purge::purge_all('Purged by Proofratings');
		}

		// Nginx Helper Plugin - https://wordpress.org/plugins/nginx-helper/
		global $nginx_purger;
		if (is_a($nginx_purger, 'Purger') && method_exists($nginx_purger, 'purge_all')) {
			$nginx_purger->purge_all();
		}

		// WP Rocket Plugin - https://wp-rocket.me/
		if (function_exists('rocket_clean_domain')) {
			rocket_clean_domain();
		}

		// NitroPack - https://wordpress.org/plugins/nitropack/
		if (function_exists('nitropack_sdk_purge')) {
			nitropack_sdk_purge(NULL, NULL, 'Manual purge of all pages');
		}
	}

	/**
	 * proofratings rest api callback
	 */
	public function get_settings_api_callback(WP_REST_Request $request) {
		$settings = get_proofratings_settings();

		$locations = get_proofratings()->query->get_locations();

		$response = array(
			'site_url' => home_url(),
			'assets_url' => PROOFRATINGS_PLUGIN_URL . '/assets/',
			'review_sites' => get_proofratings_review_sites(),
			'global' => get_proofratings()->query->global,
			'locations' => $locations,
			'connections_approved' => $settings['connections_approved'],
			'agency' => $settings['agency'],
			'pages' => get_pages(),
			'stylesheet' => PROOFRATINGS_PLUGIN_URL . '/assets/css/proofratings.css'
		);

		$location_id = $request->get_param('location_id');
		if ($location_id === 'first_one') {
			$response['location_settings'] = [];
			if (count($locations) >= 0) {
				$response['location_settings'] = get_proofratings()->query->get($locations[0]['location_id']);
			}
		}

		return $response;
	}

	/**
	 * proofratings rest api callback
	 */
	public function get_location_settings(WP_REST_Request $request) {
		$this->clear_cache();

		$query = $this->query;


		$location = $query->get($request->get_param('location_id'));
		if (!$location) {
			return new WP_Error('location_404', 'No location found');
		}

		$location->settings->schema = get_proofratings_settings('schema');
		$location->settings->enable_schema = get_proofratings_settings('enable_schema');

		$settings = get_proofratings_settings();

		return array(
			'global' => $query->global,
			'reviews' => $location->reviews,
			'location_name' => $location->location['name'],
			'settings' => $location->settings,
			'proofratings' => array(
				'api' => PROOFRATINGS_API_URL,
				'site_url' => home_url(),
				'assets_url' => PROOFRATINGS_PLUGIN_URL . '/assets/',
				'review_sites' => get_proofratings_review_sites(),
				'pages' => get_pages(),
				'global' => $query->global,
				'locations' => $query->get_locations(),
				'connections_approved' => $settings['connections_approved'],
				'agency' => $settings['agency'],
				'stylesheet' => PROOFRATINGS_PLUGIN_URL . '/assets/css/proofratings.css'
			)
		);
	}

	/**
	 * proofratings rest api callback
	 */
	public function save_settings_callback(WP_REST_Request $request) {
		$client_settings = $request->get_param('client_settings');
		if ($client_settings) {
			update_proofratings_settings($client_settings);
		}

		$location_id = $request->get_param('location_id');
		$location_settings = $request->get_param('location_settings');

		if ($location_id && is_array($location_settings)) {
			$this->query->save_settings($location_id, $location_settings);
		}

		return true;
	}

	/**
	 * proofratings activate
	 */
	public function activate() {
		global $wpdb;

		update_option('proofratings_version', PROOFRATINGS_VERSION);

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		maybe_create_table($wpdb->proofratings, "CREATE TABLE $wpdb->proofratings (
			`id` INT NOT NULL AUTO_INCREMENT, 
			`location_id` VARCHAR(50) NULL,
			`location` MEDIUMTEXT NULL, 
			`reviews` LONGTEXT NULL, 
			`settings` LONGTEXT NULL, 
			`meta_data` LONGTEXT NULL, 
			`status` VARCHAR(20) NOT NULL DEFAULT 'pending', 
			`created_on` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
		);");

		$wpdb->query("ALTER TABLE $wpdb->proofratings CHANGE `location` `location` MEDIUMTEXT");
		$wpdb->query("ALTER TABLE $wpdb->proofratings CHANGE `settings` `settings` longtext");
	}


	/**
	 * Loads textdomain for plugin.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain('proofratings', false, PROOFRATINGS_PLUGIN_DIR . '/languages');
	}

	/**
	 * frontend CSS and JS assets.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style('proofratings', PROOFRATINGS_PLUGIN_URL . '/assets/css/proofratings.css', ['proofratings-fonts'], PROOFRATINGS_VERSION);
		wp_register_script('js-cookie', PROOFRATINGS_PLUGIN_URL .  '/assets/js/js.cookie.min.js', [], '3.0.1', true);

		$upload_dir = wp_upload_dir();
		$generated_css = $upload_dir['basedir'] . '/proofratings-generated.css';
		if (file_exists($generated_css)) {
			wp_enqueue_style('proofratings-generated', $upload_dir['baseurl'] . '/proofratings-generated.css', [], filemtime($generated_css));
		}

		wp_enqueue_script('proofratings', PROOFRATINGS_PLUGIN_URL . '/assets/js/proofratings.js', ['jquery', 'js-cookie'], PROOFRATINGS_VERSION, true);
		wp_localize_script('proofratings', 'proofratings', array('api' => PROOFRATINGS_API_URL, 'site_url' => get_site_url()));
	}

	/**
	 * Overrall Ratings Rectangle  badge on frontend
	 * @since 1.1.7
	 */
	public function add_schema_markup() {
		$query = get_proofratings()->query;
		$location = $query->get($query->get_global_id());
		if (!$location) {
			return;
		}

		$settings = get_proofratings_settings();
		if (!$settings['enable_schema']) {
			return;
		}

		$schema = get_proofratings_settings('schema');
		if (empty($schema)) {
			return;
		}

		$schema = str_replace('{aggregate rating}', $location->overall_reviews->rating, $schema);
		$schema = str_replace('{total review count}', $location->overall_reviews->reviews, $schema);
		$schema = json_decode($schema);
		if (is_object($schema)) {
			echo '<script id="proofratings-schema" type="application/ld+json">' . stripslashes(json_encode($schema)) . '</script>';
		}
	}

	/**
	 * Overrall Ratings Rectangle  badge on frontend
	 * @since 1.0.4
	 */
	public function overall_ratings_float() {
		$locations = get_proofratings()->query->locations;

		foreach ($locations as $location) {
			if (!isset($location->settings->badge_display['overall_rectangle_float']) || !$location->settings->badge_display['overall_rectangle_float']) {
				continue;
			}

			if (isset($_COOKIE['proofratings_badge_overall_rectangle_float_' . $location->id])) {
				continue;
			}

			$on_pages = [];
			if (isset($location->settings->overall_rectangle_float['on_pages'])) {
				$on_pages = $location->settings->overall_rectangle_float['on_pages'];
			}

			if (in_array(get_the_ID(), $on_pages)) {
				echo do_shortcode(sprintf('[proofratings_overall_rectangle id="%s" float="yes"]', esc_attr($location->location_id)));
			}
		}

		foreach ($locations as $location) {
			if (!isset($location->settings->badge_display['overall_narrow_float']) || !$location->settings->badge_display['overall_narrow_float']) {
				continue;
			}

			if (isset($_COOKIE['proofratings_badge_overall_narrow_float_' . $location->id])) {
				continue;
			}

			$on_pages = [];
			if (isset($location->settings->overall_narrow_float['on_pages'])) {
				$on_pages = $location->settings->overall_narrow_float['on_pages'];
			}

			if (in_array(get_the_ID(), $on_pages)) {
				echo do_shortcode(sprintf('[proofratings_overall_narrow id="%s" float="yes"]', esc_attr($location->location_id)));
			}
		}
	}

	/**
	 * Banner badge on frontend
	 */
	public function banner_badge() {
		$locations = get_proofratings()->query->locations;

		foreach ($locations as $location) {
			if (!isset($location->settings->badge_display['overall_cta_banner']) || !$location->settings->badge_display['overall_cta_banner']) {
				continue;
			}

			if (isset($_COOKIE['proofratings_badge_overall_cta_banner_' . $location->id])) {
				continue;
			}

			$on_pages = [];
			if (isset($location->settings->overall_cta_banner['on_pages'])) {
				$on_pages = $location->settings->overall_cta_banner['on_pages'];
			}


			if (in_array(get_the_ID(), $on_pages)) {
				echo do_shortcode(sprintf('[proofratings_overall_ratings_cta_banner id="%s"]', esc_attr($location->location_id)));
			}
		}
	}

	public function demo_user_redirect($redirect_to) {
		if (is_proofratings_demo_mode()) {
			return admin_url('admin.php?page=proofratings');
		}

		return $redirect_to;
	}
}
