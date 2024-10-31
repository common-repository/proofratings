<?php
/**
 * File containing the class Proofratings_Locations.
 *
 * @package proofratings
 * @since   1.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get locations
 * @since 1.0.6
 */
class Proofratings_Query  {
	/**
	 * Is global
	 * @since  1.0.6
	 */
	var $global = true;

	/**
	 * The single instance of the class.
	 * @var self
	 * @since  1.0.6
	 */
	private static $instance = null;

	/**
	 * Main Proofratings_Locations
	 * @since  1.0.6
	 * @return self Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * locations
	 * @var array
	 * @since  1.1.0
	 */
	var $locations = [];

	/**
	 * Constructor.
	 * @since  1.0.6
	 */
	public function __construct() {
        $this->prepare_locations();
		$this->total = sizeof($this->locations);
	}

	/**
	 * Update location
	 * @since  1.1.7
	 */
	function update($location_id, $params) {
		global $wpdb;
		return $wpdb->update($wpdb->proofratings, $params, ['location_id' => $location_id]);
	}

	/**
	 * Update a column
	 * @since  1.1.7
	 */
	function update_column($location_id, $column, $value) {
		global $wpdb;
		return $wpdb->update($wpdb->proofratings, [$column => maybe_serialize( $value )], ['location_id' => $location_id]);
	}

	/**
	 * save location
	 * @param string location_id
	 * @since  1.0.6
	 */
	function save_settings($location_id, $data) {
		if ( !is_array($data) ) {
			$data = [];
		}

		if ( $location_id === 'overall' ) {
			$settings = get_option('proofratings_overall_rating_settings');
			if ( !is_array($settings) ) {
				$settings = [];
			}

			$settings =  array_merge($settings, $data);
			if ( isset($settings['active_connections']) ) {
				unset($settings['active_connections']);
			}

			$result = update_option('proofratings_overall_rating_settings', $settings);
			do_action( 'proofrating_location_save_settings' );
			return $result;
		}

		global $wpdb;

		$location = $this->get($location_id);
		if ( !$location ) {
			return false;
		}

		if ( !isset($data['widget_square'])) {
			$data['widget_square'] = [];
		}

		if ( !isset($data['widget_basic'])) {
			$data['widget_basic'] = [];
		}

		if ( !isset($data['widget_icon'])) {
			$data['widget_icon'] = [];
		}

		if ( !isset($data['widget_rectangle'])) {
			$data['widget_rectangle'] = [];
		}
		
		$settings = array_merge((array) maybe_unserialize($location->settings), $data);
		$result = $wpdb->update($wpdb->proofratings, ['settings' => maybe_serialize( $settings )], ['location_id' => $location_id]);
		do_action( 'proofrating_location_save_settings' );
		return $result;
	}

	/**
	 * save meta data
	 * @param string location_id column of table
	 * @param $meta_data meta_data column merge previous to new one
	 * @since  1.1.7
	 */
	public function save_meta_data($location_id, $key, $updated_data) {
		$location = $this->get($location_id);
		if ( $location === false) {
			return false;
		}

		$meta_data = $location->meta_data;
		if ( !is_array($meta_data) ) {
			$meta_data = [];
		}

		$meta_data[$key] = $updated_data;

		global $wpdb;
		return $wpdb->update($wpdb->proofratings, ['meta_data' => maybe_serialize( $meta_data )], ['location_id' => $location_id]);
	}

	/**
	 * Get location
	 * @since  1.1.7
	 */
	function get_locations() {
		return array_map(function($location){
			return array('id' => $location->id, 'location_id' => $location->location_id, 'name' => $location->location['name']);
		}, $this->locations);		
	}
	/**
	 * Sanitize location
	 * @since  1.0.6
	 */
	function sanitize_location($location) {
		$reviews = maybe_unserialize( $location->reviews );
		if ( !is_array($reviews) ) {
			$reviews = [];
		}

		$location->reviews = $reviews;

		$settings = maybe_unserialize( $location->settings );
		if ( !is_array($settings) ) {
			$settings = [];
		}

		$settings = $location->settings = new Proofratings_Site_Data(sanitize_proofrating_boolean_data($settings));		

		$active_connections = [];
		if ( isset($location->settings->active_connections) && is_array($location->settings->active_connections)) {
			$active_connections = $location->settings->active_connections;
		}		

		foreach ($active_connections as $key => $review_site) {
			if ( !isset($review_site['selected']) || $review_site['selected'] !== true ) {
				unset($active_connections[$key]);
			}				
		}

		$location->active_connections = $active_connections;

		$location->connected = sizeof($location->active_connections);

		$location->widgets = 0;
		if ( is_array($settings->badge_display) ) {
			$location->widgets = sizeof(array_filter($settings->badge_display));
		}

		$location->meta_data = isset($location->meta_data) ? maybe_unserialize( $location->meta_data ) : [];
		if ( !is_array($location->meta_data)) {
			$location->meta_data = [];
		}

		$location_info = [];
		if ( isset($location->location) ) {
			$location_info = maybe_unserialize($location->location);
		}

		if ( !is_array($location_info) ) {
			$location_info = [];
		}		

		$location->location = wp_parse_args( $location_info, array(
			'name' => '',
			'street' => '',
			'city' => '',
			'state' => '',
			'zip' => '',
			'country' => '',
		));

		return $location;
	}

