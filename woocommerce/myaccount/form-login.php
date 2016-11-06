<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php wc_print_notices(); ?>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

<div class="u-columns col2-set" id="customer_login">

	<div class="u-column1 col-1">

<?php endif; ?>

		<h2><?php _e( 'Login', 'woocommerce' ); ?></h2>

		<form method="post" class="login">

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
				<label for="username"><?php _e( 'Username or email address', 'woocommerce' ); ?> <span class="required">*</span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
			</p>
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
				<label for="password"><?php _e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" />
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<p class="form-row">
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<input type="submit" class="woocommerce-Button button" name="login" value="<?php esc_attr_e( 'Login', 'woocommerce' ); ?>" />
				<label for="rememberme" class="inline">
					<input class="woocommerce-Input woocommerce-Input--checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember me', 'woocommerce' ); ?>
				</label>
			</p>
			<p class="woocommerce-LostPassword lost_password">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php _e( 'Lost your password?', 'woocommerce' ); ?></a>
			</p>

			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>

<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

	</div>

	<div class="u-column2 col-2">

		<h2><?php _e( 'Register', 'woocommerce' ); ?></h2>

		<form method="post" class="register">

			<?php do_action( 'woocommerce_register_form_start' ); ?>

		    <p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
			<label for="reg_email"><?php _e( 'Email address', 'woocommerce' ); ?> <span class="required">*</span></label>
			<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" value="<?php if ( ! empty( $_POST['email'] ) ) echo esc_attr( $_POST['email'] ); ?>" />
		    </p>

		    <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
					<label for="reg_username"><?php _e( 'Username', 'woocommerce' ); ?> <span class="required">*</span></label>
					<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
				</p>

			<?php endif; ?>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
					<label for="reg_password"><?php _e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" />
				</p>

			<?php endif; ?>

<!-- HC added form fields start here -->

			<!-- First name -->
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
			    <label for="reg_first_name"><?php _e( 'First Name', 'woocommerce' ); ?> <span class="required">*</span></label>
			    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_first_name" id="reg_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) echo esc_attr( $_POST['billing_first_name'] ); ?>" />
			</p>

			<!-- Last name -->
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
			    <label for="reg_last_name"><?php _e( 'Last Name', 'woocommerce' ); ?> <span class="required">*</span></label>
			    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_last_name" id="reg_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) echo esc_attr( $_POST['billing_last_name'] ); ?>" />
			</p>
			
			<!-- Company -->
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
			    <label for="reg_company"><?php _e( 'Company', 'woocommerce' ); ?></label>
			    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_company" id="reg_company" value="<?php if ( ! empty( $_POST['billing_company'] ) ) echo esc_attr( $_POST['billing_company'] ); ?>" />
			</p>

			<!-- Country -->
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
			    <?php
			    woocommerce_form_field('billing_country', array(
			    'type'       => 'country',
			    'label'      => __('Country'),
			    'required'   => true,
			    'placeholder'    => __('Choose a Country')
			    )
			    ); ?>
			</p>

			<!-- Address_1 -->
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
			    <label for="reg_address_1"><?php _e( 'Address', 'woocommerce' ); ?> <span class="required">*</span></label>
			    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_address_1" id="reg_address_1" value="<?php if ( ! empty( $_POST['billing_address_1'] ) ) echo esc_attr( $_POST['billing_address_1'] ); ?>" />
			</p>

			<!-- address_2 -->
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
			    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_address_2" id="reg_address_2" value="<?php if ( ! empty( $_POST['billing_address_2'] ) ) echo esc_attr( $_POST['billing_address_2'] ); ?>" />
			</p>

			<!-- city -->
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
			    <label for="reg_city"><?php _e( 'City', 'woocommerce' ); ?> <span class="required">*</span></label>
			    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_city" id="reg_city" value="<?php if ( ! empty( $_POST['billing_city'] ) ) echo esc_attr( $_POST['billing_city'] ); ?>" />
			</p>

			<!-- State -->
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">


			    <?php
			    woocommerce_form_field('billing_state', array(
				'type'       => 'state',
				'label'      => __('State -- leave blank outside US'),
				'placeholder'    => __('Choose a State')
			    )
			    ); ?>
			</p>


			<!-- postcode -->
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
			    <label for="reg_postcode"><?php _e( 'Zip/Postcode', 'woocommerce' ); ?> <span class="required">*</span></label>
			    <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_postcode" id="reg_postcode" value="<?php if ( ! empty( $_POST['billing_postcode'] ) ) echo esc_attr( $_POST['billing_postcode'] ); ?>" />
			</p>
			
<!-- HC added form fields end here -->			
					
			<!-- Spam Trap -->
			<div style="<?php echo ( ( is_rtl() ) ? 'right' : 'left' ); ?>: -999em; position: absolute;"><label for="trap"><?php _e( 'Anti-spam', 'woocommerce' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" autocomplete="off" /></div>

			<?php do_action( 'woocommerce_register_form' ); ?>
			<?php do_action( 'register_form' ); ?>

			<p class="woocomerce-FormRow form-row">
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<input type="submit" class="woocommerce-Button button" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>" />
			</p>

			<?php do_action( 'woocommerce_register_form_end' ); ?>

		</form>

	</div>

</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
