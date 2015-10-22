<!-- This form belongs in /usr/share/nginx/harpcolumn/wp-content/plugins/another-wordpress-classifieds-plugin/frontend/templates -->

<?php if ( awpcp_request_param( 'register', false ) ): ?>
	<?php echo awpcp_print_message( __( 'Please check your email for the password and then return to log in.', 'AWPCP' ) ); ?>
<?php elseif ( awpcp_request_param( 'reset', false ) ): ?>
	<?php echo awpcp_print_message( __( 'Please check your email to reset your password.', 'AWPCP' ) ); ?>
<?php elseif ( $message ): ?>
	<?php echo awpcp_print_message( $message ); ?>
<?php endif; ?>

<div class="awpcp-login-form">
	<?php wp_login_form( array( 'redirect' => $redirect ) ); ?>

	<p id="nav" class="nav">
	<a href="<?php echo esc_url( '/membership-account/subscribe/' ); ?>"><?php _e( 'Register', 'AWPCP' ); ?></a> |
	<a href="<?php echo esc_url( $lost_password_url ); ?>" title="<?php esc_attr_e( 'Password Lost and Found', 'AWPCP' ); ?>"><?php echo esc_html( __( 'Lost your password?', 'AWPCP' ) ); ?></a>
	</p>
</div>
