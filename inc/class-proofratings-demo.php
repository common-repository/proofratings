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
class Demo {
	/**
	 * Constructor.
     * @since  1.0.6
	 */
	public function __construct() {
        add_action( 'init', [$this, 'init'], 333333);        
	}

    public function init() {
        if ( !is_proofratings_demo_mode() ) {
			return;
		}

        remove_action('in_admin_footer', 'proofratings_review_us', 11);

        remove_action( 'in_admin_header', 'wp_admin_bar_render', 0);
        add_filter( 'show_admin_bar', '__return_false' );

        add_action('admin_menu', [$this, 'hide_menu'], 333);
        add_action( 'admin_footer', [$this, 'show_demo_notification']);
    }

    /**
	 * Get dashboard menu name
     * @since  1.0.7
	 */
	public function hide_menu() {
        remove_menu_page( 'index.php' );
        remove_menu_page( 'profile.php' );
    }

    public function show_demo_notification() { ?>
        <div class="proofratings-demo-message">            
            <div class="demo-message">Proofratings is running in Demo Mode.</div>
        </div>

        <style>
            html.wp-toolbar {padding-top: 0}
        </style>
        <?php
    }
}

return new Demo();