	/**
	 * get overall location
	 * @since  1.0.6
	 */
	function overall_location($locations) {
		$status_text = __('~', 'proofratings');
		if ( sizeof(array_unique(wp_list_pluck( $locations, 'status'))) == 1 ) {
			$status_text = $locations[0]->status;
		}

		$active_connections = [];
		foreach ($locations as $key => $location) {
			$active_connections = $active_connections + $location->active_connections;
		}

		$sites_reviews = [];
		foreach ($locations as $location) {
			if (empty($location->reviews)) {
				continue;
			}

			foreach ($location->reviews as $key => $rating) {
				$sites_reviews[$key][] = $rating;
			}			
		}

		$site_overall_review = [];		
		foreach ($sites_reviews as $key => $reviews) {			
			$count = sizeof($reviews);
			if ($count == 0) {
				$count = 1;
			}
			
			$rating = floatval(array_sum(wp_list_pluck( $reviews, 'rating')) / $count);
			$site_overall_review[$key] = array(
				'rating' => $rating,
				'percent' => $rating * 20,
				'reviews' => array_sum(wp_list_pluck( $reviews, 'reviews')),
			);
		}

		$overall_location = (object) $this->sanitize_location((object) array(
			'id' => 'overall',
			'location_id' => 'overall',
			'settings' => get_option('proofratings_overall_rating_settings'),
			'reviews' => $site_overall_review,
			'status' => $status_text
		));

		$overall_location->location['name'] = __('Overall Location', 'proofratings');

		$overall_location->active_connections = $active_connections;

		return $overall_location;
	}
	
	/**
     * Prepare locations
     * @since 1.0.6
     */
	function prepare_locations() {
		global $wpdb;

		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->proofratings ) );
		if ( ! $wpdb->get_var( $query ) == $wpdb->proofratings ) {
			return [];
		}

		//$locations = $wpdb->get_results("SELECT * FROM $wpdb->proofratings WHERE status IN ('active', 'pause', 'pending', 'due', 'deleted') ORDER BY location ASC");
		$locations = $wpdb->get_results("SELECT * FROM $wpdb->proofratings WHERE status IN ('active', 'pause', 'pending', 'due') ORDER BY location ASC");


		usort($locations, function($location){
			if ( $location->status === 'active' ) {
				return -1;
			}

			return 2;
		});
		
		array_walk($locations, function(&$location){
			$location = $this->sanitize_location($location);
		});

		if ( count($locations) > 1 ) {
			$this->global = false;
			array_unshift($locations, $this->overall_location($locations));
		}

		$review_sites = get_proofratings_review_sites();

		$connections_approved = get_proofratings_settings('connections_approved');

		foreach ($locations as $key => $location) {	
			$location->reviews_connections = [];
			foreach ($location->active_connections as $key => $connection_info) {
				if ( !isset($review_sites[$key]) || !in_array($key, $connections_approved)) {
					continue;
				}

				$site_ratings = array('rating' => 0, 'reviews' => 0, 'url' => '');
				if ( isset($location->reviews[$key] ) ) {
					$site_ratings = $location->reviews[$key];
				}
				
				$location->reviews_connections[$key] = array_merge($review_sites[$key], $connection_info, $site_ratings);
			}

			$location->overall_reviews = new Proofratings_Ratings($location->reviews_connections);
			$location->overall_reviews->id = $location->id;
			$location->overall_reviews->location_id = $location->location_id;

			$location->settings->active_connections = $location->active_connections;
		}

		return $this->locations = $locations;
	}

	/**
	 * get global location id
	 * @since  1.0.6
	 */
	function get_global_id() {
		if ( sizeof($this->locations) == 0 ) {
			return false;
		}
		
		return $this->locations[0]->location_id;		
	}

	/**
	 * get single location by location_id column
	 * @since  1.0.6
	 */
	function get($location_id) {
		$key = array_search($location_id, array_column($this->locations, 'location_id'));
		return $key === false ? false : $this->locations[$key];
	}
}