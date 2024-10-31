<?php
/**
 * File containing the class Proofratings_Generate_Style.
 *
 * @package proofratings
 * @since   1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate style
 * @since 1.0.5
 */
class Proofratings_Generate_Style {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( "proofrating_location_save_settings", [ $this, 'generate_css' ]);
	}

	/**
	 * Generate styles for sites badge
	 */
	public function sites_badge($location, $slug = 'widget_square', $type = 'square') {
		$sites_badge = new Proofratings_Site_Data($location->settings->$slug);

		printf(".proofratings-widgets-%s .proofratings-widget.proofratings-widget-%s {\n", $location->id, $type);
			if ( $sites_badge->star_color ) {
				printf("\t--themeColor: %s;\n", $sites_badge->star_color);
			}

			if ( $sites_badge->logo_color ) {
				printf("\t--logoColor: %s;\n", $sites_badge->logo_color);
			}

			if ( $sites_badge->icon_color ) {
				printf("\t--iconColor: %s;\n", $sites_badge->icon_color);
			}

			if ( $sites_badge->textcolor ) {
				printf("\t--textColor: %s;\n", $sites_badge->textcolor);
			}

			if ( $sites_badge->review_color_textcolor ) {
				printf("\t--reviewCountTextColor: %s;\n", $sites_badge->review_color_textcolor);
			}

			if ( $sites_badge->background_color ) {
				printf("\tbackground-color: %s;\n", $sites_badge->background_color);
			}

			if ( isset($sites_badge->border['show']) && $sites_badge->border['show']) {
				if ( $sites_badge->border['color'] ) {
					printf("\t--borderColor: %s;\n", $sites_badge->border['color']);
				}

				if ( $sites_badge->border['hover'] ) {
					printf("\t--borderHoverColor: %s;\n", $sites_badge->border['hover']);
				}
			}

			if ( isset($sites_badge->border['show']) && !$sites_badge->border['show'] ) {
				print("\tborder: none!important;\n");
			}

			if ( isset($sites_badge->shadow['shadow']) && $sites_badge->shadow['shadow'] ) {
				if ( !empty($sites_badge->shadow['color'] )) {
					printf("\t--shadowColor: %s;\n", $sites_badge->shadow['color']);
				}
			}
		echo "}\n\n";

		if ( isset($sites_badge->shadow['shadow']) && $sites_badge->shadow['shadow'] ) {
			printf(".proofratings-widgets-%s .proofratings-widget.proofratings-widget-%s:hover {\n", $location->id, $type);
				if ( !empty($sites_badge->shadow['hover'] )) {
					printf("\t--shadowColor: %s;\n", $sites_badge->shadow['hover']);
				}
			echo "}\n\n";
		}

		if ( isset($sites_badge->shadow['shadow']) && !$sites_badge->shadow['shadow'] ) {
			printf(".proofratings-widgets-%1\$s .proofratings-widget.proofratings-widget-%2\$s, .proofratings-widgets-%1\$s .proofratings-widget.proofratings-widget-%2\$s:hover {\n", $location->id, $type);
				echo "\t--shadowColor: transparent;\n";
			echo "}\n\n";
		}
	}

	/**
	 * Generate styles for sites badge
	 */
	public function badge_basic($location) {
		$badge_settings = new Proofratings_Site_Data($location->settings->widget_basic);

		printf(".proofratings-widgets-%s .proofratings-widget.proofratings-widget-basic {\n", $location->id);
			if ( $badge_settings->star_color ) {
				printf("\t--themeColor: %s;\n", $badge_settings->star_color);
			}

			if ( $badge_settings->logo_color ) {
				printf("\t--logoColor: %s;\n", $badge_settings->logo_color);
			}

			if ( $badge_settings->review_count_textcolor ) {
				printf("\t--review_count_textcolor: %s;\n", $badge_settings->review_count_textcolor);
			}

			if ( $badge_settings->view_reviews_text_color ) {
				printf("\t--view_review_textcolor: %s;\n", $badge_settings->view_reviews_text_color);
			}
		echo "}\n\n";
	}

	/**
	 * Generate styles for sites icon
	 * @since 1.0.8
	 */
	public function sites_icon($location) {
		if ( $location->settings->font ) {
			printf(".proofratings-widgets-%s .proofratings-widget.proofratings-widget-icon {\n", $location->id);
				printf("\tfont-family: %s!important;\n", $location->settings->font);
			echo "}\n\n";
		}

		$badge_settings = new Proofratings_Site_Data($location->settings->widget_icon);
		

		printf(".proofratings-widgets-%s .proofratings-widget.proofratings-widget-icon {\n", $location->id);
			if ( $badge_settings->icon_color ) {
				printf("\t--logoColor: %s;\n", $badge_settings->icon_color);
			}

			if ( $badge_settings->star_color ) {
				printf("\t--star_color: %s;\n", $badge_settings->star_color);
			}

			if ( $badge_settings->textcolor ) {
				printf("\t--textcolor: %s;\n", $badge_settings->textcolor);
			}

			if ( $badge_settings->background_color ) {
				printf("\tbackground-color: %s;\n", $badge_settings->background_color);
			}
		echo "}\n\n";
	}

	/**
	 * Generate styles overall ratings
	 */
	public function overall_rectangle($location, $type = 'overall_rectangle_embed') {		
		$overall_badge = new Proofratings_Site_Data($location->settings->$type);

		$type_class = 'badge-embed';
		if ( $type === 'overall_rectangle_float') {
			$type_class = 'badge-float';
		}

		printf(".proofratings-badge-%s.proofratings-badge.proofratings-badge-rectangle.%s {\n", $location->id, $type_class);
			if ( $overall_badge->star_color ) {
				printf("\t--star_color: %s;\n", $overall_badge->star_color);
			}
			
			if ( $overall_badge->rating_color ) {
				printf("\t--rating_color: %s;\n", $overall_badge->rating_color);
			}

			if ( $overall_badge->review_text_color ) {
				printf("\t--review_text_color: %s;\n", $overall_badge->review_text_color);
			}

			if ( $overall_badge->review_background ) {
				printf("\t--review_background: %s;\n", $overall_badge->review_background);
			}

			if ( $overall_badge->background_color ) {
				printf("\t--background_color: %s;\n", $overall_badge->background_color);
			}

			if ( !isset($overall_badge->border) || !$overall_badge->border['show']) {
				printf("\t--border_color: transparent!important;\n");
				printf("\t--border_hover: transparent!important;\n");
			}

			if ( isset($overall_badge->border['show']) && $overall_badge->border['show'] ) {		
				if ( $overall_badge->border['color'] ) {
					printf("\t--border_color: %s;\n", $overall_badge->border['color']);
				}
				
				if ( $overall_badge->border['hover'] ) {
					printf("\t--border_hover: %s;\n", $overall_badge->border['hover']);
				}
			}

			if ( !isset($overall_badge->shadow) || !$overall_badge->shadow['shadow']) {
				printf("\t--shadow_color: transparent!important;\n");
				printf("\t--shadow_hover: transparent!important;\n");
			}

			if ( isset($overall_badge->shadow['shadow']) && $overall_badge->shadow['shadow'] ) {		
				if ( $overall_badge->shadow['color'] ) {
					printf("\t--shadow_color: %s;\n", $overall_badge->shadow['color']);
				}
				
				if ( $overall_badge->shadow['hover'] ) {
					printf("\t--shadow_hover: %s;\n", $overall_badge->shadow['hover']);
				}
			}
		echo "}\n\n";

		$popup_settings = [];
		if ( isset($overall_badge->popup_settings) ) {
			$popup_settings = $overall_badge->popup_settings;
		}

		$popup_settings = new Proofratings_Site_Data($popup_settings);
		$this->popover_style($popup_settings, $location->overall_reviews);
	}


	/**
	 * Generate styles overall ratings narrow
	 */
	public function overall_narrow($location, $type = 'overall_narrow_embed') {		
		if ( $location->settings->font ) {
			printf(".proofratings-badge-%s.proofratings-badge.proofratings-badge-narrow.%s {\n", $location->id, $type_class);
				printf("\tfont-family: %s!important;\n", $location->settings->font);
			echo "}\n\n";
		}

		$overall_badge = new Proofratings_Site_Data($location->settings->$type);

		$type_class = 'badge-embed';
		if ( $type === 'overall_narrow_float') {
			$type_class = 'badge-float';
		}

		printf(".proofratings-badge-%s.proofratings-badge.proofratings-badge-narrow.%s {\n", $location->id, $type_class);
			if ( $overall_badge->star_color ) {
				printf("\t--star_color: %s;\n", $overall_badge->star_color);
			}
			
			if ( $overall_badge->rating_color ) {
				printf("\t--rating_color: %s;\n", $overall_badge->rating_color);
			}

			if ( $overall_badge->review_text_color ) {
				printf("\t--review_text_color: %s;\n", $overall_badge->review_text_color);
			}

			if ( $overall_badge->review_background ) {
				printf("\t--review_background: %s;\n", $overall_badge->review_background);
			}

			if ( $overall_badge->background_color ) {
				printf("\t--background_color: %s;\n", $overall_badge->background_color);
			}

			if ( !isset($overall_badge->border) || !$overall_badge->border['show']) {
				printf("\t--border_color: transparent!important;\n");
				printf("\t--border_hover: transparent!important;\n");
			}

			if ( isset($overall_badge->border['show']) && $overall_badge->border['show'] ) {		
				if ( $overall_badge->border['color'] ) {
					printf("\t--border_color: %s;\n", $overall_badge->border['color']);
				}
				
				if ( $overall_badge->border['hover'] ) {
					printf("\t--border_hover: %s;\n", $overall_badge->border['hover']);
				}
			}

			if ( !isset($overall_badge->shadow) || !$overall_badge->shadow['shadow']) {
				printf("\t--shadow_color: transparent!important;\n");
				printf("\t--shadow_hover: transparent!important;\n");
			}

			if ( isset($overall_badge->shadow['shadow']) && $overall_badge->shadow['shadow'] ) {		
				if ( $overall_badge->shadow['color'] ) {
					printf("\t--shadow_color: %s;\n", $overall_badge->shadow['color']);
				}
				
				if ( $overall_badge->shadow['hover'] ) {
					printf("\t--shadow_hover: %s;\n", $overall_badge->shadow['hover']);
				}
			}
		echo "}\n\n";

		$popup_settings = [];
		if ( isset($overall_badge->popup_settings) ) {
			$popup_settings = $overall_badge->popup_settings;
		}

		$popup_settings = new Proofratings_Site_Data($popup_settings);
		$this->popover_style($popup_settings, $location->overall_reviews);
	}

	/**
	 * Generate styles popover badges
	 */
	public function popover_style($popup_settings, $overall_reviews) {
		printf(".proofratings-badges-popup.proofratings-badges-popup-%s .proofratings-widget {\n", $overall_reviews->id);
			if ( $popup_settings->star_color ) {
				printf("\t--themeColor: %s;\n", $popup_settings->star_color);
			}

			if ( $popup_settings->review_text_color ) {
				printf("\t--reviewCountTextColor: %s;\n", $popup_settings->review_text_color);
			}

			if ( $popup_settings->review_text_background ) {
				printf("\t--review_text_background: %s;\n", $popup_settings->review_text_background);
			}

			if ( $popup_settings->view_review_color ) {
				printf("\t--view_review_color: %s;\n", $popup_settings->view_review_color);
			}

			if ( $popup_settings->rating_color ) {
				printf("\t--rating_color: %s;\n", $popup_settings->rating_color);
			}

		echo "}\n\n";			
	}

	

	/**
	 * Generate styles overall banner badges
	 */
	public function overall_banner_badges($location) {
		$banner_badge = new Proofratings_Site_Data($location->settings->overall_cta_banner);

		if ( $location->settings->font ) {
			printf(".proofratings-banner-badge.proofratings-banner-badge-%s {\n", $location->id);
				printf("\tfont-family: %s!important;\n", $location->settings->font);
			echo "}\n\n";
		}

				
		printf(".proofratings-banner-badge.proofratings-banner-badge-%s {\n", $location->id);

			if ( $banner_badge->star_color ) {
				printf("\t--star_color: %s;\n", $banner_badge->star_color);
			}

			if ( $banner_badge->number_review_text_color ) {
				printf("\t--reviewCountTextcolor: %s;\n", $banner_badge->number_review_text_color);
			}
			
			if ( $banner_badge->background_color ) {
				printf("\t--backgroundColor: %s;\n", $banner_badge->background_color);
			}

			if ( $banner_badge->rating_text_color ) {
				printf("\t--rating_text_color: %s;\n", $banner_badge->rating_text_color);
			}

			if ( $banner_badge->review_rating_background_color ) {
				printf("\t--review_rating_background_color: %s;\n", $banner_badge->review_rating_background_color);
			}
		echo "}\n\n";
		

		if ( isset($banner_badge->button1) ) {
			$button1 = new Proofratings_Site_Data($banner_badge->button1);

			printf(".proofratings-banner-badge.proofratings-banner-badge-%s .proofratings-button.button1 {\n", $location->id);
				if ( $button1->textcolor ) {
					printf("\t--textColor: %s;\n", $button1->textcolor);
				}

				if ( $button1->hover_textcolor ) {
					printf("\t--textHoverColor: %s;\n", $button1->hover_textcolor);
				}

				if ( $button1->background_color ) {
					printf("\t--backgroundColor: %s;\n", $button1->background_color);
				}

				if ( $button1->hover_background_color ) {
					printf("\t--backgroundHoverColor: %s;\n", $button1->hover_background_color);
				}

				if ( $button1->border ) {
					if ( $button1->border_color ) {
						printf("\t--borderColor: %s;\n", $button1->border_color);
					}
					
					if ( $button1->hover_border_color ) {
						printf("\t--borderHoverColor: %s;\n", $button1->hover_border_color);
					}
				}
			echo "}\n\n";
		}

		if ( isset($banner_badge->button2) ) {
			$button2 = new Proofratings_Site_Data($banner_badge->button2);

			printf(".proofratings-banner-badge.proofratings-banner-badge-%s .proofratings-button.button2 {\n", $location->id);
				if ( $button2->textcolor ) {
					printf("\t--textColor: %s;\n", $button2->textcolor);
				}

				if ( $button2->hover_textcolor ) {
					printf("\t--textHoverColor: %s;\n", $button2->hover_textcolor);
				}

				if ( $button2->background_color ) {
					printf("\t--backgroundColor: %s;\n", $button2->background_color);
				}

				if ( $button2->hover_background_color ) {
					printf("\t--backgroundHoverColor: %s;\n", $button2->hover_background_color);
				}

				if ( $button2->border ) {
					if ( $button2->border_color ) {
						printf("\t--borderColor: %s;\n", $button2->border_color);
					}
					
					if ( $button2->hover_border_color ) {
						printf("\t--borderHoverColor: %s;\n", $button2->hover_border_color);
					}
				}
			echo "}\n\n";
		}
	}

	/**
	 * Generate styles 
	 */
	public function generate_css() {
		//Get location again - Must use
		$locations = get_proofratings()->query->prepare_locations();

		ob_start();

		foreach ($locations as $location) {
			$this->sites_badge($location);
			$this->badge_basic($location);
			$this->sites_icon($location);
			$this->sites_badge($location, 'widget_rectangle', 'rectangle');

			$this->overall_rectangle($location);
			$this->overall_rectangle($location, 'overall_rectangle_float');

			$this->overall_narrow($location);
			$this->overall_narrow($location, 'overall_narrow_float');
			
			$this->overall_banner_badges($location);
		}

		$styles = ob_get_clean();
		file_put_contents(wp_upload_dir()['basedir'] . '/proofratings-generated.css', $styles);	
	}
}

return new Proofratings_Generate_Style();