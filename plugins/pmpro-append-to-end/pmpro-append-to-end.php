<?php
/*
Plugin Name: Paid Memberships Pro: Extend membership by new level duration
Plugin URI: http://www.paidmembershipspro.com/wp/pmpro-customizations/
Description: On change of level, modify the end date to add the new level's time
Version: 1.0
Requires: 4.5.3
Author: Thomas Sjolshagen <thomas@eighty20results.com>
Author URI: http://www.eighty20results.com/thomas-sjolshagen/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

function e20r_extend_enddate_by_duration( $enddate, $user_id, $level, $startdate ) {

	// If the user has a current membership level
	if ( false !== ( $current_level = pmpro_getMembershipLevelForUser( $user_id ) )
	     && ! empty( $level->expiration_number ) && ! empty( $level->expiration_period )
	) {

		$enddate = date( 'Y-m-d', strtotime( "+ {$level->expiration_number} {$level->expiration_period}", $current_level->enddate ) );
	}

	return $enddate;
}

add_filter( 'pmpro_checkout_end_date', 'e20r_extend_enddate_by_duration', 10, 4 );

function harpcolumn_extend_enddate_pmprowoo($custom_level) {

  $pmpro_level = pmpro_getLevel($custom_level['id']);

  if ( false !== ( $current_level = pmpro_getMembershipLevelForUser( $custom_level['user_id'] ) )
	     && ! empty( $pmpro_level->expiration_number ) && ! empty( $pmpro_level->expiration_period )
       ) {
    $custom_level['enddate'] = date( 'Y-m-d', strtotime( "+ {$pmpro_level->expiration_number} {$pmpro_level->expiration_period}", $current_level->enddate ) );
  }

  return $custom_level;

}


/*
	apply end date extension filter to woo commerce checkouts as well
	
	$level_array is a custom_level array for the pmpro_changeMembershipLevel call
	$level_obj in the function is an object with the stored values for the level
*/
function pmprowoo_harpcol_checkout_level_extend_memberships($level_array)
{
	$level_obj = pmpro_getLevel($level_array['membership_id']);
	
	//does this level expire? are they an existing user of this level?
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
