<?php

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
