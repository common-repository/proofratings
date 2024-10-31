<?php

/**
 * File containing the class WP_Job_Manager.
 *
 * @package proofratings
 * @since   1.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class Proofratings_Site_Data {
    function __construct($data = []) {
        if (is_a($data, 'Proofratings_Site_Data') || empty($data)) {
            return $data;
        }

        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __isset($key) {
        return isset($this->$key);
    }

    public function __get($key) {
        return isset($this->$key) ? $this->$key : null;
    }
}

/**
 * Sanitize boolean data
 * @since  1.1.7
 */
function sanitize_proofrating_boolean_data($string) {
    if (is_array($string)) {
        foreach ($string as $k => $v) {
            $string[$k] = sanitize_proofrating_boolean_data($v);
        }

        return $string;
    }

    if ($string === 'true') {
        return true;
    }

    if ($string === 'false') {
        return false;
    }

    return $string;
}

/**
 * get settings of proofratings settings
 * @since  1.0.1
 */
function get_proofratings_settings($key = null) {
    $settings = get_option('proofratings_settings');
    if (!is_array($settings)) {
        $settings = [];
    }

    if (!isset($settings['connections_approved']) || !is_array($settings['connections_approved'])) {
        $settings['connections_approved'] = [];
    }

    if (!isset($settings['agency'])) {
        $settings['agency'] = false;
    }

    if (!isset($settings['schema'])) {
        $settings['schema'] = '{
            "@context": "http://schema.org", 
            "@type": "Product", 
            "name": "{company name}", 
            "description": "{add description}", 
            "image": "{add png image}", 
            "itemCondition": "https://schema.org/NewCondition", 
            "brand":{
                "@type": "Brand", 
                "name": "{company name}"
            }, 
            "manufacturer": "{company name}", 
            "aggregateRating":{
                "itemReviewed": "{company name}", 
                "worstRating": "1", 
                "bestRating": "5", 
                "ratingValue": "{aggregate rating}", 
                "reviewCount": "{total review count}"
            }
        }';
    }

    if (!isset($settings['enable_schema'])) {
        $settings['enable_schema'] = false;
    }

    $settings['schema'] = wp_specialchars_decode($settings['schema'], ENT_QUOTES);

    if ($key) {
        return isset($settings[$key]) ? $settings[$key] : false;
    }

    return sanitize_proofrating_boolean_data($settings);
}

/**
 * Update proofratings settings
 * @since  1.1.7
 */
function update_proofratings_settings($args) {
    update_option('proofratings_settings', array_merge(get_proofratings_settings(), (array) $args));
}

/**
 * get current status
 * @since  1.0.1
 */
function is_proofratings_active() {
    return in_array(get_proofratings_settings('status'), ['active', 'trialing']);
}

/**
 * Get api request args
 * @since  1.4.7
 */
function get_proofratings_api_args($args = []) {
    $params = array_merge(array(
        'site_name' => get_bloginfo('name'),
        'site_email' => get_bloginfo('admin_email'),
        'site_url' => get_site_url()
    ), (array) $args);

    $settings = get_proofratings_settings();

    $token = '';
    if (isset($settings['token'])) {
        $token = $settings['token'];
    }

    return array('body' => $params, 'headers' => array('Proofratings-Token' => $token), 'sslverify' => false);
}

function proofratings_review_us() {
    $screen = get_current_screen();
    preg_match('/(toplevel_page_proofratings)$/', $screen->id, $matches);
    if (!$matches) {
        return;
    } ?>
    <p class="proofratings-review-us">Enjoying Proofratings? <img draggable="false" role="img" class="emoji" alt="❤️" src="https://s.w.org/images/core/emoji/13.1.0/svg/2764.svg"> Review us <a href="https://wordpress.org/plugins/proofratings/" target="_blank">here</a></p>
<?php
}
add_action('in_admin_footer', 'proofratings_review_us', 11);

/**
 * Check if demo mode
 * @since  1.1.7
 */
function is_proofratings_demo_mode() {
    if (!is_user_logged_in()) {
        return true;
    }

    if (defined('PROOFRATINGS_DEMO') && PROOFRATINGS_DEMO === true && in_array('subscriber', (array) wp_get_current_user()->roles)) {
        return true;
    }

    return false;
}



add_action('init', function () {
    return;

    $locations = get_proofratings()->Query->locations[0];

    var_dump($locations);
    exit;
});
