<?php
/**
 * Plugin Name: Proofratings
 * Plugin URI: https://proofratings.com
 * Description: Proofratings monitors all your third party sites for reviews by your customers. Sharing review ratings badges on your website increases conversions.
 * Version: 1.1.9
 * Author: Proofratings
 * Requires at least: 5.2
 * Tested up to: 6.0.1
 * Requires PHP: 7.0
 * Text Domain: proofratings
 * Domain Path: /languages/
 * License: GPL2+
 *
 * @package proofratings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants.
define( 'PROOFRATINGS_VERSION', '1.1.9' );
define( 'PROOFRATINGS_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'PROOFRATINGS_PLUGIN_URL', untrailingslashit(plugin_dir_url(__FILE__)));
define( 'PROOFRATINGS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( !defined('PROOFRATINGS_API_URL') ) {
  define( 'PROOFRATINGS_API_URL', 'https://proofratings.com/wp-json/proofratings-manager/v1');
}

require_once dirname( __FILE__ ) . '/inc/class-proofratings.php';


/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_proofratings() {

    if ( ! class_exists( 'Appsero\Client' ) ) {
      require_once PROOFRATINGS_PLUGIN_DIR . '/appsero/Client.php';
    }

    $client = new Appsero\Client( '932d86b7-ff6f-4437-b713-244c7458cfda', 'Proofratings', __FILE__ );

    // Active insights
    $client->insights()->init();
}

appsero_init_tracker_proofratings();


/**
 * Main instance of Wordpress Proofratings.
 * Returns the main instance of Proofratings to prevent the need to use globals.
 * @since  1.0.1
 * @return Proofratings
 */
function get_proofratings() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
	return Proofratings::instance();
}

register_activation_hook( PROOFRATINGS_PLUGIN_BASENAME, array( get_proofratings(), 'activate' ) );