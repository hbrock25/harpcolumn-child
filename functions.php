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
