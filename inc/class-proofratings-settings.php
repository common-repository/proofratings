<?php

/**
 * File containing the class Proofratings_Settings.
 *
 * @package proofratings
 * @since   1.0.1
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Handles core plugin hooks and action setup.
 *
 * @since 1.0.0
 */
class Proofratings_Settings {
	/**
	 * The single instance of the class.
	 * @var self
	 * @since  1.0.1
	 */
	private static $instance = null;

	/**
	 * Allows for accessing single instance of class. Class should only be constructed once per call.
	 * @since  1.0.1
	 * @static
	 * @return self Main instance.
	 */
	public static function instance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hold all errors
	 * @var WP_Error
	 * @since  1.0.1
	 */
	var $error;

	/**
	 * Hold form data
	 * @since  1.1.7
	 */
	var $form_data;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->error = new WP_Error();
		$this->form_data = new Proofratings_Site_Data();

		add_action('init', [$this, 'handle_signup_form']);
		add_action('init', [$this, 'handle_support_form']);
		add_action('init', [$this, 'handle_edit_location']);
		add_action('init', [$this, 'handle_change_contact_email']);
		add_action('init', [$this, 'handle_cancel_subscription']);

		add_action('wp_ajax_proofratings_update_payment_method', [$this, 'update_payment_method']);
	}

	public function handle_signup_form() {
		if (!isset($_POST['_nonce'])) {
			return;
		}

		if (!wp_verify_nonce($_POST['_nonce'], 'proofratings_license_confirm_nonce')) {
			return;
		}

		$postdata = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

		$license_key = @$postdata['license-key'];
		if (empty($license_key)) {
			return $this->error->add('license_key', 'Please enter your license key');
		}

		$response = wp_remote_get(PROOFRATINGS_API_URL . '/activate_site', get_proofratings_api_args(['license_key' => $license_key]));
		if (is_wp_error($response)) {
			return $this->error->add('remote_request', $response->get_error_message());
		}

		$result = json_decode(wp_remote_retrieve_body($response));
		if (!is_object($result)) {
			return $this->error->add('error', 'Unknown error');
		}

		if (!isset($result->success) || $result->success !== true) {
			return $this->error->add('license_key', $result->message);
		}

		global $wpdb;

		if (isset($result->data->locations) && is_object($result->data->locations)) {
			foreach ($result->data->locations as $location_slug => $location) {
				$location_data = array('location_id' => $location_slug, 'location' => @$location->name, 'status' => @$location->status);

				$sql = $wpdb->prepare("SELECT * FROM $wpdb->proofratings WHERE location_id = '%s'", $location_slug);
				if ($get_location = $wpdb->get_row($sql)) {
					$wpdb->update($wpdb->proofratings, $location_data, ['id' => $get_location->id]);
					continue;
				}

				$wpdb->insert($wpdb->proofratings, $location_data);
			}
		}

		update_proofratings_settings(['status' => $result->data->status]);
		exit(wp_safe_redirect(admin_url('admin.php?page=proofratings')));
	}

	public function handle_support_form() {
		if (!isset($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], '_nonce_submit_ticket')) {
			return;
		}

		if (is_proofratings_demo_mode()) {
			$this->error->add('error_demo', __('On the demo, you are not able to send a message.'));
		}

		$postdata = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

		if (empty($postdata['subject'])) {
			$this->error->add('subject_missing', __('Please fill your subject.'));
		}

		if (empty($postdata['message'])) {
			$this->error->add('message_missing', __('Please fill your message.'));
		}

		$this->form_data->subject = sanitize_text_field($postdata['subject']);
		$this->form_data->message = sanitize_textarea_field($postdata['message']);

		if ($this->error->has_errors()) {
			return;
		}

		$request = wp_safe_remote_post(PROOFRATINGS_API_URL . '/submit_ticket', get_proofratings_api_args(array(
			'subject' => $this->form_data->subject,
			'message' => $this->form_data->message,
		)));

		if (is_wp_error($request)) {
			return $this->error = $request;
		}

		$response = json_decode(wp_remote_retrieve_body($request));
		if (isset($response->code)) {
			return $this->error->add($response->code, $response->message);
		}

		$this->form_data = new Proofratings_Site_Data(['success' => 'You have successfully placed your ticket.']);
	}

	public function get_location_data() {
		$location_id = false;
		if (isset($_GET['location'])) {
			$location_id = $_GET['location'];
		}

		$location = get_proofratings()->query->get($location_id);
		if ($location === false || $location_id === 0) {
			return new Proofratings_Site_Data(['error' => 'Not a valid location']);
		}

		$location_data = $location->location;
		$postdata = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
		if (isset($postdata['_nonce']) && wp_verify_nonce($postdata['_nonce'], '_nonce_edit_location')) {
			$location_data = $postdata;
		}

		$location_data['location_id'] = $location->location_id;
		return new Proofratings_Site_Data($location_data);
	}

	/**
	 * handle edit location form submit
	 * @since 1.0.6
	 */
	public function handle_edit_location() {
		if (!isset($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], '_nonce_edit_location')) {
			return;
		}

		$form_data = $this->get_location_data();
		if (!empty($form_data->error)) {
			wp_die($form_data->error);
		}

		if (is_proofratings_demo_mode()) {
			$this->error->add('error_demo', __('On the demo, you are not able to edit the location.'));
		}

		if (empty($form_data->name)) {
			$this->error->add('name', __('Please fill location name field', 'proofratings'));
		}

		if (empty($form_data->street)) {
			$this->error->add('street', __('Please fill location street field', 'proofratings'));
		}

		if (empty($form_data->city)) {
			$this->error->add('city', __('Please fill location city field', 'proofratings'));
		}

		if (empty($form_data->state)) {
			$this->error->add('state', __('Please fill location state/province field', 'proofratings'));
		}

		if (empty($form_data->zip)) {
			$this->error->add('zip', __('Please fill location zip/postal field', 'proofratings'));
		}

		if (empty($form_data->country)) {
			$this->error->add('country', __('Please fill location country field', 'proofratings'));
		}

		if ($this->error->has_errors()) {
			return;
		}

		$location = get_object_vars($form_data);
		foreach (['_nonce', '_wp_http_referer', 'submit'] as $clear_key) {
			unset($location[$clear_key]);
		}

		get_proofratings()->query->update_column($_GET['location'], 'location', $location);

		$response = wp_remote_get(PROOFRATINGS_API_URL . '/update_location', get_proofratings_api_args($location));

		$result = json_decode(wp_remote_retrieve_body($response));
		if (isset($result->code) && $result->code === 'rest_no_route') {
			return $this->error->add('error', "We can't communicate with proofratings website. Please contact with them.");
		}

		if (isset($result->message)) {
			return $this->error->add($result->code, $result->message);
		}

		if (isset($result->success)) {
			$_POST['success'] = $result->data->message;
		}
	}

	/**
	 * Cancel subscription handler
	 * @since 1.0.6
	 */
	public function handle_change_contact_email() {
		$postdata = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

		if (!isset($postdata['_nonce']) || !wp_verify_nonce($postdata['_nonce'], '_nonce_update_contact_email')) {
			return;
		}

		if (is_proofratings_demo_mode()) {
			return $this->error->add('error_demo', __('On the demo, you are not able to update your email'));
		}

		$contact_email = trim($postdata['contact_email']);
		if (filter_var($contact_email, FILTER_VALIDATE_EMAIL) === false) {
			$this->error->add('not_valid', __('Please enter valid email address', 'proofratings'));
		}

		if ($this->error->has_errors()) {
			return;
		}

		$response = wp_remote_post(PROOFRATINGS_API_URL . '/update-client-data', get_proofratings_api_args(['contact_email' => $contact_email]));
		$result = json_decode(wp_remote_retrieve_body($response));

		if (isset($result->success) && $result->success === true) {
			update_proofratings_settings(array('email' => $contact_email));
			exit(wp_safe_redirect(admin_url('admin.php?page=proofratings-billing')));
		}

		$this->error->add('error', __('We failed to update your contact email.', 'proofratings'));
	}

	/**
	 * Cancel subscription handler
	 * @since 1.0.6
	 */
	public function handle_cancel_subscription() {
		if (!isset($_GET['_nonce']) || !wp_verify_nonce($_GET['_nonce'], '_nonce_cancel_subscription')) {
			return;
		}

		if (is_proofratings_demo_mode()) {
			return $this->error->add('error_demo', __('On the demo, you are not able to cancel subscription'));
		}

		$response = wp_remote_get(PROOFRATINGS_API_URL . '/cancel_subscription', get_proofratings_api_args());
		$result = json_decode(wp_remote_retrieve_body($response));

		if (isset($result->success) && $result->success === true) {
			delete_transient('proofratings_get_subscription');
			update_proofratings_settings(array('status' => 'canceled'));
			exit(wp_safe_redirect(admin_url('admin.php?page=proofratings')));
		}

		if (isset($result->code)) {
			return $this->error->add('error', $result->message);
		}
	}

	/**
	 * Update payment method
	 * @since 1.0.6
	 */
	public function update_payment_method() {
		if (is_proofratings_demo_mode()) {
			wp_send_json(array('error' => 'On the demo, you are not able to update card.'));
		}

		$postdata = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
		unset($postdata['action']);

		$response = wp_remote_post(PROOFRATINGS_API_URL . '/update_subscription_method', get_proofratings_api_args(['data' => $postdata]));
		$result = json_decode(wp_remote_retrieve_body($response));
		if (!is_object($result)) {
			wp_send_json(array('error' => 'Unknown error'));
		}

		if (isset($result->success)) {
			delete_transient('proofratings_get_subscription');
		}

		wp_send_json($result);
	}

	/**
	 * Shows the plugin's settings page.
	 */
	public function license_page() {
		$postdata = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS); ?>
		<div class="wrap proofratings-settings-wrap">
			<header class="proofratins-header">
				<h1 class="title"><?php _e('Proofratings Activation', 'proofratings') ?></h1>
			</header>

			<div class="proofratings-form-activation-wrapper">
				<p class="lead-text"><?php _e('This plugin requires an annual subscription to cover daily, automatic rating updates. You can try Proofratings free for 30 days by signing up for a trial below.', 'proofratings') ?></p>
				<a class="button btn-primary" href="https://proofratings.com/checkout" target="_blank"><?php _e('SIGN UP FOR TRIAL', 'proofratings') ?></a>

				<div class="gap-30"></div>

				<hr class="wp-header-end">

				<form class="proofratings-activation" method="POST">
					<?php wp_nonce_field('proofratings_license_confirm_nonce', '_nonce');
					if ($this->error->has_errors()) {
						echo '<div class="notice notice-error settings-error is-dismissible">';
						echo '<p>' . $this->error->get_error_message() . '</p>';
						echo '</div>';
					} ?>

					<p>If you already signed up, please enter your license key below.</p>
					<div class="inline-field">
						<input name="license-key" type="text" value="<?php echo esc_attr(@$postdata['license-key'])  ?>" placeholder="<?php _e('License key', 'proofratings') ?>" style="width: 285px">
						<button class="button btn-primary"><?php _e('CONFIRM', 'proofratings') ?></button>
					</div>
				</form>
			</div>
		</div>
	<?php
	}

	/**
	 * Main menu of proofratings
	 */
	public function main_menu() { ?>
		<div class="wrap proofratings-settings-wrap">
			<header class="proofratins-header header-row">
				<h1 class="title"><?php _e('Proofratings Main Menu', 'proofratings') ?></h1>

				<?php
				if ($join_date = wp_date(get_option('date_format'), strtotime(get_proofratings_settings('registered')))) {
					printf('<span class="proofratings-join-date">Date Joined: %s</span>', $join_date);
				}
				?>
			</header>

			<div class="proofratings-dashboard-menu">
				<a href="<?php menu_page_url('proofratings-analytics') ?>">
					<i class="menu-icon menu-icon-analytics"></i>
					<span class="menu-label">Analytics</span>
					<p>View your rating widget data from impressions, hover, clicks, to conversions</p>
				</a>

				<a href="<?php menu_page_url('proofratings-rating-badges') ?>">
					<i class="menu-icon menu-icon-rating-badges"></i>
					<span class="menu-label"><?php echo get_proofratings()->query->global ? __('Rating Badges', 'proofratings') :  __('Locations & Rating Badges', 'proofratings'); ?></span>
					<p>Create and view all your rating trust badges</p>
				</a>

				<a href="<?php menu_page_url('proofratings-settings') ?>">
					<i class="menu-icon menu-icon-settings"></i>
					<span class="menu-label">Settings</span>
					<p>Edit review site connections, manage monthly reports and add schema</p>
				</a>

				<a href="<?php menu_page_url('proofratings-support') ?>">
					<i class="menu-icon menu-icon-support"></i>
					<span class="menu-label">Support</span>
					<p>Need help? Submit a ticket</p>
				</a>

				<a href="<?php menu_page_url('proofratings-billing') ?>">
					<i class="menu-icon menu-icon-billing"></i>
					<span class="menu-label">Billing</span>
					<p>Manage and update your payment source, subscription and invoices</p>
				</a>
			</div>
		</div>
	<?php
	}

	/**
	 * Shows the plugin's settings page.
	 */
	public function edit_location() {
		$location = $this->get_location_data();
		if (!empty($location->error)) {
			wp_die($location->error);
		} ?>
		<div class="wrap proofratings-settings-wrap">
			<header class="proofratins-header header-row">
				<div class="header-left">
					<a class="btn-back-main-menu" href="<?php menu_page_url('proofratings') ?>"><i class="icon-back fa-solid fa-angle-left"></i> Back to Main Menu</a>
					<h1 class="title"><?php _e('Edit Location', 'proofratings') ?></h1>
				</div>

				<div class="header-right">
					<a class="btn-support fa-regular fa-circle-question" href="<?php menu_page_url('proofratings-support') ?>"></a>
				</div>
			</header>

			<hr class="wp-header-end">

			<?php if ($this->error->has_errors()) : ?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo $this->error->get_error_message() ?></p>
				</div>
			<?php endif; ?>

			<?php if (isset($_POST['success'])) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php echo esc_html($_POST['success']) ?></p>
				</div>
			<?php endif; ?>


			<form method="post">
				<?php wp_nonce_field('_nonce_edit_location', '_nonce') ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e('Location Name*', 'proofratings') ?></th>
						<td>
							<input name="name" type="text" value="<?php echo esc_attr($location->name) ?>" />
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e('Location Street*', 'proofratings') ?></th>
						<td>
							<input name="street" type="text" value="<?php echo esc_attr($location->street) ?>" />
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e('Location Street 2', 'proofratings') ?></th>
						<td>
							<input name="street2" type="text" value="<?php echo esc_attr($location->street2) ?>" />
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e('Location City*', 'proofratings') ?></th>
						<td>
							<input name="city" type="text" value="<?php echo esc_attr($location->city) ?>" />
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e('Location State/Province*', 'proofratings') ?></th>
						<td>
							<input name="state" type="text" value="<?php echo esc_attr($location->state) ?>" />
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e('Location Zip/Postal*', 'proofratings') ?></th>
						<td>
							<input name="zip" type="text" value="<?php echo esc_attr($location->zip) ?>" />
						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e('Location Country*', 'proofratings') ?></th>
						<td>
							<input name="country" type="text" value="<?php echo esc_attr($location->country) ?>" />
						</td>
					</tr>
				</table>

				<?php submit_button('Update location'); ?>
			</form>
		</div>
	<?php
	}


	/**
	 * Shows the plugin's settings page.
	 */
	public function settings() { ?>
		<div class="wrap proofratings-settings-wrap">
			<div id="proofratings-settings-root"></div>
		</div>
	<?php
	}

	public function billing() {
		$request = get_transient('proofratings_get_subscription');
		if ($request === false) {
			$request = wp_safe_remote_get(PROOFRATINGS_API_URL . '/get_subscription', get_proofratings_api_args());
			if (wp_remote_retrieve_response_code($request) === 200) {
				set_transient('proofratings_get_subscription', $request, HOUR_IN_SECONDS);
			}
		}

		if (is_wp_error($request)) {
			$this->error->add('unknown', $request->get_error_message());
		}

		$contact_email = get_proofratings_settings('email');
		if (isset($_POST['contact_email'])) {
			$contact_email = $_POST['contact_email'];
		}

		$result = json_decode(wp_remote_retrieve_body($request));
		if (isset($result->code)) {
			$this->error->add($result->code, $result->message);
		} ?>
		<div class="wrap proofratings-settings-wrap">
			<header class="proofratins-header header-row">
				<div class="header-left">
					<a class="btn-back-main-menu" href="<?php menu_page_url('proofratings') ?>"><i class="icon-back fa-solid fa-angle-left"></i> Back to Main Menu</a>
					<h1 class="title"><?php _e('Billing', 'proofratings') ?></h1>
				</div>

				<div class="header-right">
					<a class="btn-support fa-regular fa-circle-question" href="<?php menu_page_url('proofratings-support') ?>"></a>
				</div>
			</header>

			<div class="proofratings-billing-wrapper">
				<hr class="wp-header-end">

				<?php if ($this->error->has_errors()) : ?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo $this->error->get_error_message() ?></p>
					</div>
				<?php endif; ?>

				<h3 style="margin-bottom: 8px">Contact email</h3>
				<form class="proofratings-contact-email" method="post">
					<?php wp_nonce_field('_nonce_update_contact_email', '_nonce') ?>
					<input name="contact_email" type="email" value="<?php echo $contact_email; ?>">
					<button class="button button-primary">Save</button>
				</form>

				<?php if (!$this->error->has_errors()) : ?>
					<div class="proofratings-customer-card">
						<h3>Credit/debit card</h3>

						<?php if (isset($result->payment_method) && $result->payment_method !== false) : ?>
							<div class="card-details billing-item">
								<div class="brand"><?php echo $result->payment_method->brand ?></div>
								<div class="card-number"><?php echo ucwords($result->payment_method->brand) ?> xxxx-<?php echo $result->payment_method->last4 ?></div>
								<div class="expiry">
									<p>Expires</p>
									<?php echo str_pad($result->payment_method->exp_month, 2, '0', STR_PAD_LEFT) ?> / <?php echo $result->payment_method->exp_year ?>
								</div>
							</div>
						<?php endif; ?>

						<form class="card-form" method="post" id="update-proofratings-card">
							<?php wp_nonce_field('_nonce_update_proofratings_card', '_nonce') ?>
							<input class="card-input card-number" name="card-number" type="text" placeholder="Card number">
							<input class="card-input card-expiry" type="text" placeholder="MM / YY" data-mask="00 / 00">
							<input class="card-input card-cvc" type="text" placeholder="CVC" data-mask="0000">
						</form>

						<div class="card-action">
							<a href="#" class="button button-link button-card-form">Update Card</a>
							<div class="update-card-buttons">
								<button class="button button-primary" form="update-proofratings-card">Update Card</button>
								<a href="#" class="button button-discard">Discard</a>
							</div>
						</div>
					</div>

					<h3 class="billing-title"><?php _e('Subscription') ?></h3>
					<div class="billing-item">
						<div class="billing-name"><?php echo $result->name ?> <span class="status"><?php echo $result->status ?></span></div>

						<div class="billing-meta">
							<?php printf('%s / %s • Created on %s', $result->price, $result->plan, date(get_option('date_format'),  $result->created)); ?>
						</div>

						<div class="billing-footer">
							<a class="btn-cancel-subscription" href="<?php echo add_query_arg('_nonce', wp_create_nonce('_nonce_cancel_subscription')) ?>"><?php _e('Cancel') ?></a>
						</div>
					</div>

					<?php if (count($result->invoices) > 0) :  ?>
						<div class="gap-40"></div>
						<h3 class="billing-title"><?php _e('Invoices') ?></h3>
						<ul class="subscription-invoices">
							<?php foreach ($result->invoices as $invoice) : ?>
								<li class="billing-item">
									<div class="billing-name">
										<?php printf('%s &nbsp;<span class="wpfs-invoice-date">(%s / %s)</span>', $invoice->number, $invoice->total, date(get_option('date_format'), $invoice->date)); ?>
									</div>
									<div class="billing-footer">
										<?php if ($invoice->pdf) {
											printf('<a href="%s" target="_blank">%s</a>', esc_url_raw($invoice->pdf), __('Download'));
										} ?>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	<?php
	}

	public function support() { ?>
		<div class="wrap proofratings-settings-wrap">
			<header class="proofratins-header header-row">
				<div class="header-left">
					<a class="btn-back-main-menu" href="<?php menu_page_url('proofratings') ?>"><i class="icon-back fa-solid fa-angle-left"></i> Back to Main Menu</a>
					<h1 class="title"><?php _e('Support', 'proofratings') ?></h1>
				</div>

				<div class="header-right">
					<!-- <a class="btn-support fa-regular fa-circle-question" href="<?php menu_page_url('proofratings-support') ?>"></a> -->
				</div>
			</header>

			<?php if ($this->error->has_errors()) : ?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo $this->error->get_error_message() ?></p>
				</div>
			<?php endif;

			if ($this->form_data->success) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php echo $this->form_data->success; ?></p>
				</div>
			<?php endif; ?>

			<form class="form-submit-ticket" method="post">
				<hr class="wp-header-end">

				<?php wp_nonce_field('_nonce_submit_ticket', '_nonce') ?>

				<label>Subject</label>
				<input class="input-field" name="subject" type="text" value="<?php echo esc_attr($this->form_data->subject) ?>">

				<label>Message</label>
				<textarea class="input-field" name="message"><?php echo esc_textarea($this->form_data->message) ?></textarea>
				<?php submit_button('SUBMIT'); ?>
			</form>
		</div>
<?php
	}
}
