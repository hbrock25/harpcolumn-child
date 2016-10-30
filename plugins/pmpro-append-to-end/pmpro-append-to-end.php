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

