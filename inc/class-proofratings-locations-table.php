<?php
/**
 * File containing the class Proofratings_Locations_Table.
 *
 * @package proofratings-manager
 * @since   1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registered sites WP List table
 * @since 1.0.1
 */
class Proofratings_Locations_Table extends WP_List_Table  {
	/**
	 * Entry per page
	 * @since  1.0.1
	 */
	var $per_page = 15;

    /**
	 * Total Items
	 * @since  1.0.1
	 */
    var $total_items = 0;

	/**
	 * Constructor.
	 * @since  1.0.1
	 */
	public function __construct() {
        $this->total_items = get_proofratings()->query->total;
		parent::__construct(array('singular' => 'singular_form', 'plural' => 'locations_table', 'ajax' => false));
	}

    /**
	 * extra table nav
     * @since  1.0.6
	 */
    function extra_tablenav( $which ) {        
        return;
        $button_url = admin_url( 'admin.php?page=proofratings-add-location');
        printf('<a style="margin-right: 10px" class="btn-add-new button-primary" href="%s">%s</a>', $button_url, __('Add Location', 'proofratings'));
    }
	
	/**
     * Prepare the items for the table to process
     * @since 1.0.1
     */
    public function prepare_items() {
        global $wpdb;

        $sortable = $this->get_sortable_columns(); 
        $this->_column_headers = array($this->get_columns());

		$this->items = get_proofratings()->query->locations;
        
        $this->set_pagination_args( array(
            'total_items' => $this->total_items,
            'per_page'    => $this->per_page
        ) );
    }

	/**
     * set bulk action for table
     * @since 1.0.1
     */
    public function get_bulk_actions() {
        return;
        $actions = [
            'bulk-delete' => __('Delete', 'proofratings'),
        ];

        return $actions;
    }

	function get_columns() {
		return [
			'cb' => '<input type="checkbox" />',
			'edit' => __('Edit', 'proofratings'),
			'location' => __('Location', 'proofratings'),
			'connected' => __('# of Sites Connected', 'proofratings'),
			'widgets' => __('# of Widgets', 'proofratings'),
			'status' => __('Status', 'proofratings'),
			'action' => '' // __('Action', 'proofratings'),
		];
	}

	/**
     * Define what data to show on each column of the table
     * @param  String $column_name - Current column name
     * @since 1.0.6
     */
    public function column_default( $location, $column_name ) {
		switch ($column_name) {
			case 'location':
			case 'connected':
			case 'widgets':
			case 'status':
				return $location->$column_name;
			
			default:
				return print_r($location->$column_name, true);
		}
    }

	/**
     * checkbox column 
     * @since 1.0.6
     */
    function column_cb( $location ) {
        if ( $location->location_id === 'overall' ) {
            return;
        }
        
        return sprintf('<input type="checkbox" name="locations[]" value="%d" />', $location->location_id);
    }

	/**
     * Edit column 
     * @since 1.0.6
     */
    function column_edit( $location ) {
        if ( $location->location_id === 'overall' ) {
            return;
        }

		$permalink = add_query_arg( 'location', $location->location_id, menu_page_url('proofratings-edit-location', false));
        return sprintf('<a class="dashicons dashicons-edit" href="%s"></a>', $permalink);
    }

	/**
     * Location column 
     * @since 1.0.6
     */
    function column_location( $location ) {
        $page_slug = 'proofratings-rating-badges';
        if ( empty($location->location['name'])) {
            $page_slug = 'proofratings-edit-location';
            $location->location['name'] = 'Edit location';
        }

		$permalink = add_query_arg( 'location', $location->location_id, menu_page_url($page_slug, false));
        return sprintf('<a href="%s">%s</a>', $permalink, $location->location['name']);
    }

	/**
     * Action column 
     * @since 1.0.6
     */
    function column_action( $location ) {
        $actions[] = sprintf('<a class="dashicons dashicons-admin-settings" href="%s"></a>', add_query_arg( 'location', $location->location_id, menu_page_url('proofratings-rating-badges', false)));
        return implode(' ', $actions);

        $input_data = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

        if ( $location->id === 'overall' ) {
            return;
        }

		$permalink = add_query_arg( ['id' => $location->location_id, '_nonce' => wp_create_nonce( 'delete-location' )], menu_page_url('proofratings-rating-badges', false));

		if ( !empty($input_data['s']) ) {
			$permalink = add_query_arg( ['s' => $input_data['s']], $permalink );
		}

		if ( $this->get_pagenum() > 1 ) {
			$permalink = add_query_arg( ['paged' => $this->get_pagenum()], $permalink );
		}
		
		$actions[] = sprintf('<a class="dashicons dashicons-trash" href="%s"></a>', $permalink);
		$actions[] = sprintf('<a class="dashicons dashicons-trash" href="%s"></a>', $permalink);
        return implode(' ', $actions);
    }

    public function single_row( $item ) {
        $class = '';
        if ( $item->id === 'overall' ) {
            $class = 'overall-location';
        }

        printf('<tr class="%s">', $class);
        $this->single_row_columns( $item );
        echo '</tr>';
    }
}