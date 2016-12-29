<?php

/* functions to return various lists of users */

function get_members($l, $s, $limit, $start) {
    global $wpdb;
    
    // build the query

    // selects
    $select_clause = "
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

    // from/joins

    // condititional joins
    if($l == "exp_last_60_print") {
	$cond_join = " 
LEFT JOIN $wpdb->pmpro_memberships_users mu 
  ON u.ID = mu.user_id 
AND mu.status = 'expired' 
AND mu.membership_id IN(2, 6) 
LEFT JOIN $wpdb->pmpro_memberships_users mu2 
  ON u.ID = mu2.user_id AND mu2.status = 'active' 
  AND mu2.membership_id IN(0, 1) ";

    } else {
	$cond_join = "
LEFT JOIN $wpdb->pmpro_memberships_users mu 
  ON u.ID = mu.user_id 
";
    }

    // These are always in
    $from_clause = "
FROM $wpdb->users u 
LEFT JOIN $wpdb->usermeta um 
  ON u.ID = um.user_id "
		 . $cond_join // one of the two above
		 . "
LEFT JOIN $wpdb->pmpro_membership_levels m 
  ON mu.membership_id = m.id ";

    // WHERE stuff

    $search_clause = "";
    if($s) {
	$search_clause = " AND (u.user_login LIKE '%" . esc_sql($s) . "%' "
		       . "  OR u.user_email LIKE '%" . esc_sql($s) . "%' "
		       . "  OR um.meta_value LIKE '%" . esc_sql($s) . "%')";
    }

    // Choose the restriction for the various member classes, etc.

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

    $where_clause = "WHERE " . $restriction . $search_clause;


    $sqlQuery = $select_clause . $from_clause . $where_clause
	      . " GROUP BY u.ID "
	      . " ORDER BY u.user_registered DESC ";

    if($limit)
	$sqlQuery .= "LIMIT $start, $limit";

    // Query assembled, now get the results
    $theusers = $wpdb->get_results($sqlQuery);

    return $theusers;
}

function get_rowcount_last_query() {
    global $wpdb;
    return $wpdb->get_var("SELECT FOUND_ROWS() as found_rows");
}

function get_levels() {
    global $wpdb;
    return $wpdb->get_results("SELECT id, name FROM $wpdb->pmpro_membership_levels ORDER BY name");
}
