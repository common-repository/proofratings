<?php
/**
 * File containing the class Proofratings_Shortcodes.
 *
 * @package proofratings
 * @since   1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode class
 *
 * @since 1.0.0
 */
class Proofratings_Shortcodes {

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
	 * Constructor.
	 */
	public function __construct() {	
        add_shortcode('proofratings_widgets', [$this, 'proofratings_widgets']);
		add_shortcode('proofratings_badges_popup', [$this, 'proofratings_badges_popup']);

        add_shortcode('proofratings_overall_rectangle', [$this, 'overall_rectangle']);
        add_shortcode('proofratings_overall_narrow', [$this, 'overall_narrow']);

        add_shortcode('proofratings_overall_ratings_cta_banner', [$this, 'overall_ratings_cta_banner']);
	}

	public function overall_rectangle($atts, $content = null) {
		$atts = shortcode_atts(['id' => 'overall', 'float' => 'no', 'type' => 'rectangle'], $atts);
		return $this->proofratings_overall_ratings($atts, $content = null);
	}

	public function overall_narrow($atts, $content = null) {
		$atts = shortcode_atts(['id' => 'overall', 'float' => 'no', 'type' => 'narrow'], $atts);
		return $this->proofratings_overall_ratings($atts, $content = null);
	}

	/**
	 * floating badge shortcode
	 */
	public function proofratings_overall_ratings($atts, $content = null) {
        $atts = shortcode_atts([
			'id' => 'overall',
			'float' => 'no',
			'type' => 'rectangle',
        ], $atts);

		$location_query = get_proofratings()->query;
		if ( $location_query->global ) {
			$atts['id'] = $location_query->get_global_id();
		}

		$type = sanitize_title( $atts['type']);
		if ( !in_array($type, array('rectangle', 'narrow')) ) {
			$type = 'rectangle';
		}

		$overall_slug = "overall_{$type}_embed";
		if ( $atts['float'] === 'yes' ) {
			$overall_slug = "overall_{$type}_float";
		}

		$location = get_proofratings()->query->get($atts['id']);
		if ( !$location ) {
			return;
		}
		
		if ( $atts['float'] !== 'yes' && isset($location->settings->badge_display[$overall_slug]) && !$location->settings->badge_display[$overall_slug]) {
			return;
		}

		$attributes = array();
		$tag = 'div';
		
		$badge_settings = new Proofratings_Site_Data($location->settings->$overall_slug);
		
		
		
		$classes = ['proofratings-badge', 'proofratings-badge-'.$type];
		$classes[] = 'proofratings-badge-' . $location->id;

		if ( $atts['float'] !== 'yes' ) {
			$classes[] = 'badge-embed';

			$link = $badge_settings->link;

			if ( isset($link['enable']) && $link['enable'] === true ) {
				if ( !empty($link['url']) ) {
					$tag = 'a';
					$attributes['href'] = esc_attr( $link['url'] );
					if ( $link['_blank'] === true  ) {
						$attributes['target'] = '_blank';
					}
				}
			}
		}

		if ( $atts['float'] == 'yes' ) {
			array_push($classes, 'badge-float');

			if ( !empty($badge_settings->position) ) {
				$classes[] = $badge_settings->position;
			}

			if ( $badge_settings->mobile === false) {
				$classes[] = 'badge-hidden-mobile';
			}

			if ( $badge_settings->tablet === false) {
				$classes[] = 'badge-hidden-tablet';
			}
		}

		if ( sizeof($location->reviews) > 5 ) {
			$classes[] = 'connected-more';
		}

		$attributes['class'] = implode(' ', $classes);
		$attributes['data-location'] = $location->location_id;
		$attributes['data-type'] = $overall_slug;

		$attribute_html = '';

		foreach ($attributes as $ak => $attribute_value) {
			$attribute_html .= sprintf(' %s="%s"', $ak, $attribute_value);
		}

		

        ob_start();
        printf('<%s %s>', $tag, $attribute_html);
			if ( $badge_settings->close_button && $atts['float'] == 'yes' ) {
				echo  '<i class="proofratings-close">&times;</i>';
			}

			if($type == 'narrow') {
				$this->overall_ratings_narrow($location);
			} else {				
				$this->overall_ratings_rectangle($location);
			}
			
        printf('</%s>', $tag);
        $overall_ratings = ob_get_clean();

		if ( $atts['float'] !== 'yes' ) {
			return $overall_ratings;
		}

		$popup_settings = [];
		if ( isset($badge_settings->popup_settings) ) {
			$popup_settings = $badge_settings->popup_settings;
		}

		$popup_settings = new Proofratings_Site_Data($popup_settings);

		$popover = $this->get_popup_widgets($popup_settings, $location->overall_reviews);
		if ( empty($popover) ) {
			return $overall_ratings;
		}

		return sprintf('<div>%s %s</div>', $overall_ratings, $popover);
	}

