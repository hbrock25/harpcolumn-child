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

/*
Customize the pmpro membership template
*/

//use custom levels template
function pmprodiv_pmpro_pages_shortcode_levels($content)
{
	ob_start();
	include(get_stylesheet_directory() . '/templates/levels.php' );
	$temp_content = ob_get_contents();
	ob_end_clean();
	return $temp_content;
}
add_filter("pmpro_pages_shortcode_levels", "pmprodiv_pmpro_pages_shortcode_levels");

/*
	When users cancel (are changed to membership level 0) we give them another "cancelled" level. Can be used to downgrade someone to a free level when they cancel.
*/
function my_pmpro_after_change_membership_level($level_id, $user_id)
{
	if($level_id == 0)
	{
		//cancelling, give them level 1 instead
		pmpro_changeMembershipLevel(1, $user_id);
	}
}
add_action("pmpro_after_change_membership_level", "my_pmpro_after_change_membership_level", 10, 2);

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

// un-require CVV, phone

function hc_pmpro_required_billing_fields($fields)
{
	//remove state and zip
	unset($fields['bphone']);
	unset($fields['bcountry']);
	unset($fields['CVV']);

	return $fields;
}
add_filter("pmpro_required_billing_fields", "hc_pmpro_required_billing_fields");

// Fix bug in my import -- US is "USA"

function hc_pmpro_countries($countries)
{
	$countries["USA"] = "United States";

	return $countries;
}
add_filter("pmpro_countries", "hc_pmpro_countries");

// add renew link to bottom of membership account page

function hc_pmpro_member_links_top()
{
  if (pmpro_hasMembershipLevel(array(2,3,4,5,6,7,8,9,10))) 
  ?><li><a href="/membership-account/subscribe/">Renew your subscription</a></li><li><a href="/membership-account/your-profile/">Change your print magazine mailing address</a></li><?php
}                                                 

add_action("pmpro_member_links_top", "hc_pmpro_member_links_top");

add_action('frm_validate_entry', 'validate_my_form', 20, 2);
function validate_my_form($errors, $values){
  if( ($values['form_id'] == 4) || ($values['form_id'] == 5) || ($values['form_id'] == 6)) {
    if ( ! is_user_logged_in() ) {
      $errors['my_error'] = 'You must be logged in to submit this form.';
      return $errors;
    }
  }
}

// Add event manager widget to Smart Mag page builder. Commenting out since we may stop using event-manager...

// include 'em-events.php';

// add_filter('siteorigin_panels_widgets', 'my_add_widgets', 20);

function my_add_widgets($widgets) {
  $widgets['events-list-widget'] = array('class' => 'EM_Widget', 'name' => 'Events List Widget');
  return $widgets;
}

function my_tribe_event_featured_image($featured_image, $post_id, $size, $image_src) {
  if (($size == 'thumbnail') && (! empty($image_src[0]))) {
    return '<div class="tribe-events-event-image hc_post_thumbnail"><a href="' . tribe_get_event_link() . '" title="' . get_the_title( $post_id ) . '"><img src="' . $image_src[0] . '" title="' . get_the_title( $post_id ) . '" width="75" height="75" /></a></div>';
  } else {
    return $featured_image;
  }
}

add_filter('tribe_event_featured_image', 'my_tribe_event_featured_image',10, 4);

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
