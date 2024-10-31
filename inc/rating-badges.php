<?php
namespace Proofratings_Admin;
/**
 * @package proofratings
 * @since   1.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rating Badges
 * @since 1.1.6
 */
class Rating_Badges {
    /**
	 * Menu Slug
     * @since  1.1.6
	 */
    var $menu_slug = 'proofratings-rating-badges';

	/**
	 * Constructor.
     * @since  1.0.6
	 */
	public function __construct() {	
		add_action( 'init', [$this, 'handle_delete_action']);
	}

    /**
	 * Get dashboard menu name
     * @since  1.0.7
	 */
	public function get_menu_label() {
		return get_proofratings()->query->global ? __('Rating Badges', 'proofratings') :  __('Locations & Rating Badges', 'proofratings');
    }

	/**
	 * handle bulk action
     * @since  1.0.6
	 */
	public function handle_delete_action() {
		$data = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

		if ( !isset($data['_nonce']) || empty($data['id'])) {
			return;
		}

		if( !wp_verify_nonce($data['_nonce'], 'delete-location' ) ) {
			return;
		}

		global $wpdb;
		$wpdb->update($wpdb->proofratings,  ['status' => 'deleted'],  ['id' => $data['id']]);

		exit(wp_safe_redirect(admin_url( 'admin.php?page=' . 'proofratings-locations')));
	}

	/**
	 * Render Page
	 * @since  1.0.6
	 */
	public function render() {
		$data = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

		$location_id = isset($data['location']) ? $data['location'] : false;
		if ( get_proofratings()->query->global ) {
			$location_id = get_proofratings()->query->get_global_id();
		}

		$this->widgets_settings($location_id);
	}	

	/**
	 * Shows locations
	 * @since  1.0.6
	 */
	public function output() {	
		include_once PROOFRATINGS_PLUGIN_DIR . '/inc/class-proofratings-locations-table.php';
		$location_table = new \Proofratings_Locations_Table();
		$location_table->prepare_items(); ?>

		<div class="wrap proofratings-settings-wrap">
			<header class="proofratins-header header-row">
				<div class="header-left">
					<a class="btn-back-main-menu" href="<?php menu_page_url( 'proofratings' ) ?>"><i class="icon-back fa-solid fa-angle-left"></i> Back to Main Menu</a>
					<h1 class="title"><?php _e('Locations & Rating Badges', 'proofratings') ?></h1>
				</div>
				
				<div class="header-right">
					<a class="btn-support fa-regular fa-circle-question" href="<?php menu_page_url( 'proofratings-support' ) ?>"></a>
				</div>
			</header>

            <form method="post">
                <?php $location_table->display(); ?>
            </form>
		</div>
		<?php
	}

	/**
	 * Shows locations
	 * @since  1.0.6
	 */
	public function widgets_settings($location_id) {
		global $wpdb;

		$location = get_proofratings()->query->get($location_id);

		if ( !$location ) {
			return $this->output();
		}

		$location_name = 'Overall';
		if ( $location ) {
			$location_name = $location->location;
		} ?>

		<div class="wrap proofratings-settings-wrap">
			<div id="proofratings-root" data-location=<?php echo esc_attr($location_id) ?>></div>
			<div id="toast-proofratings"></div>
		</div>
		<?php
	}
}