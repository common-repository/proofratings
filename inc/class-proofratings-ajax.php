<?php
/**
 * File containing the class Proofratings_Ajax.
 *
 * @package proofratings
 * @since   1.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles core plugin hooks and action setup.
 *
 * @since 1.0.6
 */
class Proofratings_Ajax {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_proofratings_notice_feedback', [$this, 'notice_feedback']);
		add_action( 'wp_ajax_nopriv_proofratings_notice_feedback', [$this, 'notice_feedback']);	


		add_action( 'wp_ajax_save_proofratings_location_settings', [$this, 'save_location_settings']);
		add_action( 'wp_ajax_nopriv_save_proofratings_location_settings', [$this, 'save_location_settings']);

		add_action( 'wp_ajax_get_proofratings_location_settings', [$this, 'get_location_settings']);
		add_action( 'wp_ajax_nopriv_get_proofratings_location_settings', [$this, 'get_location_settings']);
	}

	public function notice_feedback() {
		$postdata = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

		if ( !isset($postdata['days']) ) {
			update_option('proofratings_feedback_hide', true);
			wp_send_json_success();
		}

		$days = $postdata['days'];
		setcookie("proofratings_feedback_hide", true, strtotime("+ $days days"));
		wp_send_json_success();
	}

	public function get_location_settings() {
		$postdata = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		
		$location = @$postdata['location_id'];
		if ( empty($location)) {
			wp_send_json_error();
		}
		
		$location = get_proofratings()->query->get($location);
		if ( !$location ) {
			wp_send_json_error();
		}

		$location->settings->schema = get_proofratings_settings('schema');
		$location->settings->enable_schema = get_proofratings_settings('enable_schema');

		wp_send_json(array(
			'global' => get_proofratings()->query->global,
			'reviews' => $location->reviews,
			'location_name' => $location->location['name'],
			'settings' => $location->settings,
		));
	}

	public function save_location_settings() {
		$post_data = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		unset($post_data['action'], $post_data['settings_tab']);

		$active_connections = [];
		if ( isset($post_data['active_connections']) && is_array($post_data['active_connections'])) {
			$active_connections = $post_data['active_connections'];
		}

		foreach ($active_connections as $key => $connection) {
			if ( sizeof($connection) === 1 && isset($connection['active']) && $connection['active'] == false) {
				unset($active_connections[$key]);
			}
		}
		
		$location_id = false;
		if ( isset($post_data['location_id']) ) {
			$location_id = $post_data['location_id'];
			unset($post_data['location_id']);
		}

		$settings = array('schema' => '', 'enable_schema' => '');
		foreach (array_keys($settings) as $key_id) {
			if ( !isset($post_data[$key_id]) ) {
				continue;				
			}

			$settings[$key_id] = $post_data[$key_id];
			unset($post_data[$key_id]);			
		}

		update_proofratings_settings($settings);

		if ( $location_id === false ) {
			wp_send_json_success();
		}

		$location = get_proofratings()->query;
		$location->save_settings($location_id, $post_data);
		
	
		$connection_approved = get_proofratings_settings('connections_approved');
		$new_connections = array_diff(array_keys($active_connections), $connection_approved);

		$settings = array('location_id' => $location_id, 'connections' => $new_connections);		
		if ( isset($post_data['automated_email_report']) ) {
			$settings['automated_email_report'] = $post_data['automated_email_report'];
		}

		if ( isset($post_data['agency_settings']) ) {
			$settings['agency_settings'] = $post_data['agency_settings'];
		}
		
		$response = wp_remote_get(PROOFRATINGS_API_URL . '/update_client', get_proofratings_api_args($settings));

		$result = json_decode(wp_remote_retrieve_body( $response ));
		if ( isset($result->code) ) {
			wp_send_json_error($result);
		}

		if ( isset($result->success) && $result->success === true ) {
			wp_send_json_success($result);
		}		

		wp_send_json_error(array('message' => 'Unknown error, please contact with support'));
	}


	function sanitize_data($string) {
		if (is_array($string)) {
        	foreach ($string as $k => $v) {
            	$string[$k] = $this->sanitize_data($v); 
			}

			return $string;
		}

		if ( $string === 'true' ) {
			return true;
		}

		if ( $string === 'false' ) {
			return false;
		}
		
		return $string;
	}

	

	
}


return new Proofratings_Ajax();