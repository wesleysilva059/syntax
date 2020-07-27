<?php
$action 				= isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';
$current_url 			= remove_query_arg( 'fake_arg' );
$redirect_url 			= $current_url;
$logout_redirect_url 	= $current_url;
$show_label				= 'yes' == $settings->show_labels;
$show_lost_password		= 'yes' == $settings->show_lost_password;
$show_register 			= 'yes' == $settings->show_register && get_option( 'users_can_register' );
$is_lost_password		= 'lost_pass' === $action || isset( $_GET['lost_pass'] );
$is_reset_password		= 'reset_pass' === $action || isset( $_GET['reset_pass'] );
$is_logged_in			= is_user_logged_in();
$is_builder_active		= FLBuilderModel::is_builder_active();
$reauth 				= false;

/**
 * Fires before a specified login form action.
 *
 * The dynamic portion of the hook name, `$action`, refers to the action
 * that brought the visitor to the login form. Actions include 'lost_pass',
 * 'reset_pass', etc.
 *
 * @since 2.8.1
 */
do_action( "login_form_{$action}" );

if ( 'yes' == $settings->redirect_after_login && ! empty( $settings->redirect_url ) ) {
	$redirect_url = $settings->redirect_url;
}
// Update redirect URL if session has expired in WP admin.
if ( isset( $_GET['redirect_to'] ) && isset( $_GET['reauth'] ) ) {
	if ( ! empty( $_GET['redirect_to'] ) && $_GET['reauth'] ) {
		// Clear any stale cookies.
		wp_clear_auth_cookie();
		// Get redirect URL.
		$redirect_url = $_GET['redirect_to'];
		$reauth = true;
	}
}
if ( 'yes' == $settings->redirect_after_logout && ! empty( $settings->redirect_logout_url ) ) {
	$logout_redirect_url = $settings->redirect_logout_url;
}
if ( ! isset( $_GET['key'] ) || empty( $_GET['key'] ) ) {
	$is_reset_password = false;
}
if ( ! isset( $_GET['id'] ) || empty( $_GET['id'] ) ) {
	$is_reset_password = false;
}
?>
<div class="pp-login-form-wrap">
	<?php if ( $is_logged_in && ! $is_builder_active && ! $reauth ) {
		if ( 'yes' == $settings->show_logged_in_message ) { $current_user = wp_get_current_user(); ?>
			<div class="pp-login-message">
				<?php
				// translators: Here %1$s is for current user's display name and %2$s is for logout URL.
				$msg = sprintf( __( 'You are Logged in as %1$s (<a href="%2$s">Logout</a>)', 'bb-powerpack' ), $current_user->display_name, wp_logout_url( $logout_redirect_url ) );
				echo apply_filters( 'pp_login_form_logged_in_message', $msg, $current_user->display_name, wp_logout_url( $logout_redirect_url ) );
				?>
			</div>
		<?php }
	} ?>

	<?php if ( ! $is_logged_in || $is_builder_active || $reauth ) { ?>
		<?php if ( ! $is_lost_password && ! $is_reset_password ) { ?>
		<form class="pp-login-form" id="pp-form-<?php echo $id; ?>" method="post" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>">
			<?php wp_nonce_field( 'login_nonce', 'pp-lf-login-nonce' ); ?>
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_url ); ?>">
			<?php if ( $reauth ) { ?>
				<input type="hidden" name="reauth" value="1" />
			<?php } ?>
			<div class="pp-login-form-inner">
				<div class="pp-login-form-fields">
					<div class="pp-login-form-field pp-field-group pp-field-type-text">
						<?php if ( $show_label ) { ?>
						<label for="user"><?php echo $settings->username_label; ?></label>
						<?php } ?>
						<input size="1" type="text" name="log" id="user" placeholder="<?php echo $settings->username_placeholder; ?>" class="pp-login-form--input" />
					</div>

					<div class="pp-login-form-field pp-field-group pp-field-type-text">
						<?php if ( $show_label ) { ?>
						<label for="password"><?php echo $settings->password_label; ?></label>
						<?php } ?>
						<input size="1" type="password" name="pwd" id="password" placeholder="<?php echo $settings->password_placeholder; ?>" class="pp-login-form--input" />
					</div>

					<?php if ( 'yes' == $settings->show_remember_me ) { ?>
					<div class="pp-login-form-field pp-field-group pp-field-type-checkbox">
						<label for="pp-login-remember-me">
							<input type="checkbox" name="rememberme" id="pp-login-remember-me" class="pp-login-form--checkbox" />
							<span class="pp-login-remember-me"><?php echo ! empty( $settings->remember_me_text ) ? $settings->remember_me_text : __( 'Remember Me', 'bb-powerpack' ); ?></span>
						</label>
					</div>
					<?php } ?>

					<?php
					// Render reCAPTCHA field.
					if ( 'yes' === $settings->enable_recaptcha ) {
						$module->render_recaptcha_field( $id );
					}
					?>

					<div class="pp-field-group pp-field-type-submit">
						<button type="submit" name="wp-submit" class="pp-login-form--button pp-submit-button">
							<span class="pp-login-form--button-text"><?php echo $settings->button_text; ?></span>
						</button>
					</div>

					<?php if ( $show_lost_password || $show_register ) { ?>
					<div class="pp-field-group pp-field-type-link">
						<?php if ( $show_lost_password ) { ?>
							<a class="pp-login-lost-password" href="<?php echo add_query_arg( 'lost_pass', '1' ); ?>">
								<?php echo ! empty( $settings->lost_password_text ) ? $settings->lost_password_text : __( 'Lost your password?', 'bb-powerpack' ); ?>
							</a>
						<?php } ?>
						<?php if ( $show_register ) { ?>
							<?php if ( $show_lost_password ) { ?>
								<span class="pp-login-separator"> | </span>
							<?php } ?>
							<a class="pp-login-register" href="<?php echo $module->get_registration_url(); ?>">
								<?php _e( 'Register', 'bb-powerpack' ); ?>
							</a>
						<?php } ?>
					</div>
					<?php } ?>
				</div><!-- /.pp-login-form-fields -->
				<?php if ( $module->has_social_login() ) {
					include $module->dir . 'includes/social-login.php';
				} ?>
			</div>
		</form>
		<?php
		} elseif ( $is_lost_password && file_exists( $module->dir . 'includes/form-lost-pass.php' ) ) {
			include $module->dir . 'includes/form-lost-pass.php';
		} elseif ( $is_reset_password && file_exists( $module->dir . 'includes/form-reset-pass.php' ) ) {
			include $module->dir . 'includes/form-reset-pass.php';
		}
		?>
	<?php } ?>
</div>