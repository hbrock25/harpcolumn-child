<?php

function pmpro_shortcode_protection_text($atts, $content=null, $code="")
{
    // $atts    ::= array of attributes
    // $content ::= text within enclosing form of shortcode element
    // $code    ::= the shortcode found, when == callback name
    // examples: [membership level="3"]...[/membership]

    global $current_user;

    //use globals if no values supplied
    if(!$user_id)
	$user_id = $current_user->ID;

    $pmpro_content_message_pre = '<div class="pmpro_content_message">';
    $pmpro_content_message_post = '</div>';

    //get the correct message to show at the bottom
    if($current_user->ID)
    {
	//not a member
	$newcontent = apply_filters("pmpro_non_member_text_filter", stripslashes(pmpro_getOption("nonmembertext")));
	$content = $pmpro_content_message_pre . $newcontent . $pmpro_content_message_post;
    }
    else
    {
	//not logged in!
	$newcontent = apply_filters("pmpro_not_logged_in_text_filter", stripslashes(pmpro_getOption("notloggedintext")));
	$content = $pmpro_content_message_pre . $newcontent . $pmpro_content_message_post;
    }

    return do_shortcode($content);

}
add_shortcode("protection_text", "pmpro_shortcode_protection_text");

function dae_pmpro_email_recipient($recipient, $email)
{
    //if($email->template == "invoice")			//use this to check for a certain template
    $recipient = NULL;	
    return $recipient;
}
// add_filter("pmpro_email_recipient", "dae_pmpro_email_recipient", 10, 2);

// For buddyblog form -- don't let users set their own categories.

function buddyblog_my_post_form_settings($settings)
{

    // edit existing settings array rather than make a whole new one
    unset($settings['tax']);
    // $settings['upload_count'] = 10;
    return $settings;
    
}

add_filter("buddyblog_post_form_settings", "buddyblog_my_post_form_settings");

// Shortcode to pull up buddypress profile link

function display_profile_link_bbpress_func() {
    
    //Get user ID
    $user_ID = get_current_user_id();
    
    //Get the profile domain in BBpress
    $profile_domain= bp_core_get_user_domain($user_ID );
    
    //Get profile link as requested
    $profile_link=$profile_domain.'profile';
    
    //make it a hyperlink
    $profile_link_hyperlink="<a href='$profile_link'>profile</a>";
    
    //return the shortcode value
    return $profile_link_hyperlink;
}

add_shortcode('display_profile_link_bbpress','display_profile_link_bbpress_func');

// default country to US

function hc_pmpro_default_country($default)
{	
    return "US";
}
add_filter("pmpro_default_country", "hc_pmpro_default_country");

// Fix bug in my import -- US is "USA"

function hc_pmpro_countries($countries)
{
    $countries["USA"] = "United States";

    return $countries;
}
add_filter("pmpro_countries", "hc_pmpro_countries");


add_action('frm_validate_entry', 'validate_my_form', 20, 2);
function validate_my_form($errors, $values){
    if( ($values['form_id'] == 4) || ($values['form_id'] == 5) || ($values['form_id'] == 6)) {
	if ( ! is_user_logged_in() ) {
	    $errors['my_error'] = 'You must be logged in to submit this form.';
	    return $errors;
	}
    }
}


function my_tribe_event_featured_image($featured_image, $post_id, $size) {
    if ($size == 'thumbnail') {
	$image_src = get_the_post_thumbnail( $post_id, $size );
	if (! empty($image_src[0])) {
	    return '<div class="tribe-events-event-image hc_post_thumbnail"><a href="' . tribe_get_event_link() . '" title="' . get_the_title( $post_id ) . '"><img src="' . $image_src[0] . '" title="' . get_the_title( $post_id ) . '" width="75" height="75" /></a></div>';
	}
    }
    return $featured_image;
}

add_filter('tribe_event_featured_image', 'my_tribe_event_featured_image',10, 3);

function my_tribe_get_events_title($title) {
    return "Harp Concert Calendar";
}

add_filter('tribe_get_events_title', 'my_tribe_get_events_title');

function tml_action_url( $url, $action, $instance ) {
    if ( 'register' == $action )
	$url = '/membership-account/subscribe/';
    return $url;
}

add_filter( 'tml_action_url', 'tml_action_url', 10, 3 );

/* Always display the "register" link even though we don't 
 * allow native wordpress user registration
 */

function hc_add_back_register_link( $action_links, $args ) {
    $action_links[] = array(
	'title' => 'Register',
	'url'   => '/membership-account/subscribe/'
    );
    return $action_links;
}

add_filter( 'tml_action_links', 'hc_add_back_register_link', 10, 2);

add_filter('bbp_get_do_not_reply_address','my_bbp_no_reply_email');
function no_reply_email(){
    $email = 'noreply@harpcolumn.com';
    return $email;
}

