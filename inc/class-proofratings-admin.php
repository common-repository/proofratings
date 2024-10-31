<?php
/**
 * File containing the class Proofratings_Admin.
 *
 * @package proofratings
 * @since   1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles core plugin hooks and action setup.
 *
 * @since 1.0.0
 */
class Proofratings_Admin {

	/**
	 * The single instance of the class.
	 * @var self
	 * @since  1.0.1
	 */
	private static $instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 *
	 * @since  1.0.1
	 * @static
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Capability for settings
	 * @var self
	 * @since  1.0.1
	 */
	private $capability = 'manage_options';

	/**
	 * Constructor.
	 */
	public function __construct() {
		include_once dirname( __FILE__ ) . '/class-proofratings-settings.php';

		if ( defined('PROOFRATINGS_DEMO') && PROOFRATINGS_DEMO ) {
			$this->capability = 'read';
		}
		
		$this->settings_page = Proofratings_Settings::instance();
		$this->analytics = include_once dirname( __FILE__ ) . '/class-proofratings-analytics.php';
		
		if ( ! defined( 'DISABLE_NAG_NOTICES' ) || ! DISABLE_NAG_NOTICES ) {
			add_action( 'admin_notices', [$this, 'admin_notice_rating_us']);
		}
		
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

		add_filter( 'admin_body_class', [$this, 'body_class']);
	}

	function body_class($classes) {
		$screen = get_current_screen();
		if ( strpos($screen->id, 'proofratings') === false ) {
			return $classes;
		}

		$classes .= ' screen-proofratings';	 
		return $classes;
	}

