<?php
/**
 * @class PPLoginFormModule
 */
class PPLoginFormModule extends FLBuilderModule {
	public $form_error = false;

	/**
	 * Holds reCAPTCHA Site Key.
	 *
	 * @since 2.8.0
	 * @var string $recaptcha_site_key
	 */
	public $recaptcha_site_key   = '';

	/**
	 * Holds reCAPTCHA Secret Key.
	 *
	 * @since 2.8.0
	 * @var string $recaptcha_secret_key
	 */
	public $recaptcha_secret_key = '';

	/**
	 * Holds reCAPTCHA V3 Site Key.
	 * 
	 * @since 2.8.0
	 * @var string $recaptcha_site_key
	 */
	public $recaptcha_v3_site_key   = '';

	/**
	 * Holds reCAPTCHA V3 Secret Key.
	 * 
	 * @since 2.8.0
	 * @var string $recaptcha_secret_key
	 */
	public $recaptcha_v3_secret_key = '';

    /**
     * @method __construct
     */
    public function __construct() {
        parent::__construct( array(
            'name'              => __('Login Form', 'bb-powerpack'),
            'description'       => __('A module for better login form.', 'bb-powerpack'),
            'group'             => pp_get_modules_group(),
            'category'		    => pp_get_modules_cat( 'content' ),
            'dir'               => BB_POWERPACK_DIR . 'modules/pp-login-form/',
            'url'               => BB_POWERPACK_URL . 'modules/pp-login-form/',
            'editor_export'     => true,
            'enabled'           => true,
            'partial_refresh'   => true,
		) );

		$this->init_recaptcha_keys();

		add_action( 'wp_ajax_pp_lf_process_login', array( $this, 'process_login' ) );
		add_action( 'wp_ajax_nopriv_pp_lf_process_login', array( $this, 'process_login' ) );
		add_action( 'wp_ajax_pp_lf_process_social_login', array( $this, 'process_social_login' ) );
		add_action( 'wp_ajax_nopriv_pp_lf_process_social_login', array( $this, 'process_social_login' ) );
		add_action( 'wp_ajax_pp_lf_process_lost_pass', array( $this, 'process_lost_password' ) );
		add_action( 'wp_ajax_nopriv_pp_lf_process_lost_pass', array( $this, 'process_lost_password' ) );
		add_action( 'wp_ajax_pp_lf_process_reset_pass', array( $this, 'process_reset_password' ) );
		add_action( 'wp_ajax_nopriv_pp_lf_process_reset_pass', array( $this, 'process_reset_password' ) );
	}

	/**
	 * Get reCAPTCHA keys from setting options.
	 *
	 * @since 2.7.10
	 *
	 * @return void
	 */
	public function init_recaptcha_keys() {
		// Get reCAPTCHA Site Key from PP admin settings.
		$this->recaptcha_site_key   = BB_PowerPack_Admin_Settings::get_option( 'bb_powerpack_recaptcha_site_key' );
		// Get reCAPTCHA Secret Key from PP admin settings.
		$this->recaptcha_secret_key = BB_PowerPack_Admin_Settings::get_option( 'bb_powerpack_recaptcha_secret_key' );
		// Get reCAPTCHA V3 Site Key from PP admin settings.
		$this->recaptcha_v3_site_key   = BB_PowerPack_Admin_Settings::get_option( 'bb_powerpack_recaptcha_v3_site_key' );
		// Get reCAPTCHA V3 Secret Key from PP admin settings.
		$this->recaptcha_v3_secret_key = BB_PowerPack_Admin_Settings::get_option( 'bb_powerpack_recaptcha_v3_secret_key' );
	}

	/**
	 * Check if social login is enabled or not.
	 * 
	 * @since 2.8.0
	 */
	public function has_social_login() {
		if ( isset( $this->settings->facebook_login ) && 'yes' === $this->settings->facebook_login ) {
			return true;
		} elseif ( isset( $this->settings->google_login ) && 'yes' === $this->settings->google_login ) {
			return true;
		}

		return false;
	}

	/**
	 * Enqueue required scripts.
	 * 
	 * @since 2.8.0
	 */
	public function enqueue_scripts() {
		if ( isset( $this->settings->google_login ) && 'yes' === $this->settings->google_login ) {
			$client_id = BB_PowerPack_Admin_Settings::get_option( 'bb_powerpack_google_client_id' );

			if ( ! empty( $client_id ) ) {
				$this->add_js( 'pp_login_form_google_api', 'https://apis.google.com/js/api:client.js' );
			}
		}

		$is_builder_active = FLBuilderModel::is_builder_active();

		// reCAPTCHA.
		if ( ! $is_builder_active ) {
			if ( ! isset( $this->settings->enable_recaptcha ) || 'no' === $this->settings->enable_recaptcha ) {
				return;
			}
		}

		if ( ! empty( $this->recaptcha_site_key ) || ! empty( $this->recaptcha_v3_site_key ) ) {

			$site_lang = substr( get_locale(), 0, 2 );

			$this->add_js(
				'g-recaptcha',
				'https://www.google.com/recaptcha/api.js?onload=onLoadPPReCaptcha&render=explicit&hl=' . $site_lang,
				array(),
				'2.0',
				true
			);
		}
	}

	/**
	 * Add attributes to the enqueued `g-recaptcha` script.
	 * 
	 * @since 2.8.0
	 *
	 * @param string $tag    Script tag.
	 * @param string $handle Registered script handle.
	 * @return string
	 */
	public function add_async_attribute( $tag, $handle ) {
		if ( ( 'g-recaptcha' !== $handle ) || ( 'g-recaptcha' === $handle && strpos( $tag, 'g-recaptcha-api' ) !== false ) ) {
			return $tag;
		}

		return str_replace( ' src', ' id="g-recaptcha-api" async="async" defer="defer" src', $tag );
	}

