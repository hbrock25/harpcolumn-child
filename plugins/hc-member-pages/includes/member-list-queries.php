<?php

/* functions to return various lists of users */

function get_members($l, $s, $limit, $start) {
    global $wpdb;
    
    $sqlQuery = user_list_select()
	      . user_list_joins($l)
	      . user_list_where($l, $s)
	      . " GROUP BY u.ID "
	      . " ORDER BY u.user_registered DESC "
	      . $limit ? " LIMIT $start, $limit" : "";
 
    // Query assembled, now get the results
    return $wpdb->get_results($sqlQuery);
}

/**
 * Fields we need for the user list, including calculated fields
 * $wpdb->users == u
 * $wpdb->pmpro_memberships_users == mu
 * $wpdb->pmpro_membership_levels == m
 **/

function user_list_select() {

    return "
SELECT 
SQL_CALC_FOUND_ROWS 
u.ID, 
u.user_login, 
u.user_email, 
UNIX_TIMESTAMP(u.user_registered) as joindate, 
mu.membership_id, 
mu.initial_payment, 
mu.billing_amount, 
mu.cycle_period,
mu.cycle_number,
mu.billing_limit,
mu.trial_amount,
mu.trial_limit,
UNIX_TIMESTAMP(mu.startdate) as startdate,
UNIX_TIMESTAMP(mu.enddate) as enddate,
m.name as membership 
";

}

/**
 * Fields we need for the user list, including calculated fields
 * $wpdb->users == u
 * $wpdb->pmpro_memberships_users == mu
 * $wpdb->pmpro_memberships_users == mu2 (for checking expired vs. active)
 * $wpdb->pmpro_membership_levels == m
 *
 * $l is the kind of user list desired 
 **/

function user_list_joins($l) {
    global $wpdb;

    // Start with the bits that are always in.
    $from_clause = "
FROM $wpdb->users u 
LEFT JOIN $wpdb->usermeta um 
  ON u.ID = um.user_id 
LEFT JOIN $wpdb->pmpro_memberships_users mu 
  ON u.ID = mu.user_id ";
    
    // Here we want current users who have an expired domestic subscription
    // (there can be many of these), and no current subscription.
    // The WHERE clause will restrict the age of the expired subscription
    // to consider. We only add this join if the user asks for exp_last_60_print

    $cond_join = "
  AND mu.status = 'expired' 
  AND mu.membership_id IN(2, 6) 
LEFT JOIN $wpdb->pmpro_memberships_users mu2 
  ON u.ID = mu2.user_id 
  AND ((mu2.status = 'active' AND mu2.membership_id = 1) 
        OR mu2.membership_id = 0)";
    
    if($l == "exp_last_60_print")
	$from_clause .= $cond_join;
    
    // conditional join needs to come before this bit, since it defines
    // the mu table. This finishes it off.
    $from_clause .= "
LEFT JOIN $wpdb->pmpro_membership_levels m 
  ON mu.membership_id = m.id ";

    return $from_clause;
}

function user_list_where($l, $s) {
    global $wpdb;

    switch ($l) {
	case "paid":
	    $restriction = " mu.status = 'active' "
			 . "AND mu.membership_id NOT IN(0, 1, 7)";
	    break;

	case "paid_print_domestic":
	    $restriction = " mu.status = 'active' "
			 . "AND mu.membership_id NOT IN(0, 1, 3, 4, 5, 7)";
	    break;

	case "exp_last_60_print":
	    $restriction = " date(mu.enddate) < CURDATE() "
			 . "AND date(mu.enddate) > (DATE_SUB(CURDATE(), INTERVAL 2 MONTH))";
	    break;

	case "exp_next_month":
	    // This is for renewal notices -- only do them for
	    // domestic and foreign non-agency subscribers
	    $restriction = " mu.status = 'active' "
			 . "AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) "
			 . " AND (LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)) "
			 . "  >= date(mu.enddate))";
	    break;

	case "exp_next_2_3":
	    $restriction = " mu.status = 'active' "
			 . "AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) "
			 . "AND (mu.enddate >= "
			 . "  STR_TO_DATE(((PERIOD_ADD(EXTRACT(YEAR_MONTH FROM CURDATE()),2)*100)+1), '%Y%m%d')) "
			 . "AND (mu.enddate <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 3 MONTH)))";
	    break;

	case "exp_next_4_5":
	    $restriction = " mu.status = 'active' "
			 . "AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9)"
			 . "AND (mu.enddate >= STR_TO_DATE(((PERIOD_ADD(EXTRACT(YEAR_MONTH FROM CURDATE()),4)*100)+1), '%Y%m%d')) "
			 . "AND (mu.enddate <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 5 MONTH)))";
	    break;

	case "new_non_subs":
	    // They have no membership, or a guest membership, and they joined
	    // less than 60 days ago
	    $restriction = " (mu.membership_id IN(0, 1) OR mu.user_id IS NULL) "
			 . "AND u.user_registered >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)";
	    break;

	case true:
	    $restriction = " mu.status = 'active' "
			 . "AND mu.membership_id = '" . esc_sql($l) . "' ";
	    break;

	default:
	    $restriction = " mu.status = 'active' ";
    }

    // add the search clause if necessary
    $search_clause = "";
    if($s) {
	$search_clause = " AND (u.user_login LIKE '%" . esc_sql($s) . "%' "
		       . "  OR u.user_email LIKE '%" . esc_sql($s) . "%' "
		       . "  OR um.meta_value LIKE '%" . esc_sql($s) . "%')";
    }

    return "WHERE " . $restriction . $search_clause;
}

function get_rowcount_last_query() {
    global $wpdb;
    return $wpdb->get_var("SELECT FOUND_ROWS() as found_rows");
}

function get_levels() {
    global $wpdb;
    return $wpdb->get_results("SELECT id, name FROM $wpdb->pmpro_membership_levels ORDER BY name");
}