	private function get_popup_widgets($popup_settings, $overall_reviews) {
		$review_sites = $overall_reviews->review_sites;

		$column = 4;
		if ( count($review_sites) == 5 ) {
			$column = 5;
		}

		if ( count($review_sites) < 4 ) {
			$column = count($review_sites);
		}

        ob_start(); 
		
        printf('<div class="proofratings-badges-popup proofratings-badges-popup-%s" data-location="%s">', $overall_reviews->id, $overall_reviews->location_id);
			printf ('<div class="proofratings-popup-widgets-box" data-column="%d">', $column);
	        foreach ($review_sites as $key => $site) {
				$tag = 'div';
				$attribue= '';
				
				if( !empty($site->url) ) {
					$tag = 'a';
					$attribue = sprintf('href="%s" target="_blank"', esc_url_raw($site->url));
				}

				printf('<%s %s class="proofratings-widget proofratings-widget-%s" data-location="%s">', $tag, $attribue, $key, $overall_reviews->location_id);
	            	printf('<div class="review-site-logo"><img src="%1$s" alt="%2$s" ></div>', esc_attr($site->logo), esc_attr($site->name));
				
					echo '<div class="proofratings-reviews">';
						printf('<span class="proofratings-score">%s</span>', number_format($site->rating, 1));
						printf('<span class="proofratings-stars"><i style="width: %s%%"></i></span>', esc_attr($site->percent));
			        echo '</div>';

					printf('<div class="review-count"> %d %s </div>', esc_html($site->reviews), __('reviews', 'proofratings'));

					echo '<p class="view-reviews">' . __('View Reviews', 'proofratings') . '</p>';

				printf('</%s>', $tag);
	        }
			echo '</div>';

			echo '<span class="proofrating-close">-<span>';
        echo '</div>';
        return ob_get_clean();
	}

	private function overall_ratings_rectangle($location) {		
		echo '<div class="proofratings-inner">';
			
			$location->overall_reviews->get_logos();

			echo '<div class="proofratings-reviews">';
				printf('<span class="proofratings-score">%s</span>', esc_html( $location->overall_reviews->rating));
				printf( '<span class="proofratings-stars"><i style="width: %s%%"></i></span>', esc_html($location->overall_reviews->percent));
			echo '</div>';
		echo '</div>';

		printf('<div class="proofratings-review-count">%d %s</div>', esc_html($location->overall_reviews->reviews), __('reviews', 'proofratings'));
	}

	private function overall_ratings_narrow($location) {
		$location->overall_reviews->get_logos();

        echo '<div class="proofratings-reviews">';
            printf('<span class="proofratings-score">%s</span>', esc_html($location->overall_reviews->rating));
            printf( '<span class="proofratings-stars"><i style="width: %s%%"></i></span>', esc_html($location->overall_reviews->percent));
        echo '</div>';

    	printf('<div class="proofratings-review-count">%d %s</div>', esc_html($location->overall_reviews->reviews), __('reviews', 'proofratings'));
	}