	function admin_notice_rating_us() {
		$feedback_hide = get_option( 'proofratings_feedback_hide');
		if ( $feedback_hide || isset($_COOKIE['proofratings_feedback_hide'])) {
			return;
		} ?>
		<div id="proofrating-notice" class="notice notice-info is-dismissible">
			<p>We are excited that you chose Proofratings to display your reputation. We are working hard around the clock to continually help you convert more website visitors and increase sales. Would you please take 2 minutes to leave us a review?</p>
			<div class="btn-actions">
				<a href="https://wordpress.org/support/plugin/proofratings/reviews/" target="_blank"><span class="dashicons dashicons-external"></span> Yes, of course!</a> |
				<a href="#" data-days="28"><span class="dashicons dashicons-calendar-alt"></span> Maybe later</a> |
				<a href="#" data-days="90">Not quite yet!</a> |
				<a href="#"><span class="dashicons dashicons-dismiss"></span> No thank you</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Add menu page
	 */
	public function admin_menu() {
		$capability = 'manage_options';
		if ( is_proofratings_demo_mode() ) {
			$capability = 'read';
		}
		
		$main_screen = [$this->settings_page, 'license_page'];
		if (is_proofratings_active()) {
			$main_screen = [$this->settings_page, 'main_menu'];
		}
		
		$proofratings_icon = PROOFRATINGS_PLUGIN_URL . '/assets/images/proofratings-icon.png';

		$rating_badges = new \Proofratings_Admin\Rating_Badges();


		add_menu_page(__('Proofratings', 'proofratings'), __('Proofratings', 'proofratings'), $capability, 'proofratings', $main_screen, $proofratings_icon, 25);

		if (is_proofratings_active()) {
			add_submenu_page('proofratings', __('Proofratings', 'proofratings'), __('Proofratings', 'proofratings'), $capability, 'proofratings', $main_screen);
			add_submenu_page('proofratings', __('Proofratings Analytics', 'proofratings'), __('Analytics', 'proofratings'), $capability, 'proofratings-analytics', [$this->analytics, 'output']);
		
			add_submenu_page('proofratings', $rating_badges->get_menu_label(), $rating_badges->get_menu_label(), $capability, $rating_badges->menu_slug, [$rating_badges, 'render']);
			

			add_submenu_page('proofratings', __('Settings', 'proofratings'), __('Settings', 'proofratings'), $capability, 'proofratings-settings', [$this->settings_page, 'settings']);
			add_submenu_page('proofratings', __('Support', 'proofratings'), __('Support', 'proofratings'), $capability, 'proofratings-support', [$this->settings_page, 'support']);
			add_submenu_page('proofratings', __('Billing', 'proofratings'), __('Billing', 'proofratings'), $capability, 'proofratings-billing', [$this->settings_page, 'billing']);

			add_submenu_page('', __('Edit Location', 'proofratings'), __('Edit Location', 'proofratings'), $capability, 'proofratings-edit-location', [$this->settings_page, 'edit_location']);
		}
	}

	/**
	 * Enqueues CSS and JS assets.
	 */
	public function admin_enqueue_scripts() {
		if ( WP_DEBUG ) {
			wp_deregister_script( 'react' );
			wp_deregister_script( 'react-dom' );
			wp_register_script('react', 'https://unpkg.com/react@17.0.1/umd/react.development.js', []);
			wp_register_script('react-dom', 'https://unpkg.com/react-dom@17.0.1/umd/react-dom.development.js', []);
		}

		wp_enqueue_style( 'proofratings-dashboard', PROOFRATINGS_PLUGIN_URL . '/assets/css/proofratings-dashboard.css', [], PROOFRATINGS_VERSION);

		wp_register_script( 'popper', PROOFRATINGS_PLUGIN_URL . '/assets/js/popper.min.js', [], '2.11.5', true);
		wp_register_script( 'tippy', PROOFRATINGS_PLUGIN_URL . '/assets/js/tippy.js', ['popper'], 6, true);

		$settings = get_proofratings_settings();
		

		wp_enqueue_script( 'proofratings-dashboard', PROOFRATINGS_PLUGIN_URL . '/assets/js/proofratings-dashboard.js', ['jquery'], PROOFRATINGS_VERSION, true);
		wp_localize_script( 'proofratings-dashboard', 'proofratings', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'api' => PROOFRATINGS_API_URL,
			'site_url' => home_url(),
			'assets_url' => PROOFRATINGS_PLUGIN_URL . '/assets/',
			'review_sites' => get_proofratings_review_sites(),
			'pages' => get_pages(),
			'global' => get_proofratings()->query->global,
			'locations' => get_proofratings()->query->get_locations(),
			'connections_approved' => $settings['connections_approved'],
			'agency' => $settings['agency'],
		));

		$screen = get_current_screen();
		if ( strpos($screen->id, 'proofratings') === false ) {
			return;
		}

		add_action('in_admin_header', function () {
			remove_all_actions('admin_notices');
			remove_all_actions('all_admin_notices');
		}, 1000);

		
		
		preg_match('/(proofratings_page|proofratings-widgets|proofratings-edit-location)/', $screen->id, $matches);

		wp_register_style('fontawesome', PROOFRATINGS_PLUGIN_URL . '/assets/css/fontawesome.css', [], '6.1.1');
		
		if ( $screen->id == 'toplevel_page_proofratings' || $matches  ) {
			wp_enqueue_style( 'proofratings-frontend', PROOFRATINGS_PLUGIN_URL . '/assets/css/proofratings.css', ['wp-color-picker', 'fontawesome', 'proofratings-fonts'], PROOFRATINGS_VERSION);
			wp_enqueue_style( 'proofratings', PROOFRATINGS_PLUGIN_URL . '/assets/css/proofratings-admin.css', ['wp-color-picker'], PROOFRATINGS_VERSION);

			wp_enqueue_script('sweetalert2', '//cdn.jsdelivr.net/npm/sweetalert2@11', [], 11, true);
			wp_enqueue_script('jquery-card-validation', PROOFRATINGS_PLUGIN_URL . '/assets/js/jquery.creditCardValidator.js', ['jquery'], '1.2', true);
			wp_enqueue_script('jquery-mask', PROOFRATINGS_PLUGIN_URL . '/assets/js/jquery.mask.min.js', [], '1.14.16', true);
			wp_enqueue_script( 'proofratings', PROOFRATINGS_PLUGIN_URL . '/assets/js/proofratings-admin.js', ['jquery', 'wp-util', 'wp-color-picker', 'tippy', 'jquery-mask', 'jquery-card-validation'], PROOFRATINGS_VERSION, true);
			wp_localize_script('proofratings', 'proofratings_admin', array(
				'api' => PROOFRATINGS_API_URL,
				'ajax_url' => admin_url('admin-ajax.php'),
			));
		}

		preg_match('/(proofratings-rating-badges)/', $screen->id, $widget_matches);		
		if ( $widget_matches ) {
			wp_enqueue_script( 'proofratings-widgets', PROOFRATINGS_PLUGIN_URL . '/assets/js/widgets.js', ['react', 'react-dom'], PROOFRATINGS_VERSION, true);
		}

		preg_match('/proofratings-settings/', $screen->id, $matches_settings);
		if ( $matches_settings ) {
			wp_enqueue_script( 'proofratings-settings', PROOFRATINGS_PLUGIN_URL . '/assets/js/settings.js', ['react', 'react-dom'], PROOFRATINGS_VERSION, true);
		}
	}
}