	/**
	 * Renders reCAPTCHA field.
	 *
	 * @since 2.8.0
	 *
	 * @param string $instance_id	Unique module ID.
	 * @return void
	 */
	public function render_recaptcha_field( $instance_id ) {
		?>
		<div class="pp-login-form-field pp-field-group pp-field-type-recaptcha">
			<?php
			$id = $instance_id;
			$settings = $this->settings;
			$recaptcha_site_key = 'invisible_v3' == $settings->recaptcha_validate_type ? $this->recaptcha_v3_site_key : $this->recaptcha_site_key;
			$recaptcha_validate_type = 'invisible_v3' == $settings->recaptcha_validate_type ? 'invisible' : $settings->recaptcha_validate_type;
			$recaptcha_theme = $settings->recaptcha_theme;

			if ( isset( $recaptcha_site_key ) && ! empty( $recaptcha_site_key ) ) { ?>
				<div id="<?php echo $id; ?>-pp-grecaptcha" class="pp-grecaptcha" data-sitekey="<?php echo $recaptcha_site_key; ?>" data-validate="<?php echo $recaptcha_validate_type; ?>" data-theme="<?php echo $recaptcha_theme; ?>"></div>
			<?php } else { ?>
				<div><?php echo pp_get_recaptcha_desc(); ?></div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Process the login form.
	 *
	 * @throws Exception On login error.
	 */
	public function process_login() {
		if (
			! isset( $_POST['pp-lf-login-nonce'] ) ||
			! check_ajax_referer( 'login_nonce', 'pp-lf-login-nonce', false ) ) {
				if ( ! isset( $_POST['reauth'] ) || empty( $_POST['reauth'] ) ) {
					wp_send_json_error( __( 'Error: Invalid data.', 'bb-powerpack' ) );
				}
		}

		$recaptcha_response = isset( $_POST['recaptcha_response'] ) ? $_POST['recaptcha_response'] : false;

		// Validate reCAPTCHA if enabled
		if ( isset( $_POST['recaptcha'] ) && $recaptcha_response ) {
			$this->init_recaptcha_keys();

			$recaptcha_validate_type = sanitize_text_field( $_POST['recaptcha_validate'] );
			$recaptcha_site_key   = 'invisible_v3' == $recaptcha_validate_type ? $this->recaptcha_v3_site_key : $this->recaptcha_site_key;
			$recaptcha_secret_key = 'invisible_v3' == $recaptcha_validate_type ? $this->recaptcha_v3_secret_key : $this->recaptcha_secret_key;

			if ( ! empty( $recaptcha_secret_key ) && ! empty( $recaptcha_site_key ) ) {
				if ( version_compare( phpversion(), '5.3', '>=' ) ) {
					$validate = new BB_PowerPack_ReCaptcha( $recaptcha_secret_key, $recaptcha_validate_type, $recaptcha_response );
					if ( ! $validate->is_success() ) {
						wp_send_json_error( __( 'Error verifying reCAPTCHA, please try again.', 'bb-powerpack' ) );
					}
				} else {
					wp_send_json_error( __( 'reCAPTCHA API requires PHP version 5.3 or above.', 'bb-powerpack' ) );
				}
			} else {
				wp_send_json_error( __( 'Your reCAPTCHA Site or Secret Key is missing!', 'bb-powerpack' ) );
			}
		}

		if ( isset( $_POST['username'], $_POST['password'] ) ) {
			try {
				$creds = array(
					'user_login'    => trim( wp_unslash( $_POST['username'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					'user_password' => $_POST['password'], // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
					'remember'      => isset( $_POST['remember'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				);

				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'pp_login_form_process_login_errors', $validation_error, $creds['user_login'], $creds['user_password'] );

				if ( $validation_error->get_error_code() ) {
					throw new Exception( '<strong>' . __( 'Error:', 'bb-powerpack' ) . '</strong> ' . $validation_error->get_error_message() );
				}

				if ( empty( $creds['user_login'] ) ) {
					throw new Exception( '<strong>' . __( 'Error:', 'bb-powerpack' ) . '</strong> ' . __( 'Username is required.', 'bb-powerpack' ) );
				}

				// On multisite, ensure user exists on current site, if not add them before allowing login.
				if ( is_multisite() ) {
					$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

					if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
						add_user_to_blog( get_current_blog_id(), $user_data->ID, $user_data->roles[0] );
					}
				}

				// Perform the login.
				$user = wp_signon( apply_filters( 'pp_login_form_credentials', $creds ), is_ssl() );

				if ( is_wp_error( $user ) ) {
					$message = $user->get_error_message();
					// Exclude BuddyPress activation link.
					// if ( 'bp_account_not_activated' !== $user->get_error_code() ) {
					// 	$message = preg_replace( '/<\/?a[^>].*>/', '', $message );
					// }
					throw new Exception( $message );
				} else {
					wp_send_json_success( array(
						'redirect_url' => $this->get_redirect_url(),
					) );
				}
			} catch ( Exception $e ) {
				$this->form_error = apply_filters( 'login_errors', $e->getMessage() );
				wp_send_json_error( $this->form_error );
			}
		} else {
			wp_send_json_error( __( 'Username or password is missing.', 'bb-powerpack' ) );
		}
	}

	/**
	 * Process social login.
	 * 
	 * @since 2.8.0
	 */
	public function process_social_login() {
		if (
			! isset( $_POST['nonce'] ) ||
			! check_ajax_referer( 'login_nonce', 'nonce', false ) ) {
				wp_send_json_error( __( 'Invalid data.', 'bb-powerpack' ) );
		}

		if ( ! isset( $_POST['provider'] ) ) {
			wp_send_json_error( __( 'Provider was not set.', 'bb-powerpack' ) );
		}

		if ( 'facebook' === $_POST['provider'] ) {
			$this->process_facebook_login();
		}
		if ( 'google' === $_POST['provider'] ) {
			$this->process_google_login();
		}
	}

	/**
	 * Process Facebook login.
	 * 
	 * @since 2.8.0
	 * @access private
	 */
	private function process_facebook_login() {
		$err_unauth_access = __( 'Unauthorized access.', 'bb-powerpack' );

		if ( ! isset( $_POST['auth_response'] ) ) {
			wp_send_json_error( $err_unauth_access );
		}
		if ( ! isset( $_POST['user_data'] ) ) {
			wp_send_json_error( $err_unauth_access );
		}
		
		$auth_response = $_POST['auth_response'];
		$user_data = $_POST['user_data'];

		// Email can be empty in case user was registered using phone number on Facebook.
		$email = isset( $user_data['email'] ) ? sanitize_email( $user_data['email'] ) : '';
		$name = sanitize_user( $user_data['name'] );
		$first_name = sanitize_user( $user_data['first_name'] );
		$last_name = sanitize_user( $user_data['last_name'] );
		$id = sanitize_text_field( $user_data['id'] );
		$access_token = sanitize_text_field( $auth_response['accessToken'] );
		$user_id = sanitize_text_field( $auth_response['userID'] );

		$client_id = pp_get_fb_app_id();
		$client_secret = BB_PowerPack_Admin_Settings::get_option( 'bb_powerpack_fb_app_secret' );

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		// Get Facebook App Access Token.
		$response = wp_remote_get( add_query_arg( array(
			'client_id' 	=> $client_id, // Facebook App ID.
			'client_secret' => $client_secret, // Facebook App Secret.
			'grant_type' 	=> 'client_credentials'
		), 'https://graph.facebook.com/oauth/access_token' ) );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		$app_access_token = $response->access_token;

		// Get valid Facebook User ID.
		$user_response = wp_remote_get( add_query_arg( array(
			'input_token'	=> $access_token,
			'access_token'	=> $app_access_token,
		), 'https://graph.facebook.com/debug_token' ) );

		if ( is_wp_error( $user_response ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		$user_response = json_decode( wp_remote_retrieve_body( $user_response ), true );

		if ( false === $user_response['is_valid'] ) {
			wp_send_json_error( $err_unauth_access );
		}

		if ( $user_id !== $user_response['data']['user_id'] ) {
			wp_send_json_error( $err_unauth_access );
		}

		$valid_user_id = $user_response['data']['user_id'];

		// Get Facebook User Email.
		$response = wp_remote_get( add_query_arg( array(
			'fields'		=> 'email',
			'access_token' 	=> $access_token,
		), 'https://graph.facebook.com/' . $valid_user_id ) );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		$response = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! empty( $email ) && $email !== $response->email ) {
			wp_send_json_error( $err_unauth_access );
		}

		if ( empty( $email ) && empty( $response->email ) ) {
			$user_email = $valid_user_id . '@facebook.com';
		} else {
			$user_email = $response->email;
		}

		$this->do_social_login( array(
			'email' 		=> $user_email,
			'first_name' 	=> $first_name,
			'last_name' 	=> $last_name,
			'provider' 		=> 'facebook',
		) );
	}

	/**
	 * Process Google login.
	 * 
	 * @since 2.8.0
	 * @access private
	 */
	private function process_google_login() {
		$err_unauth_access = __( 'Unauthorized access.', 'bb-powerpack' );

		if ( ! isset( $_POST['user_data'] ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		$user_data = $_POST['user_data'];

		$email = sanitize_email( $user_data['email'] );
		$name = sanitize_user( $user_data['name'] );
		$id_token = sanitize_text_field( $user_data['hash'] );
		$client_id = BB_PowerPack_Admin_Settings::get_option( 'bb_powerpack_google_client_id' );

		require_once BB_POWERPACK_DIR . 'modules/pp-login-form/includes/vendor/autoload.php';

		// Let's verify id_token.
		$google_client = new Google_Client( array(
			'client_id' => $client_id,
		) );

		$play_load = $google_client->verifyIdToken( $id_token );

		if ( empty( $play_load ) ) {
			wp_send_json_error( $err_unauth_access );
		}

		if ( $email !== $play_load['email'] || $client_id !== $play_load['aud'] ) {
			wp_send_json_error( $err_unauth_access );
		}

		$this->do_social_login( array(
			'email' 		=> $email,
			'first_name' 	=> $name,
			'provider' 		=> 'google',
		) );
	}

	/**
	 * Login into WP via social platforms.
	 * 
	 * @since 2.8.0
	 * @access private
	 */
	private function do_social_login( $data, $validate_password = false ) {
		$email 		= $data['email'];
		$username 	= explode( '@', $email );
		$username 	= $username[0];
		$userdata 	= get_user_by( 'email', $email );

		if ( ! empty( $userdata ) ) {
			if ( $validate_password && isset( $data['password'] ) ) {
				if ( ! wp_check_password( $data['password'], $userdata->user_pass, $userdata->ID ) ) {
					wp_send_json_error( __( 'Password does not match.', 'bb-powerpack' ) );
				}
			}

			$user_id    = $userdata->ID;
			$user_email = $userdata->user_email;
			$username   = $userdata->user_login;

			wp_set_auth_cookie( $user_id );
			wp_set_current_user( $user_id, $username );

			do_action( 'wp_login', $userdata->user_login, $userdata );

			wp_send_json_success( array(
				'redirect_url' => $this->get_redirect_url(),
			) );
		} else {
			if ( ! apply_filters( 'pp_login_form_social_login_registration', get_option( 'users_can_register' ) ) ) {
				wp_send_json_error( array(
					'code'		=> 'registration_disabled',
					'message' 	=> sprintf( __( 'Account does not exist with the email %s', 'bb-powerpack' ), $email ),
				) );
			}

			if ( username_exists( $username ) ) {
				$username .= '-' . zeroise( wp_rand( 0, 9999 ), 4 );
			}

			$data['username'] = $username;
			$data['password'] = wp_generate_password( apply_filters( 'pp_login_form_password_length', 12 ), true, false );

			$user_id = wp_insert_user( array(
				'user_login' 	=> $data['username'],
				'user_pass'		=> $data['password'],
				'user_email'	=> $email,
				'first_name'	=> isset( $data['first_name'] ) ? $data['first_name'] : '',
				'last_name'		=> isset( $data['last_name'] ) ? $data['last_name'] : '',
			) );

			if ( is_wp_error( $user_id ) ) {
				wp_send_json_error( $user_id->get_error_message() );
			}

			update_user_meta( $user_id, 'pp_login_form_provider', $data['provider'] );

			$this->do_social_login( $data, true );
		}
	}

	public function process_lost_password() {
		if (
			! isset( $_POST['pp-lf-lost-password-nonce'] ) ||
			! check_ajax_referer( 'lost_password', 'pp-lf-lost-password-nonce', false ) ) {
				wp_send_json_error( __( 'Invalid data.', 'bb-powerpack' ) );
		}

		$success = $this->retrieve_password();

		if ( ! $success ) {
			wp_send_json_error( $this->form_error );
		}

		wp_send_json_success();
	}

	private function retrieve_password() {
		$login = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : ''; // WPCS: input var ok, CSRF ok.

		if ( empty( $login ) ) {

			$this->form_error = __( 'Enter a username or email address.', 'bb-powerpack' );

			return false;

		} else {
			// Check on username first, as customers can use emails as usernames.
			$user_data = get_user_by( 'login', $login );
		}

		// If no user found, check if it login is email and lookup user based on email.
		if ( ! $user_data && is_email( $login ) ) {
			$user_data = get_user_by( 'email', $login );
		}

		$errors = new WP_Error();

		do_action( 'lostpassword_post', $errors );

		if ( $errors->get_error_code() ) {
			$this->form_error = $errors->get_error_message();

			return false;
		}

		if ( ! $user_data ) {
			$this->form_error = __( 'Invalid username or email.', 'bb-powerpack' );

			return false;
		}

		if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
			$this->form_error = __( 'Invalid username or email.', 'bb-powerpack' );

			return false;
		}

		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;

		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow ) {

			$this->form_error = __( 'Password reset is not allowed for this user', 'bb-powerpack' );

			return false;

		} elseif ( is_wp_error( $allow ) ) {

			$this->form_error = $errors->get_error_message();

			return false;
		}

		// Get password reset key (function introduced in WordPress 4.4).
		$key = get_password_reset_key( $user_data );

		$page_url = esc_url_raw( $_POST['page_url'] );

		$reset_url = add_query_arg( array(
			'reset_pass' => 1,
			'key'	=> $key,
			'id'	=> $user_data->ID
		), $page_url );

		// Send email notification.
		$email_sent = $this->send_activation_email( $user_data, $reset_url );

		if ( $email_sent ) {
			$this->form_error = esc_html__( 'An error occurred sending email. Please try again.', 'bb-powerpack' );
		}

		return $email_sent;
	}

	private function send_activation_email( $user, $reset_url ) {
		$email = $user->data->user_email;
		$blogname = esc_html( wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ) );
		$admin_email = get_option( 'admin_email' );
		$subject = sprintf( esc_html__( 'Password Reset Request for %s', 'bb-powerpack' ), $blogname );

		$content = '';
		/* translators: %s: Username */
		$content .= '<p>' . sprintf( esc_html__( 'Hi %s,', 'bb-powerpack' ), esc_html( $user->data->user_login ) ) . '</p>';
		/* translators: %s: Site name */
		$content .= '<p>' . sprintf( esc_html__( 'Someone has requested a new password for the following account on %s:', 'bb-powerpack' ), $blogname ) . '</p>';
		/* translators: %s Username */
		$content .= '<p>' . sprintf( esc_html__( 'Username: %s', 'bb-powerpack' ), esc_html( $user->data->user_login ) ) . '</p>';
		$content .= esc_html__( 'If you didn\'t make this request, just ignore this email. If you\'d like to proceed:', 'bb-powerpack' );
		$content .= '<p>';
		$content .= '<a class="link" href="' . esc_url( $reset_url ) . '">';
		$content .= esc_html__( 'Click here to reset your password', 'bb-powerpack' );
		$content .= '</a>';
		$content .= '</p>';

		// translators: %s: email_from_name
		$headers = sprintf( 'From: %s <%s>' . "\r\n", $blogname, get_option( 'admin_email' ) );
		// translators: %s: email_reply_to
		$headers .= sprintf( 'Reply-To: %s' . "\r\n", $admin_email );
		$headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

		// Send email to user.
		$email_sent = wp_mail( $email, $subject, $content, $headers );

		return $email_sent;
	}

	public function process_reset_password() {
		if (
			! isset( $_POST['pp-lf-reset-password-nonce'] ) ||
			! check_ajax_referer( 'reset_password', 'pp-lf-reset-password-nonce', false ) ) {
				wp_send_json_error( __( 'Invalid data.', 'bb-powerpack' ) );
		}

		$posted_fields = array( 'password_1', 'password_2', 'reset_key', 'reset_login' );

		foreach ( $posted_fields as $field ) {
			if ( ! isset( $_POST[ $field ] ) ) {
				return;
			}

			if ( in_array( $field, array( 'password_1', 'password_2' ) ) ) {
				// Don't unslash password fields
				$posted_fields[ $field ] = $_POST[ $field ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			} else {
				$posted_fields[ $field ] = wp_unslash( $_POST[ $field ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			}
		}

		$user = $this->check_password_reset_key( $posted_fields['reset_key'], $posted_fields['reset_login'] );

		if ( $user instanceof WP_User ) {
			if ( empty( $posted_fields['password_1'] ) ) {
				$this->form_error = __( 'Please enter your password.', 'bb-powerpack' );
			}

			if ( $posted_fields['password_1'] !== $posted_fields['password_2'] ) {
				$this->form_error = __( 'Passwords do not match.', 'bb-powerpack' );
			}

			$errors = new WP_Error();

			do_action( 'validate_password_reset', $errors, $user );

			if ( is_wp_error( $errors ) && $errors->get_error_messages() ) {
				foreach ( $errors->get_error_messages() as $error ) {
					$this->form_error .= $error . "\r\n";
				}
			}

			if ( empty( $this->form_error ) ) {
				$this->reset_password( $user, $posted_fields['password_1'] );

				do_action( 'pp_login_form_user_reset_password', $user );

				wp_send_json_success();
			}
		}

		if ( ! empty( $this->form_error ) ) {
			wp_send_json_error( $this->form_error );
		}
	}

	public function check_password_reset_key( $key, $login ) {
		// Check for the password reset key.
		// Get user data or an error message in case of invalid or expired key.
		$user = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			$this->form_error = __( 'This key is invalid or has already been used. Please reset your password again if needed.', 'bb-powerpack' );
			return false;
		}

		return $user;
	}

	/**
	 * Handles resetting the user's password.
	 *
	 * @param object $user     The user.
	 * @param string $new_pass New password for the user in plaintext.
	 */
	private function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );
		$this->set_reset_password_cookie();

		if ( ! apply_filters( 'pp_login_form_disable_password_change_notification', false ) ) {
			wp_password_change_notification( $user );
		}
	}

	/**
	 * Set or unset the cookie.
	 *
	 * @param string $value Cookie value.
	 */
	private function set_reset_password_cookie( $value = '' ) {
		$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
		$rp_path   = isset( $_POST['page_url'] ) ? current( explode( '?', wp_unslash( $_POST['page_url'] ) ) ) : ''; // WPCS: input var ok, sanitization ok.

		if ( $value ) {
			setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		} else {
			setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		}
	}

	private function get_redirect_url() {
		if ( ! empty( $_POST['redirect'] ) ) {
			$redirect = wp_unslash( $_POST['redirect'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		} elseif ( $this->get_raw_referer() ) {
			$redirect = $this->get_raw_referer();
		} else {
			$redirect = wp_unslash( $_POST['page_url'] );
		}

		return wp_validate_redirect( $redirect, wp_unslash( $_POST['page_url'] ) );
	}

	/**
	 * Get raw referer.
	 * 
	 * @since 2.8.0
	 * @access private
	 */
	private function get_raw_referer() {
		if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) { // WPCS: input var ok, CSRF ok.
			return wp_unslash( $_REQUEST['_wp_http_referer'] ); // WPCS: input var ok, CSRF ok, sanitization ok.
		} elseif ( ! empty( $_SERVER['HTTP_REFERER'] ) ) { // WPCS: input var ok, CSRF ok.
			return wp_unslash( $_SERVER['HTTP_REFERER'] ); // WPCS: input var ok, CSRF ok, sanitization ok.
		}

		return false;
	}

	public function get_registration_url() {
		$page_id = BB_PowerPack_Admin_Settings::get_option( 'bb_powerpack_register_page', true );

		if ( empty( $page_id ) ) {
			return wp_registration_url();
		}

		return get_permalink( $page_id );
	}

	public function get_error_message() {
		return $this->form_error;
	}
}

BB_PowerPack::register_module('PPLoginFormModule', array(
	'general'	=> array(
		'title'		=> __('General', 'bb-powerpack'),
		'sections'	=> array(
			'form_fields'	=> array(
				'title'			=> '',
				'fields'		=> array(
					'show_labels'	=> array(
						'type'			=> 'pp-switch',
						'label'			=> __('Label', 'bb-powerpack'),
						'default'		=> 'yes',
						'options'		=> array(
							'yes'			=> __('Show', 'bb-powerpack'),
							'no'			=> __('Hide', 'bb-powerpack')
						),
					)
				)
			),
			'fields_label'	=> array(
				'title'			=> __('Label & Placeholder', 'bb-powerpack'),
				'collapsed'		=> true,
				'fields'		=> array(
					'username_label'	=> array(
						'type'		=> 'text',
						'label'		=> __('Username Label', 'bb-powerpack'),
						'default'	=> __('Username or Email Address', 'bb-powerpack'),
						'connections'	=> array('string')
					),
					'username_placeholder'	=> array(
						'type'		=> 'text',
						'label'		=> __('Username Placeholder', 'bb-powerpack'),
						'default'	=> __('Username or Email Address', 'bb-powerpack'),
						'connections'	=> array('string')
					),
					'password_label'	=> array(
						'type'		=> 'text',
						'label'		=> __('Password Label', 'bb-powerpack'),
						'default'	=> __('Password', 'bb-powerpack'),
						'connections'	=> array('string')
					),
					'password_placeholder'	=> array(
						'type'		=> 'text',
						'label'		=> __('Password Placeholder', 'bb-powerpack'),
						'default'	=> __('Password', 'bb-powerpack'),
						'connections'	=> array('string')
					),
				)
			),
			'button'	=> array(
				'title'		=> __('Button', 'bb-powerpack'),
				'collapsed'	=> true,
				'fields'	=> array(
					'button_text'	=> array(
						'type'			=> 'text',
						'label'			=> __('Text', 'bb-powerpack'),
						'default'		=> __('Log In', 'bb-powerpack'),
						'connections'	=> array('string')
					),
				)
			),
			'recaptcha' => array(
				'title'       => 'reCAPTCHA',
				'description' => pp_get_recaptcha_desc(),
				'collapsed'	=> true,
				'fields'      => array(
					'enable_recaptcha'        => array(
						'type'    => 'pp-switch',
						'label'   => __( 'Enable reCAPTCHA', 'bb-powerpack' ),
						'default' => 'no',
						'toggle'  => array(
							'yes' => array(
								'fields' => array( 'recaptcha_validate_type', 'recaptcha_theme' ),
							),
						),
						'preview'	=> array(
							'type'		=> 'none',
						),
					),
					'recaptcha_validate_type' => array(
						'type'    => 'select',
						'label'   => __( 'Validate Type', 'bb-powerpack' ),
						'default' => 'normal',
						'options' => array(
							'normal'    => __( '"I\'m not a robot" checkbox (V2)', 'bb-powerpack' ),
							'invisible' => __( 'Invisible (V2)', 'bb-powerpack' ),
							'invisible_v3' => __( 'Invisible (V3)', 'bb-powerpack' ),
						),
						'help'    => __( 'Validate users with checkbox or in the background.<br />Note: Checkbox and Invisible types use seperate API keys.', 'bb-powerpack' ),
						'preview' => array(
							'type' => 'none',
						),
					),
					'recaptcha_theme'         => array(
						'type'    => 'pp-switch',
						'label'   => __( 'Theme', 'bb-powerpack' ),
						'default' => 'light',
						'options' => array(
							'light' => __( 'Light', 'bb-powerpack' ),
							'dark'  => __( 'Dark', 'bb-powerpack' ),
						),
						'preview' => array(
							'type' => 'none',
						),
					),
				),
			),
			'additional_options'	=> array(
				'title'		=> __('Additional Options', 'bb-powerpack'),
				'collapsed'	=> true,
				'fields'	=> array(
					'redirect_after_login'	=> array(
						'type'		=> 'pp-switch',
						'label'		=> __('Redirect After Login', 'bb-powerpack'),
						'default'	=> 'no',
						'options'	=> array(
							'yes'		=> __('Yes', 'bb-powerpack'),
							'no'		=> __('No', 'bb-powerpack')
						),
						'preview'	=> array(
							'type'		=> 'none',
						),
						'toggle'	=> array(
							'yes'		=> array(
								'fields'	=> array('redirect_url')
							)
						)
					),
					'redirect_url'	=> array(
						'type'			=> 'link',
						'label'			=> __('Redirect URL', 'bb-powerpack'),
						'description'	=> __('Note: Because of security reasons, you can ONLY use your current domain.', 'bb-powerpack'),
						'connections'	=> array('url', 'string'),
						'show_target'	=> false,
						'show_nofollow'	=> false,
					),
					'redirect_after_logout'	=> array(
						'type'		=> 'pp-switch',
						'label'		=> __('Redirect After Logout', 'bb-powerpack'),
						'default'	=> 'no',
						'options'	=> array(
							'yes'		=> __('Yes', 'bb-powerpack'),
							'no'		=> __('No', 'bb-powerpack')
						),
						'preview'	=> array(
							'type'		=> 'none',
						),
						'toggle'	=> array(
							'yes'		=> array(
								'fields'	=> array('redirect_logout_url')
							)
						)
					),
					'redirect_logout_url'	=> array(
						'type'			=> 'link',
						'label'			=> __('Redirect URL', 'bb-powerpack'),
						'description'	=> __('Note: Because of security reasons, you can ONLY use your current domain.', 'bb-powerpack'),
						'connections'	=> array('url', 'string'),
						'show_target'	=> false,
						'show_nofollow'	=> false,
					),
					'show_lost_password'	=> array(
						'type'			=> 'pp-switch',
						'label'			=> __('Show Password Reset Link', 'bb-powerpack'),
						'default'		=> 'yes',
						'options'		=> array(
							'yes'			=> __('Yes', 'bb-powerpack'),
							'no'			=> __('No', 'bb-powerpack')
						),
						'toggle'		=> array(
							'yes'			=> array(
								'fields'		=> array('lost_password_text')
							)
						)
					),
					'lost_password_text'	=> array(
						'type'		=> 'text',
						'label'		=> __('Text', 'bb-powerpack'),
						'default'	=> __('Lost your password?', 'bb-powerpack'),
						'preview'	=> array(
							'type'		=> 'text',
							'selector'	=> '.pp-field-group .pp-login-lost-password'
						),
						'connections'	=> array('string')
					),
					'show_register'		=> array(
						'type'			=> 'pp-switch',
						'label'			=> __('Show Register Link', 'bb-powerpack'),
						'help'			=> __('This option will only be available if the registration is enabled in WP admin general settings.', 'bb-powerpack'),
						'default'		=> 'yes',
						'options'		=> array(
							'yes'			=> __('Yes', 'bb-powerpack'),
							'no'			=> __('No', 'bb-powerpack')
						),
					),
					'show_remember_me'	=> array(
						'type'			=> 'pp-switch',
						'label'			=> __('Show Remember Me', 'bb-powerpack'),
						'default'		=> 'yes',
						'options'		=> array(
							'yes'			=> __('Yes', 'bb-powerpack'),
							'no'			=> __('No', 'bb-powerpack')
						),
						'toggle'		=> array(
							'yes'			=> array(
								'fields'		=> array('remember_me_text')
							)
						)
					),
					'remember_me_text'	=> array(
						'type'		=> 'text',
						'label'		=> __('Text', 'bb-powerpack'),
						'default'	=> __('Remember Me', 'bb-powerpack'),
						'preview'	=> array(
							'type'		=> 'text',
							'selector'	=> '.pp-field-group .pp-login-remember-me'
						),
						'connections'	=> array('string')
					),
					'show_logged_in_message'	=> array(
						'type'			=> 'pp-switch',
						'label'			=> __('Show Logged in Message', 'bb-powerpack'),
						'default'		=> 'yes',
						'options'		=> array(
							'yes'			=> __('Yes', 'bb-powerpack'),
							'no'			=> __('No', 'bb-powerpack')
						)
					)
				)
			),
			'social_login'	=> array(
				'title'			=> __( 'Social Login', 'bb-powerpack' ),
				'collapsed'		=> true,
				'fields'		=> array(
					'facebook_login'	=> array(
						'type'	=> 'pp-switch',
						'label'	=> __( 'Enable Facebook Login', 'bb-powerpack' ),
						'default' => 'no',
						'toggle'	=> array(
							'yes'	=> array(
								'fields'	=> array( 'facebook_login_help' ),
							),
						),
					),
					'facebook_login_help'	=> array(
						'type'	=> 'raw',
						'content' => sprintf( __( 'To use Facebook Login, you need to configure App ID and App Secret under <a href="%s" target="_blank">Integration Settings</a>', 'bb-powerpack' ), BB_PowerPack_Admin_Settings::get_form_action( '&tab=integration' ) ),
					),
					'google_login'	=> array(
						'type'	=> 'pp-switch',
						'label'	=> __( 'Enable Google Login', 'bb-powerpack' ),
						'default' => 'no',
						'toggle'	=> array(
							'yes'	=> array(
								'fields'	=> array( 'google_login_help' ),
							),
						),
					),
					'google_login_help'	=> array(
						'type'	=> 'raw',
						'content' => sprintf( __( 'To use Google Login, you need to configure Google Client ID under <a href="%s" target="_blank">Integration Settings</a>', 'bb-powerpack' ), BB_PowerPack_Admin_Settings::get_form_action( '&tab=integration' ) ),
					),
					'separator'	=> array(
						'type'		=> 'pp-switch',
						'label'		=> __( 'Use Separator', 'bb-powerpack' ),
						'default'	=> 'yes',
						'toggle'	=> array(
							'yes'	=> array(
								'fields'	=> array( 'separator_text', 'separator_width', 'separator_color', 'separator_text_color', 'separator_text_typography' ),
							),
						),
					),
					'separator_text'	=> array(
						'type'	=> 'text',
						'label'	=> __( 'Separator Text', 'bb-powerpack' ),
						'default' => __( 'Continue with', 'bb-powerpack' ),
						'connections' => array( 'string' ),
					),
				),
			),
		)
	),
	'style'		=> array(
		'title'		=> __('Style', 'bb-powerpack'),
		'sections'		=> array(
			'general_style'	=> array(
				'title'			=> __('General', 'bb-powerpack'),
				'fields'		=> array(
					'fields_spacing'	=> array(
						'type'			=> 'unit',
						'label'			=> __('Fields Spacing', 'bb-powerpack'),
						'default'		=> '',
						'units'			=> array('px'),
						'slider'		=> true,
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group',
							'property'		=> 'margin-bottom',
							'unit'			=> 'px'
						)
					),
					'links_color'	=> array(
						'type'			=> 'color',
						'label'			=> __('Links Color', 'bb-powerpack'),
						'default'		=> '',
						'show_reset'	=> true,
						'connections'	=> array('color'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group > a',
							'property'		=> 'color',
						)
					),
					'links_hover_color'	=> array(
						'type'			=> 'color',
						'label'			=> __('Links Hover Color', 'bb-powerpack'),
						'default'		=> '',
						'show_reset'	=> true,
						'connections'	=> array('color'),
						'preview'		=> array(
							'type'			=> 'none'
						)
					),
				)
			),
			'form_style'	=> array(
				'title'			=> __('Form', 'bb-powerpack'),
				'collapsed'		=> true,
				'fields'		=> array(
					'form_bg_color'		=> array(
						'type'			=> 'color',
						'label'			=> __('Background Color', 'bb-powerpack'),
						'default'		=> '',
						'show_reset'	=> true,
						'show_alpha'	=> true,
						'connections'	=> array('color'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-login-form',
							'property'		=> 'background-color'
						)
					),
					'form_padding'	=> array(
						'type'			=> 'dimension',
						'label'			=> __('Padding', 'bb-powerpack'),
						'default'		=> '',
						'slider'		=> true,
						'units'			=> array('px'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-login-form',
							'property'		=> 'padding',
							'unit'			=> 'px'
						)
					),
					'form_border'	=> array(
						'type'			=> 'border',
						'label'			=> __('Border', 'bb-powerpack'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-login-form'
						)
					)
				)
			),
			'label_style'	=> array(
				'title'			=> __('Label', 'bb-powerpack'),
				'collapsed'		=> true,
				'fields'		=> array(
					'label_spacing'	=> array(
						'type'			=> 'unit',
						'label'			=> __('Spacing', 'bb-powerpack'),
						'default'		=> '',
						'units'			=> array('px'),
						'slider'		=> true,
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group > label',
							'property'		=> 'margin-bottom',
							'unit'			=> 'px'
						)
					),
					'label_color'	=> array(
						'type'			=> 'color',
						'label'			=> __('Text Color', 'bb-powerpack'),
						'default'		=> '',
						'show_reset'	=> true,
						'connections'	=> array('color'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group > label',
							'property'		=> 'color',
						)
					)
				)
			),
			'fields_style'	=> array(
				'title'			=> __('Fields', 'bb-powerpack'),
				'collapsed'		=> true,
				'fields'		=> array(
					'field_text_color'	=> array(
						'type'			=> 'color',
						'label'			=> __('Text Color', 'bb-powerpack'),
						'default'		=> '',
						'show_reset'	=> true,
						'connections'	=> array('color'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group .pp-login-form--input',
							'property'		=> 'color',
						)
					),
					'field_bg_color'	=> array(
						'type'			=> 'color',
						'label'			=> __('Background Color', 'bb-powerpack'),
						'default'		=> '',
						'show_reset'	=> true,
						'show_alpha'	=> true,
						'connections'	=> array('color'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group .pp-login-form--input',
							'property'		=> 'background-color',
						)
					),
					'field_height'	=> array(
						'type'			=> 'unit',
						'label'			=> __('Height', 'bb-powerpack'),
						'default'		=> '',
						'slider'		=> true,
						'responsive'	=> true,
						'units'			=> array('px'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group .pp-login-form--input',
							'property'		=> 'height',
							'unit'			=> 'px'
						)
					),
					'field_padding'	=> array(
						'type'			=> 'dimension',
						'label'			=> __('Padding', 'bb-powerpack'),
						'default'		=> '',
						'slider'		=> true,
						'responsive'	=> true,
						'units'			=> array('px'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group .pp-login-form--input',
							'property'		=> 'padding',
							'unit'			=> 'px'
						)
					),
					'field_border'	=> array(
						'type'			=> 'border',
						'label'			=> __('Border', 'bb-powerpack'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group .pp-login-form--input',
						)
					),
					'field_border_focus_color'	=> array(
						'type'			=> 'color',
						'label'			=> __('Border Focus Color', 'bb-powerpack'),
						'default'		=> '',
						'show_reset'	=> true,
						'connections'	=> array('color'),
						'preview'		=> array(
							'type'			=> 'none'
						)
					)
				)
			),
			'button_style'	=> array(
				'title'			=> __('Button', 'bb-powerpack'),
				'collapsed'		=> true,
				'fields'		=> array(
					'button_align'	=> array(
						'type'			=> 'align',
						'label'			=> __('Alignment', 'bb-powerpack'),
						'default'		=> 'left',
						'responsive'	=> true,
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group.pp-field-type-submit, .pp-field-group.pp-field-type-link, .pp-field-group.pp-field-type-recaptcha',
							'property'		=> 'text-align'
						)
					),
					'button_text_color'	=> array(
						'type'			=> 'color',
						'label'			=> __('Text Color', 'bb-powerpack'),
						'default'		=> '',
						'show_reset'	=> true,
						'connections'	=> array('color'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group .pp-login-form--button',
							'property'		=> 'color',
						)
					),
					'button_text_hover_color'	=> array(
						'type'			=> 'color',
						'label'			=> __('Text Hover Color', 'bb-powerpack'),
						'default'		=> '',
						'show_reset'	=> true,
						'connections'	=> array('color'),
						'preview'		=> array(
							'type'			=> 'none',
						)
					),
					'button_bg_color'	=> array(
						'type'			=> 'color',
						'label'			=> __('Background Color', 'bb-powerpack'),
						'default'		=> '',
						'show_reset'	=> true,
						'show_alpha'	=> true,
						'connections'	=> array('color'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group .pp-login-form--button',
							'property'		=> 'background-color',
						)
					),
					'button_bg_hover_color'	=> array(
						'type'			=> 'color',
						'label'			=> __('Background Hover Color', 'bb-powerpack'),
						'default'		=> '',
						'show_reset'	=> true,
						'show_alpha'	=> true,
						'connections'	=> array('color'),
						'preview'		=> array(
							'type'			=> 'none',
						)
					),
					'button_border'	=> array(
						'type'			=> 'border',
						'label'			=> __('Border', 'bb-powerpack'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group .pp-login-form--button',
						)
					),
					'button_border_hover_color'	=> array(
						'type'			=> 'color',
						'label'			=> __('Border Hover Color', 'bb-powerpack'),
						'default'		=> '',
						'show_reset'	=> true,
						'connections'	=> array('color'),
						'preview'		=> array(
							'type'			=> 'none',
						)
					),
					'button_padding'	=> array(
						'type'				=> 'dimension',
						'label'				=> __('Padding', 'bb-powerpack'),
						'default'			=> '',
						'slider'			=> true,
						'units'				=> array('px'),
						'preview'			=> array(
							'type'				=> 'css',
							'selector'			=> '.pp-field-group .pp-login-form--button',
							'property'			=> 'padding',
							'unit'				=> 'px'
						)
					),
					'button_width'	=> array(
						'type'			=> 'unit',
						'label'			=> __('Width', 'bb-powerpack'),
						'default'		=> '',
						'help'			=> __('Leave empty for default width.', 'bb-powerpack'),
						'slider'		=> true,
						'responsive'	=> true,
						'units'			=> array('px', '%'),
						'preview'		=> array(
							'type'			=> 'css',
							'selector'		=> '.pp-field-group .pp-login-form--button',
							'property'		=> 'width',
						)
					)
				)
			),
			'social_buttons_style'	=> array(
				'title'	=> __( 'Social Login', 'bb-powerpack' ),
				'collapsed' => true,
				'fields' => array(
					'social_button_position' => array(
						'type'	=> 'select',
						'label'	=> __( 'Position', 'bb-powerpack' ),
						'default' => 'below',
						'options'	=> array(
							'above'	=> __( 'Above the form', 'bb-powerpack' ),
							'below'	=> __( 'Below the form', 'bb-powerpack' ),
						),
					),
					'social_button_layout' => array(
						'type'	=> 'pp-switch',
						'label'	=> __( 'Layout', 'bb-powerpack' ),
						'default' => 'inline',
						'options' => array(
							'inline'	=> __( 'Inline', 'bb-powerpack' ),
							'stacked'	=> __( 'Stacked', 'bb-powerpack' ),
						),
					),
					'social_button_alignment'	=> array(
						'type'	=> 'align',
						'label'	=> __( 'Alignment', 'bb-powerpack' ),
						'default' => 'center',
					),
					'social_button_type' => array(
						'type'	=> 'select',
						'label'	=> __( 'Button Type', 'bb-powerpack' ),
						'default' => 'solid',
						'options' => array(
							'solid'	=> __( 'Solid', 'bb-powerpack' ),
							'transparent' => __( 'Transparent', 'bb-powerpack' ),
							'custom' => __( 'Custom', 'bb-powerpack' ),
						),
						'toggle'	=> array(
							'custom'	=> array(
								'sections'	=> array( 'social_buttons_style_fb', 'social_buttons_style_google' ),
							),
						),
					),
					'social_button_width' => array(
						'type'	=> 'unit',
						'label'	=> __( 'Button Width', 'bb-powerpack' ),
						'default' => '',
						'units' => array( 'px', '%' ),
						'slider' => true,
						'responsive' => true,
						'preview' => array(
							'type'		=> 'css',
							'selector' 	=> '.pp-login-form .pp-social-login-button',
							'property'	=> 'width',
						),
					),
					'social_button_spacing' => array(
						'type'	=> 'unit',
						'label'	=> __( 'Button Spacing', 'bb-powerpack' ),
						'default' => '',
						'units' => array( 'px' ),
						'slider' => true,
						'responsive' => true,
						'preview'	=> array(
							'type'		=> 'css',
							'rules'		=> array(
								array(
									'selector'	=> '.pp-login-form .pp-social-login-wrap.pp-social-login--layout-inline .pp-social-login-button',
									'property'	=> 'margin-right',
									'unit'		=> 'px',
								),
								array(
									'selector'	=> '.pp-login-form .pp-social-login-wrap.pp-social-login--layout-stacked .pp-social-login-button',
									'property'	=> 'margin-bottom',
									'unit'		=> 'px',
								),
							),
						),
					),
					'separator_width'	=> array(
						'type'	=> 'unit',
						'label'	=> __( 'Separator Width', 'bb-powerpack' ),
						'default' => '',
						'units' => array( 'px' ),
						'slider' => true,
					),
					'separator_color'	=> array(
						'type'	=> 'color',
						'label'	=> __( 'Separator Color', 'bb-powerpack' ),
						'default' => '',
						'show_reset' => true,
						'connections' => array( 'color' ),
					),
					'separator_text_color'	=> array(
						'type'	=> 'color',
						'label'	=> __( 'Separator Text Color', 'bb-powerpack' ),
						'default' => '',
						'show_reset' => true,
						'connections' => array( 'color' ),
						'preview'	=> array(
							'type'	=> 'css',
							'selector' => '.pp-login-form .pp-login-form-sep-text',
							'property' => 'color',
						),
					),
					'separator_spacing'	=> array(
						'type'		=> 'dimension',
						'label'		=> __( 'Separator Spacing', 'bb-powerpack' ),
						'default' 	=> '',
						'slider'	=> true,
						'units'		=> array( 'px' ),
						'preview'	=> array(
							'type'	=> 'css',
							'selector' => '.pp-login-form .pp-login-form-sep',
							'property' => 'margin',
							'unit'	=> 'px',
						),
					),
				),
			),
			'social_buttons_style_fb'	=> array(
				'title'		=> __( 'Social Login - Facebook', 'bb-powerpack' ),
				'collapsed' => true,
				'fields'	=> array(
					'social_button_fb_bg_color'	=> array(
						'type'	=> 'color',
						'label'	=> __( 'Background Color', 'bb-powerpack' ),
						'default' => '',
						'show_reset' => true,
						'show_alpha' => true,
						'connections' => array( 'color' ),
						'preview'	=> array(
							'type'		=> 'css',
							'selector'	=> '.pp-login-form .pp-social-login-button.pp-fb-login-button',
							'property'	=> 'background-color',
						),
					),
					'social_button_fb_hover_bg_color'	=> array(
						'type'	=> 'color',
						'label'	=> __( 'Background Hover Color', 'bb-powerpack' ),
						'default' => '',
						'show_reset' => true,
						'show_alpha' => true,
						'connections' => array( 'color' ),
						'preview'	=> array(
							'type'		=> 'none',
						),
					),
					'social_button_fb_color'	=> array(
						'type'	=> 'color',
						'label'	=> __( 'Color', 'bb-powerpack' ),
						'default' => '',
						'show_reset' => true,
						'connections' => array( 'color' ),
						'preview'	=> array(
							'type'		=> 'css',
							'selector'	=> '.pp-login-form .pp-social-login-button.pp-fb-login-button',
							'property'	=> 'color',
						),
					),
					'social_button_fb_hover_color'	=> array(
						'type'	=> 'color',
						'label'	=> __( 'Hover Color', 'bb-powerpack' ),
						'default' => '',
						'show_reset' => true,
						'connections' => array( 'color' ),
						'preview'	=> array(
							'type'		=> 'none',
						),
					),
					'social_button_fb_border'	=> array(
						'type'	=> 'border',
						'label'	=> __( 'Border', 'bb-powerpack' ),
						'preview'	=> array(
							'type'		=> 'css',
							'selector'	=> '.pp-login-form .pp-social-login-button.pp-fb-login-button',
						),
					),
					'social_button_fb_border_hover_color'	=> array(
						'type'	=> 'color',
						'label'	=> __( 'Border Hover Color', 'bb-powerpack' ),
						'default' => '',
						'show_reset' => true,
						'connections' => array( 'color' ),
						'preview'	=> array(
							'type'		=> 'none',
						),
					),
				),
			),
			'social_buttons_style_google'	=> array(
				'title'		=> __( 'Social Login - Google', 'bb-powerpack' ),
				'collapsed' => true,
				'fields'	=> array(
					'social_button_google_bg_color'	=> array(
						'type'	=> 'color',
						'label'	=> __( 'Background Color', 'bb-powerpack' ),
						'default' => '',
						'show_reset' => true,
						'show_alpha' => true,
						'connections' => array( 'color' ),
						'preview'	=> array(
							'type'		=> 'css',
							'selector'	=> '.pp-login-form .pp-social-login-button.pp-google-login-button',
							'property'	=> 'background-color',
						),
					),
					'social_button_google_hover_bg_color'	=> array(
						'type'	=> 'color',
						'label'	=> __( 'Background Hover Color', 'bb-powerpack' ),
						'default' => '',
						'show_reset' => true,
						'show_alpha' => true,
						'connections' => array( 'color' ),
						'preview'	=> array(
							'type'		=> 'none',
						),
					),
					'social_button_google_color'	=> array(
						'type'	=> 'color',
						'label'	=> __( 'Color', 'bb-powerpack' ),
						'default' => '',
						'show_reset' => true,
						'connections' => array( 'color' ),
						'preview'	=> array(
							'type'		=> 'css',
							'selector'	=> '.pp-login-form .pp-social-login-button.pp-google-login-button',
							'property'	=> 'color',
						),
					),
					'social_button_google_hover_color'	=> array(
						'type'	=> 'color',
						'label'	=> __( 'Hover Color', 'bb-powerpack' ),
						'default' => '',
						'show_reset' => true,
						'connections' => array( 'color' ),
						'preview'	=> array(
							'type'		=> 'none',
						),
					),
					'social_button_google_border'	=> array(
						'type'	=> 'border',
						'label'	=> __( 'Border', 'bb-powerpack' ),
						'preview'	=> array(
							'type'		=> 'css',
							'selector'	=> '.pp-login-form .pp-social-login-button.pp-google-login-button',
						),
					),
					'social_button_google_border_hover_color'	=> array(
						'type'	=> 'color',
						'label'	=> __( 'Border Hover Color', 'bb-powerpack' ),
						'default' => '',
						'show_reset' => true,
						'connections' => array( 'color' ),
						'preview'	=> array(
							'type'		=> 'none',
						),
					),
				),
			),
		)
	),
	'typography'	=> array(
		'title'			=> __('Typography', 'bb-powerpack'),
		'sections'		=> array(
			'label_typography'	=> array(
				'title'				=> __('Label', 'bb-powerpack'),
				'fields'			=> array(
					'label_typography'	=> array(
						'type'				=> 'typography',
						'label'				=> __('Typography', 'bb-powerpack'),
						'responsive'		=> true,
						'preview'			=> array(
							'type'				=> 'css',
							'selector'			=> '.pp-field-group > label',
						)
					),
				)
			),
			'fields_typography'	=> array(
				'title'				=> __('Fields', 'bb-powerpack'),
				'collapsed'			=> true,
				'fields'			=> array(
					'fields_typography'	=> array(
						'type'				=> 'typography',
						'label'				=> __('Typography', 'bb-powerpack'),
						'responsive'		=> true,
						'preview'			=> array(
							'type'				=> 'css',
							'selector'			=> '.pp-field-group .pp-login-form--input',
						)
					),
				)
			),
			'button_typography'	=> array(
				'title'				=> __('Button', 'bb-powerpack'),
				'collapsed'			=> true,
				'fields'			=> array(
					'button_typography'	=> array(
						'type'				=> 'typography',
						'label'				=> __('Typography', 'bb-powerpack'),
						'responsive'		=> true,
						'preview'			=> array(
							'type'				=> 'css',
							'selector'			=> '.pp-field-group .pp-login-form--button',
						)
					),
				)
			),
			'social_buttons_typography'	=> array(
				'title'				=> __('Social Login', 'bb-powerpack'),
				'collapsed'			=> true,
				'fields'			=> array(
					'social_button_typography'	=> array(
						'type'				=> 'typography',
						'label'				=> __('Social Buttons Typography', 'bb-powerpack'),
						'responsive'		=> true,
						'preview'			=> array(
							'type'				=> 'css',
							'selector'			=> '.pp-login-form .pp-social-login-button .pp-social-login-label',
						)
					),
					'separator_text_typography'	=> array(
						'type'				=> 'typography',
						'label'				=> __('Separator Text Typography', 'bb-powerpack'),
						'responsive'		=> true,
						'preview'			=> array(
							'type'				=> 'css',
							'selector'			=> '.pp-login-form .pp-login-form-sep-text',
						)
					),
				)
			),
		)
	)
) );