	/**
	 * embed badge shortcode
	 */
	public function proofratings_widgets($atts, $content = null) {		
		$atts = shortcode_atts([
			'style' => 'square',
            'id' => 'overall',
			'column' => false
        ], $atts);

		$location_query = get_proofratings()->query;
		if ( $location_query->global ) {
			$atts['id'] = $location_query->get_global_id();
		}

		$location = get_proofratings()->query->get($atts['id']);		
		if ( !$location ) {
			return;
		}

		$settings = $location->settings;

		$badge_style = sanitize_key( $atts['style']);
		$widget_type = sprintf('widget_%s', $badge_style);

		if ( !method_exists($this, $widget_type)) {
			$badge_style = 'square';
			$widget_type = 'widget_square';
		}

		$current_widget = isset($settings->$widget_type) ? $settings->$widget_type : [];
		$current_widget = new Proofratings_Site_Data($current_widget);		
		
		if ( isset($settings->badge_display[$widget_type]) && !$settings->badge_display[$widget_type]) {
			return;
		}

		if( !is_array($current_widget->widget_connections)) {
			return;
		}

		$widget_reviews = new Proofratings_Ratings( $location->reviews_connections, $current_widget->widget_connections );
		
		if ( !$widget_reviews->has_ratings ) {
			if ( is_admin(  ) ) {
				return sprintf('<div class="proofratings-noconnections">No connection available</div>');
			}
			
			return;
		}

		$badge_class = ['proofratings-widget', 'proofratings-widget-' . $location->id, 'proofratings-widget-' . $badge_style];
		
		if ( $current_widget->logo_color || $current_widget->icon_color ) {
			$badge_class[] = 'proofratings-widget-logo-color';
		}

		if ( $current_widget->alignment ) {
			$badge_class[] = sprintf('proofratings-widgets-align-%s', esc_attr($current_widget->alignment) );
		}

		$wrapper_classes[] = 'proofratings-review-widgets-grid';
		$wrapper_classes[] = sprintf('proofratings-widgets-%s', $location->id);
		$wrapper_classes[] = sprintf('proofratings-widgets-grid-%s', $badge_style);

		if ( $current_widget->position ) {
			$wrapper_classes[] = sprintf('proofratings-widgets-grid-%s', esc_attr($current_widget->position) );
		}

		if ( absint($atts['column']) > 0 ) {
			$wrapper_classes[] = sprintf('proofratings-widgets-grid-column-%s', absint($atts['column']));
		}

	
		$review_sites = $widget_reviews->review_sites;

        ob_start();

        printf('<div class="%s">', implode(' ', $wrapper_classes));
			foreach ($review_sites as $site_id => $rating) {
				$tag = 'div';
				$attribue = '';
			
				if( !empty($rating->url) && $widget_type !== 'widget_basic') {
					$tag = 'a';
					$attribue = sprintf('href="%s" target="_blank"', esc_url($rating->url));
				}
				
				printf('<%s class="%s %s" %s data-location="%s">', $tag, implode(' ', $badge_class), 'proofratings-widget-' . $site_id, $attribue, $location->location_id);
					$this->{$widget_type}($rating);
				printf('</%s>', $tag);
	        }

        echo '</div>';
        return ob_get_clean();
	}

	/**
	 * Embed badge sites square
	 */
	public function widget_square($site) {
    	printf('<div class="review-site-logo"><img src="%s" alt="%s" ></div>', esc_url_raw($site->logo), esc_attr($site->name));
	
		echo '<div class="proofratings-reviews"">';
			printf('<span class="proofratings-score">%s</span>', number_format($site->rating, 1));
			printf('<span class="proofratings-stars"><i style="width: %s%%"></i></span>', esc_attr($site->percent));
        echo '</div>';

		printf('<div class="review-count"> %d %s </div>', esc_html($site->reviews), __('reviews', 'proofratings'));

		echo '<p class="view-reviews">' . __('View Reviews', 'proofratings') . '</p>';
	}

	/**
	 * Embed badge basic
	 */
	public function widget_basic($site) {
    	printf('<div class="review-site-logo"><img src="%s" alt="%s" ></div>', esc_attr($site->logo), esc_attr($site->name));
	
		printf('<span class="proofratings-stars"><i style="width: %s%%"></i></span>', esc_attr($site->percent));

		printf('<div class="review-count"> %d %s </div>', esc_html($site->reviews), __('ratings', 'proofratings'));

		if ( $review_url = esc_url($site->url) ) {
			printf('<a class="view-reviews" href="%s" target="_blank">%s</a>', esc_attr($review_url), __('View all reviews', 'proofratings'));
		}            
	}

	/**
	 * Embed badge style2
	 */
	public function widget_rectangle($site) {
    	printf('<div class="review-site-logo"><img src="%s" alt="%s" ></div>', esc_attr($site->logo), esc_attr($site->name));
		printf('<span class="proofratings-score">%s</span>', number_format($site->rating, 1));
	
		echo '<div class="proofratings-reviews">';
			printf('<span class="proofratings-stars"><i style="width: %s%%"></i></span>', esc_attr($site->percent));
        echo '</div>';

		printf('<div class="review-count">Based on <b>%d %s</b></div>', esc_html($site->reviews), __('reviews', 'proofratings'));
	}

