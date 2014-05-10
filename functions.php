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
add_filter("pmpro_email_recipient", "dae_pmpro_email_recipient", 10, 2);

// For buddyblog form -- don't let users set their own categories.

function buddyblog_my_post_form_settings($settings)
{

  // edit existing settings array rather than make a whole new one
  unset($settings['tax']);
  $settings['upload_count'] = 3;
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

function hc_bp_user_can_create_groups_func($can_create, $restricted) {
  return true;
}

add_filter("hc_bp_user_can_create_groups", "hc_bp_user_can_create_groups_func");