add_filter('bbp_subscription_to_email','my_bbp_subscription_to_email');
function my_bbp_subscription_to_email(){
    $email = 'noreply@harpcolumn.com'; // any email you want
    return $email;
}

/**
 * Hide admin bar from certain user roles
 */
function hide_admin_bar( $show ) {
    if ( current_user_can( 'editor' ) ||
	 current_user_can( 'administrator' ) ||
	 current_user_can( 'ai1ec_event_assistant' ) ) {
	$show = true;
    } else {
	$show = false;
    }

    return $show;
}
add_filter( 'show_admin_bar', 'hide_admin_bar' );

/**
 * This is the validation and storage code for the extra billing address fields
 * we retrieve on registration. This code will break registration if the login
 * template does not submit the extra billing fields (I think).
 */

/**
 * Validate the extra register fields.
 *
 * @param string $username Current username.
 * @param string $email Current email.
 * @param object $validation_errorsWP_Error object.
 *
 * @return void

 */

function woo_hc_validate_extra_register_fields( $username, $email, $validation_errors ) {

    if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {

	$validation_errors->add( 'billing_first_name_error', __( '<strong>Error</strong>: First name is required', 'woocommerce' ) );

    }

    if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {

	$validation_errors->add( 'billing_last_name_error', __( '<strong>Error</strong>: Last name is required', 'woocommerce' ) );
    
    }

    if ( isset( $_POST['billing_country'] ) && empty( $_POST['billing_country'] ) ) {

	$validation_errors->add( 'billing_country_error', __( '<strong>Error</strong>: Please choose a country', 'woocommerce' ) );

    }

    if ( isset( $_POST['billing_address_1'] ) && empty( $_POST['billing_address_1'] ) ) {

	$validation_errors->add( 'billing_address_1_error', __( '<strong>Error</strong>: Please enter a street address', 'woocommerce' ) );

    }

    if ( isset( $_POST['billing_city'] ) && empty( $_POST['billing_city'] ) ) {

	$validation_errors->add( 'billing_city_error', __( '<strong>Error</strong>: Please choose a city', 'woocommerce' ) );

    }

    if ( isset( $_POST['billing_postcode'] ) && empty( $_POST['billing_postcode'] ) ) {

	$validation_errors->add( 'billing_postcode_error', __( '<strong>Error</strong>: Please enter a postal code', 'woocommerce' ) );

    }
}

add_action( 'woocommerce_register_post', 'woo_hc_validate_extra_register_fields', 10, 3 );


/**
 * Save the extra register fields.
 *
 * @paramint $customer_id Current customer ID.
 *
 * @return void
 */
function woo_hc_save_extra_register_fields( $customer_id ) {
    if ( isset( $_POST['billing_first_name'] ) ) {
	// WordPress default first name field.
	update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
	// WooCommerce billing first name.
	update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
    }
    if ( isset( $_POST['billing_last_name'] ) ) {
	// WordPress default last name field.
	update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
	// WooCommerce billing last name.
	update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
    }

    // Company
    if ( isset( $_POST['billing_company'] ) ) {
	// WooCommerce billing phone
	update_user_meta( $customer_id, 'billing_company', sanitize_text_field( $_POST['billing_company'] ) );
    }

    // Country
    if ( isset( $_POST['billing_country'] ) ) {
	// WooCommerce billing phone
	update_user_meta( $customer_id, 'billing_country', sanitize_text_field( $_POST['billing_country'] ) );
    }

    // Address_1
    if ( isset( $_POST['billing_address_1'] ) ) {
	// WooCommerce billing address_1
	update_user_meta( $customer_id, 'billing_address_1', sanitize_text_field( $_POST['billing_address_1'] ) );
    }

    // Address_2
    if ( isset( $_POST['billing_address_2'] ) ) {
	// WooCommerce billing address_2
	update_user_meta( $customer_id, 'billing_address_2', sanitize_text_field( $_POST['billing_address_2'] ) );
    }

    // City
    if ( isset( $_POST['billing_city'] ) ) {
	// WooCommerce billing city
	update_user_meta( $customer_id, 'billing_city', sanitize_text_field( $_POST['billing_city'] ) );
    }

    // State/County
    if ( isset( $_POST['billing_state'] ) ) {
	// WooCommerce billing state
	update_user_meta( $customer_id, 'billing_state', sanitize_text_field( $_POST['billing_state'] ) );
    }

    // Postcode
    if ( isset( $_POST['billing_postcode'] ) ) {
	// WooCommerce billing postcode
	update_user_meta( $customer_id, 'billing_postcode', sanitize_text_field( $_POST['billing_postcode'] ) );
    }

}
add_action( 'woocommerce_created_customer', 'woo_hc_save_extra_register_fields' );

