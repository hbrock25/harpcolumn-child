<?php	
//only admins can get this
if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("pmpro_memberslist_csv")))
{
    die(__("You do not have permissions to perform this action.", "pmpro"));
}	

global $wpdb;	

//get users	
if(isset($_REQUEST['s']))
    $s = $_REQUEST['s'];
else
    $s = false;

if(isset($_REQUEST['l']))
    $l = $_REQUEST['l'];
else
    $l = false;

//some vars for the search
if(!empty($_REQUEST['pn']))
    $pn = $_REQUEST['pn'];
else
    $pn = 1;

if(!empty($_REQUEST['limit']))
    $limit = $_REQUEST['limit'];
else
    $limit = false;

if($limit)
{	
    $end = $pn * $limit;
    $start = $end - $limit;		
}
else
{
    $end = NULL;
    $start = NULL;
}	

$search_clause = "";
if($s) {
    $search_clause = "AND (u.user_login LIKE '%" . esc_sql($s) . "%' OR u.user_email LIKE '%" . esc_sql($s) . "%' OR um.meta_value LIKE '%" . esc_sql($s) . "%')";
}

$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS u.ID, u.user_login, u.user_email, UNIX_TIMESTAMP(u.user_registered) as joindate, u.user_registered, mu.membership_id, mu.cycle_period, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate, m.name as membership FROM $wpdb->users u LEFT JOIN $wpdb->usermeta um ON u.ID = um.user_id LEFT JOIN $wpdb->pmpro_memberships_users mu ON u.ID = mu.user_id";

if($l == "exp_last_60_print") {
    $sqlQuery .= " AND mu.status = 'expired' AND mu.membership_id IN(2, 6) LEFT JOIN wp_pmpro_memberships_users mu2 ON u.ID = mu2.user_id AND mu2.status = 'active' AND mu2.membership_id = 1 "; 
}

$sqlQuery .= " LEFT JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id WHERE ";

// where clause used to contain mu.membership_id > 0 

// Add the restrictions for the various member classes
if($l == "paid")
    $sqlQuery .= " mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 7)";
elseif($l == "paid_print_domestic")
$sqlQuery .= " mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 4, 5, 7)";
elseif($l == "exp_last_60_print")
$sqlQuery .= " date(mu.enddate) < CURDATE() AND date(mu.enddate) > (DATE_SUB(CURDATE(), INTERVAL 2 MONTH))";
elseif($l == "exp_next_month")
// This is for renewal notices -- only do them for
// domestic and foreign non-agency subscribers
$sqlQuery .= " mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) AND (LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)) >= date(mu.enddate))";
elseif($l == "exp_next_2_3")
$sqlQuery .= " mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) AND (mu.enddate >= STR_TO_DATE(((PERIOD_ADD(EXTRACT(YEAR_MONTH FROM CURDATE()),2)*100)+1), '%Y%m%d')) AND (mu.enddate <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 3 MONTH)))";
elseif($l == "exp_next_4_5")
$sqlQuery .= " mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) AND (mu.enddate >= STR_TO_DATE(((PERIOD_ADD(EXTRACT(YEAR_MONTH FROM CURDATE()),4)*100)+1), '%Y%m%d')) AND (mu.enddate <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 5 MONTH)))";
elseif($l == "new_non_subs")
// They have no membership, or a guest membership, and they joined
// less than 60 days ago
$sqlQuery .= " (mu.user_id IS NULL OR mu.membership_id = 0) AND u.user_registered >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)";
elseif($l)
$sqlQuery .= " mu.status = 'active' AND mu.membership_id = '" . $l . "' ";          
else
    $sqlQuery .= " mu.status = 'active' ";      

// append the search restriction if any
$sqlQuery .= $search_clause;

$sqlQuery .= " GROUP BY u.ID ";

$sqlQuery .= "ORDER BY u.user_registered DESC ";

if($limit)
    $sqlQuery .= "LIMIT $start, $limit";

//filter
$sqlQuery = apply_filters("pmpro_members_list_sql", $sqlQuery);	

//get users
$theusers = $wpdb->get_results($sqlQuery);	

//begin output
header("Content-type: text/csv");	
if($s && $l)
    header("Content-Disposition: attachment; filename=members_list_" . intval($l) . "_level_" . sanitize_file_name($s) . ".csv");