	/**
	 * Sites icon
	 * @since 1.0.9
	 */
	public function widget_icon($site) {
    	printf('<div class="review-site-logo" style="-webkit-mask-image: url(%1$s);"><img src="%1$s" alt="%2$s" ></div>', esc_attr($site->icon3), esc_attr($site->name));

		echo '<div class="review-info-container">';	
			printf('<span class="proofratings-stars"><i style="width: %s%%"></i></span>', esc_attr($site->percent));

			echo '<div class="review-info">';
				printf('<span class="proofratings-rating">%s %s</span>', number_format($site->rating, 1), __('Rating', 'proofratings'));
				echo '<span class="separator-circle">●</span>';
				printf('<span class="proofratings-review-number">' . _n( '%s Review', '%s Reviews', $site->reviews, 'proofratings' ), number_format_i18n( $site->reviews ) . '</span>');
			echo '</div>';          
		echo '</div>';          
	}

	/**
	 * CTA banner 
	 */
	public function overall_ratings_cta_banner($atts) {
		$location = get_proofratings()->query->get($atts['id']);
		if ( !$location || !$location->overall_reviews->has_ratings ) {
			return;
		}

		$badge_settings = new Proofratings_Site_Data($location->settings->overall_cta_banner);
		
		
		$classes = ['proofratings-banner-badge'];
		$classes[] = 'proofratings-banner-badge-'.$location->id;


		if ( $badge_settings->tablet === false) {
			$classes[] = 'badge-hidden-tablet';
		}

		if ( $badge_settings->mobile === false) {
			$classes[] = 'badge-hidden-mobile';
		}

		if ( $badge_settings->shadow !== false ) {
			$classes[] = 'has-shadow';
		}

		$class = implode(' ', $classes);

		$button1 = '';
		if ( isset($badge_settings->button1['show']) && $badge_settings->button1['show'] === true ) {
			$button1_settings = new Proofratings_Site_Data($badge_settings->button1);

			$button1_class = 'proofratings-button button1';
			if ( $button1_settings->border == 'yes' ) {
				$button1_class .= ' has-border';
			}

			if ( $button1_settings->rectangle !== true) {
				$button1_class .= ' button-round';
			}

			$target = '';
			if ( $button1_settings->blank == 'yes') {
				$target = 'target="_blank"';
			}

			$button1 .= sprintf('<a href="%s" class="%s" %s>', esc_url( $button1_settings->url), trim($button1_class), $target);
			$button1 .= $button1_settings->text;
			$button1 .= '</a>';			
		}

		$button2 = '';
		if ( isset($badge_settings->button2['show']) && $badge_settings->button2['show'] === true ) {
			$button2_settings = new Proofratings_Site_Data($badge_settings->button2);
			
			$button2_class = 'proofratings-button button2';
			if ( $button2_settings->border ) {
				$button2_class .= ' has-border';
			}
			
			if ( $button2_settings->rectangle === false) {
				$button2_class .= ' button-round';
			}

			$target = '';
			if ( $button2_settings->blank == 'yes') {
				$target = 'target="_blank"';
			}

			$button2 .= sprintf('<a href="%s" class="%s" %s>', esc_url( $button2_settings->url), trim($button2_class), $target);			
			$button2 .= $button2_settings->text;
			$button2 .= '</a>';			
		}

		$close_button = '';
		
		if ( $badge_settings->close_button_desktop !== false || $badge_settings->close_button_mobile !== false ) {
			$close_button_class = ['proofratings-banner-close'];
			if ( $badge_settings->close_button_desktop ) {
				$close_button_class[] = 'close-button-desktop';
			}

			if ( $badge_settings->close_button_mobile ) {
				$close_button_class[] = 'close-button-mobile';
			}

			$close_button = sprintf('<a class="%s" href="#">%s</a>', implode(' ', $close_button_class), __('Close', 'proofratings'));
		}
		
		ob_start(); ?>
		<div class="<?php echo esc_attr($class); ?>" data-location="<?php echo esc_attr($location->location_id) ?>" data-type="overall_cta_banner">
			<?php echo wp_kses_post($close_button); ?>
			<?php $location->overall_reviews->get_logos(); ?>

			<div class="rating-box">
				<span class="proofratings-stars medium"><i style="width: <?php echo esc_attr( $location->overall_reviews->percent) ?>%"></i></span> 
				<span class="rating"><?php echo esc_html($location->overall_reviews->rating); ?> / 5</span>
			</div>

			<div class="proofratings-review-count"><?php echo esc_html($location->overall_reviews->reviews); ?> <span>customer</span> reviews</div>

			<div class="button-container"><?php echo wp_kses_post($button1 . $button2); ?></div>
		</div>
		<?php

		return ob_get_clean();
	}
}
