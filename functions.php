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

function my_bbp_no_reply_email(){
    $email = 'noreply@harpcolumn.com';
    return $email;
}
add_filter('bbp_get_do_not_reply_address','my_bbp_no_reply_email');

function my_bbp_subscription_to_email(){
    $email = 'noreply@harpcolumn.com'; // any email you want
    return $email;
}
add_filter('bbp_subscription_to_email','my_bbp_subscription_to_email');

/**
 * Hide admin bar from certain user roles
 */
function hide_admin_bar( $show ) {
    if ( current_user_can( 'editor' ) ||
	 current_user_can( 'administrator' ) ||
	 current_user_can( 'author' ) ) {
	$show = true;
    } else {
	$show = false;
    }

    return $show;
}
add_filter( 'show_admin_bar', 'hide_admin_bar' );

// remove the prime-mentions-results action since it makes the site really slow
function hc_bp_friends_remove_action() {
    remove_action('bp_activity_mentions_prime_results', 'bp_friends_prime_mentions_results' );
}
add_action( 'bp_activity_mentions_prime_results', 'hc_bp_friends_remove_action', 9 );

function hc_pmpro_getfile_403() {
    header( "HTTP/1.1 403 Restricted Content", true, 403 );

    echo '
    <html>
    <head>
    <title>Access Denied</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="author" content="Harp Column" />
    <meta name="description" content="Harp Column - Practical News for Practical Harpists" />
    <meta name="keywords" content="Access Denied" />
    <meta name="robots" content="index, follow" />
    <style>.wrap { width: 605px; margin: 100px auto 0; text-align: center;} h1 { color: #cd0076; } img { padding-bottom: 20px; }</style>
    </head>
      <body style="background-color: #fff; color: #1b1f23; font-family: sans-serif;">
	<div class="wrap">
        <center>
        <img src="/wp-content/uploads/2016/12/Harp-Column-main-logo.png" />
        </center>
        <br />
        <h1>We\'re sorry</h1>
	<h2><p>Only Harp Column subscribers can download the Harp
	Column issue at this location, and you do not appear to be
        a Harp Column subscriber.</p>
	<p>To subscribe to Harp Column, <a href="/subscribe">click here</a>,
        or if you already have a
	    subscription, <a href="/my-account">log in</a>.</p>
	</h2>
	</div>
      </body>
    </html>';
    exit;
    }

add_action("pmpro_getfile_before_error", "hc_pmpro_getfile_403");

/**
 * Don't ask users if we should create an account for them when they create
 * an ad -- for WPAdverts plugin
 */
	
add_filter( "adverts_form_load", "adverts_remove_account_field", 100 );

// Require login for people filling in contact forms.
add_action( "init", "my_contact_form_init", 1000 );
function my_contact_form_init() {
    if( ! is_user_logged_in() ) {
        remove_all_actions( "adverts_tpl_single_bottom" );
	add_action( "adverts_tpl_single_bottom", "adverts_anon_message" );
    }
}
    
function adverts_anon_message() {
    echo 'Please <a href="/my-account">login</a> to contact the seller.';
}
	
/**
 * Add user_login column to wc customer order export
 *
 */

// add custom column headers
function wc_csv_export_modify_column_headers( $column_headers ) { 
    
    $new_headers = array(
	'user_login' => 'User Login'
    );
    
    return array_merge( $column_headers, $new_headers );
}
add_filter( 'wc_customer_order_csv_export_order_headers', 'wc_csv_export_modify_column_headers' );

// set the data for each for custom columns
function wc_csv_export_modify_row_data( $order_data, $order, $csv_generator ) {
    
    $custom_data = array(
	'user_login' => $order->get_user() ? $order->get_user()->user_login : "No Login"
    );
    
    $new_order_data   = array();
    $one_row_per_item = 'item' === $csv_generator->format_definition['row_type'];
    if ( $one_row_per_item ) {
	foreach ( $order_data as $data ) {
	    $new_order_data[] = array_merge( (array) $data, $custom_data );
	}
    } else {
	$new_order_data = array_merge( $order_data, $custom_data );
    }
    return $new_order_data;
}
add_filter( 'wc_customer_order_csv_export_order_row', 'wc_csv_export_modify_row_data', 10, 3 );

/**
 * Remove related products output
 */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

/*
	apply end date extension filter to woo commerce checkouts as well
	
	$level_array is a custom_level array for the pmpro_changeMembershipLevel call
	$level_obj in the function is an object with the stored values for the level

	NB this is an exact copy of the existing extend_memberships filter from the pmprowoo
	plugin, with the check for "is the user extending the same level" removed.

*/
function pmprowoo_harpcol_checkout_level_extend_memberships($level_array)
{
	$level_obj = pmpro_getLevel($level_array['membership_id']);
	
	//does this level expire? 
	if(!empty($level_obj) && !empty($level_obj->expiration_number) )
	{
		//get the current enddate of their membership
		$user = get_userdata($level_array['user_id']);
		$user->membership_level = pmpro_getMembershipLevelForUser($user->ID);
		$expiration_date = $user->membership_level->enddate;
		
		//calculate days left
		$todays_date = current_time('timestamp');
		$time_left = $expiration_date - $todays_date;
		
		//time left?
		if($time_left > 0)
		{
			//convert to days and add to the expiration date (assumes expiration was 1 year)
			$days_left = floor($time_left/(60*60*24));
			
			//figure out days based on period
			if($level_obj->expiration_period == "Day")
				$total_days = $days_left + $level_obj->expiration_number;
			elseif($level_obj->expiration_period == "Week")
				$total_days = $days_left + $level_obj->expiration_number * 7;
			elseif($level_obj->expiration_period == "Month")
				$total_days = $days_left + $level_obj->expiration_number * 30;
			elseif($level_obj->expiration_period == "Year")
				$total_days = $days_left + $level_obj->expiration_number * 365;
			
			//update the end date
			$level_array['enddate'] = date("Y-m-d", strtotime("+ $total_days Days", $todays_date));
		}
	}
		
	return $level_array;
}
add_filter('pmprowoo_checkout_level', 'pmprowoo_harpcol_checkout_level_extend_memberships');

/**
 * Remove related products output
 */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