elseif($s)
header("Content-Disposition: attachment; filename=members_list_" . sanitize_file_name($s) . ".csv");
else
    header("Content-Disposition: attachment; filename=members_list.csv");

$heading = "id,username,firstname,lastname,email,billing firstname,billing lastname,billing company,billing address1,billing address2,billing city,billing state,billing zipcode,billing country,billing phone,shipping firstname,shipping lastname,shipping company,shipping address1,shipping address2,shipping city,shipping state,shipping zipcode,shipping country,membership,term,discount_code_id,discount_code,joined,expires";

$csvoutput = $heading;

//these are the meta_keys for the fields (arrays are object, property. so e.g. $theuser->ID)
$default_columns = array(
    array("theuser", "ID"),
    array("theuser", "user_login"),
    array("metavalues", "first_name"),
    array("metavalues", "last_name"),
    array("theuser", "user_email"),
    array("metavalues", "billing_first_name"),
    array("metavalues", "billing_last_name"),
    array("metavalues", "billing_company"),
    array("metavalues", "billing_address_1"),
    array("metavalues", "billing_address_2"),
    array("metavalues", "billing_city"),
    array("metavalues", "billing_state"),
    array("metavalues", "billing_postcode"),
    array("metavalues", "billing_country"),
    array("metavalues", "shipping_phone"),
    array("metavalues", "shipping_first_name"),
    array("metavalues", "shipping_last_name"),
    array("metavalues", "shipping_company"),
    array("metavalues", "shipping_address_1"),
    array("metavalues", "shipping_address_2"),
    array("metavalues", "shipping_city"),
    array("metavalues", "shipping_state"),
    array("metavalues", "shipping_postcode"),
    array("metavalues", "shipping_country"),
    array("theuser", "membership"),
    array("theuser", "cycle_period"),
    array("discount_code", "id"),
    array("discount_code", "code")
    //joindate and enddate are handled specifically below
);

//filter
$default_columns = apply_filters("pmpro_members_list_csv_default_columns", $default_columns);

//any extra columns
$extra_columns = apply_filters("pmpro_members_list_csv_extra_columns", array());
if(!empty($extra_columns))
{
    foreach($extra_columns as $heading => $callback)
    {
	$csvoutput .= "," . $heading;
    }
}

$csvoutput .= "\n";	

//output
echo $csvoutput;
$csvoutput = "";

if($theusers)
{
    foreach($theusers as $theuser)
    {

	//get meta                                          
	$metavalues = get_userdata($theuser->ID);  
	$sqlQuery = "SELECT c.id, c.code FROM $wpdb->pmpro_discount_codes_uses cu LEFT JOIN $wpdb->pmpro_discount_codes c ON cu.code_id = c.id WHERE cu.user_id = '" . $theuser->ID . "' ORDER BY c.id DESC LIMIT 1";			
	$discount_code = $wpdb->get_row($sqlQuery);
	
	//default columns			
	if(!empty($default_columns))
	{
	    $count = 0;
	    foreach($default_columns as $col)
	    {
		//add comma after the first item
		$count++;
		if($count > 1)
		    $csvoutput .= ",";
		
		//checking $object->property. note the double $$
		if(!empty($$col[0]->$col[1]))
		    $csvoutput .= pmpro_enclose($$col[0]->$col[1]);	//output the value				
	    }
	}
	
	//joindate and enddate
	$csvoutput .= "," . pmpro_enclose(date("Y-m-d", $theuser->joindate)) . ",";
	
	if($theuser->membership_id)
	{
	    if($theuser->enddate)
		$csvoutput .= pmpro_enclose(apply_filters("pmpro_memberslist_expires_column", date("Y-m-d", $theuser->enddate), $theuser));
	    else
		$csvoutput .= pmpro_enclose(apply_filters("pmpro_memberslist_expires_column", "Never", $theuser));
	}
	else
	    $csvoutput .= "N/A";
	
	//any extra columns			
	if(!empty($extra_columns))
	{
	    foreach($extra_columns as $heading => $callback)
	    {
		$csvoutput .= "," . pmpro_enclose(call_user_func($callback, $theuser, $heading));
	    }
	}
	
	$csvoutput .= "\n";
	
	//output
	echo $csvoutput;
	$csvoutput = "";			
    }
}

print $csvoutput;

function pmpro_enclose($s)
{
    return "\"" . str_replace("\"", "\\\"", $s) . "\"";
}
