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

function e20r_extend_enddate_by_duration( $enddate, $user_id, $level_obj, $startdate ) {

        //does this level expire? 
	if(!empty($level_obj) && !empty($level_obj->expiration_number) )
	{
		//get the current enddate of their membership
		$membership_level = pmpro_getMembershipLevelForUser($user_id);
		$expiration_date = $membership_level->enddate;
		
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
			$enddate = date("Y-m-d", strtotime("+ $total_days Days", $todays_date));
		}
	}
		
	return $enddate;

  }

add_filter( 'pmpro_checkout_end_date', 'e20r_extend_enddate_by_duration', 10, 4 );

