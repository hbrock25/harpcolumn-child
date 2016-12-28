<?php

//only admins can get this
if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("pmpro_memberslist")))
{
    die(__("You do not have permissions to perform this action.", "pmpro"));
}  

//vars
if(isset($_REQUEST['s']))
    $s = $_REQUEST['s'];
else
    $s = false;

if(isset($_REQUEST['l']))
    $l = $_REQUEST['l'];
else
    $l = false;

?>

<form id="posts-filter" method="get" action="">  
    <h2>
	<?php _e('Members List', 'pmpro');?>
	<a target="_blank" href="<?php echo admin_url('admin-ajax.php');?>?action=hc_memberslist_csv&s=<?php echo $s?>&l=<?php echo $l?>" class="add-new-h2"><?php _e('Export to CSV', 'pmpro');?></a>
    </h2>    
    <ul class="subsubsub">
	<li>      
	    <?php _e('Show', 'pmpro');?>
	    <select name="l" onchange="jQuery('#posts-filter').submit();">
		<option value="" <?php if(!$l) { ?>selected="selected"<?php } ?>><?php _e('All Levels', 'pmpro');?></option>
		<option value="paid" <?php if($l == "paid") { ?>selected="selected"<?php } ?>><?php _e('Paid Subscribers', 'pmpro');?></option>
		<option value="new_non_subs" <?php if($l == "new_non_subs") { ?>selected="selected"<?php } ?>><?php _e('New Non-Subscribers', 'pmpro');?></option>
		<option value="paid_print_domestic" <?php if($l == "paid_print_domestic") { ?>selected="selected"<?php } ?>><?php _e('Paid Domestic Print Subscribers', 'pmpro');?></option>
		<option value="exp_last_60_print" <?php if($l == "exp_last_60_print") { ?>selected="selected"<?php } ?>><?php _e('Recently Expired Domestic Print Subs', 'pmpro');?></option>
		<option value="exp_next_month" <?php if($l == "exp_next_month") { ?>selected="selected"<?php } ?>><?php _e('Expires next month', 'pmpro');?></option>
		<option value="exp_next_2_3" <?php if($l == "exp_next_2_3") { ?>selected="selected"<?php } ?>><?php _e('Expires 2-3 months out', 'pmpro');?></option>
		<option value="exp_next_4_5" <?php if($l == "exp_next_4_5") { ?>selected="selected"<?php } ?>><?php _e('Expires 4-5 months out', 'pmpro');?></option>

		<?php
		$levels = $wpdb->get_results("SELECT id, name FROM $wpdb->pmpro_membership_levels ORDER BY name");
		foreach($levels as $level)
		{
		?>
		    <option value="<?php echo $level->id?>" <?php if($l == $level->id) { ?>selected="selected"<?php } ?>><?php echo $level->name?></option>
                <?php
                }
		?>
		<option value="oldmembers" <?php if($l == "oldmembers") { ?>selected="selected"<?php } ?>><?php _e('Old Members', 'pmpro');?></option>
	    </select>      
	</li>
    </ul>
    <p class="search-box">
	<label class="hidden" for="post-search-input"><?php _e('Search Members', 'pmpro');?>:</label>
	<input type="hidden" name="page" value="hc-members-list" />    
	<input id="post-search-input" type="text" value="<?php echo $s?>" name="s"/>
	<input class="button" type="submit" value="<?php _e('Search Members', 'pmpro');?>"/>
    </p>
	<?php 
	//some vars for the search
	if(isset($_REQUEST['pn']))
	    $pn = $_REQUEST['pn'];
	else
	    $pn = 1;
	
	if(isset($_REQUEST['limit']))
	    $limit = $_REQUEST['limit'];
	else
	    $limit = 15;
	
	$end = $pn * $limit;
	$start = $end - $limit;

	$search_clause = "";
	if($s) {
	    $search_clause = "AND (u.user_login LIKE '%$s%' OR u.user_email LIKE '%$s%' OR um.meta_value LIKE '%$s%')";
	}

	$sqlQuery = "SELECT SQL_CALC_FOUND_ROWS u.ID, u.user_login, u.user_email, UNIX_TIMESTAMP(u.user_registered) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate, m.name as membership FROM $wpdb->users u LEFT JOIN $wpdb->usermeta um ON u.ID = um.user_id LEFT JOIN $wpdb->pmpro_memberships_users mu ON u.ID = mu.user_id";

	if($l == "exp_last_60_print") {
	    $sqlQuery .= " AND mu.status = 'expired' AND mu.membership_id IN(2, 6) LEFT JOIN wp_pmpro_memberships_users mu2 ON u.ID = mu2.user_id AND mu2.status = 'active' AND mu2.membership_id = 1 "; 
	} elseif($l == "oldmembers") {
	    $sqlQuery .= " LEFT JOIN $wpdb->pmpro_memberships_users mu2 ON u.ID = mu2.user_id AND mu2.status = 'active' ";
	}

	$sqlQuery .= " LEFT JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id WHERE ";

	// where clause used to contain mu.membership_id > 0 
	
	// append the search restriction if any
	$sqlQuery .= $search_clause;
	
	// Add the restrictions for the various member classes
	if($l == "oldmembers")
	    $sqlQuery .= " AND mu.status = 'inactive' AND mu2.status IS NULL ";
	elseif($l == "paid")
	$sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 7)";
	elseif($l == "paid_print_domestic")
	$sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 4, 5, 7)";
	elseif($l == "exp_last_60_print")
	$sqlQuery .= " AND date(mu.enddate) < CURDATE() AND date(mu.enddate) > (DATE_SUB(CURDATE(), INTERVAL 2 MONTH))";
	elseif($l == "exp_next_month")
        // This is for renewal notices -- only do them for
	// domestic and foreign non-agency subscribers
	$sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) AND (LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)) >= date(mu.enddate))";
	elseif($l == "exp_next_2_3")
	$sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) AND (mu.enddate >= STR_TO_DATE(((PERIOD_ADD(EXTRACT(YEAR_MONTH FROM CURDATE()),2)*100)+1), '%Y%m%d')) AND (mu.enddate <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 3 MONTH)))";
	elseif($l == "exp_next_4_5")
	$sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) AND (mu.enddate >= STR_TO_DATE(((PERIOD_ADD(EXTRACT(YEAR_MONTH FROM CURDATE()),4)*100)+1), '%Y%m%d')) AND (mu.enddate <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 5 MONTH)))";
	elseif($l == "new_non_subs")
	$sqlQuery .= " AND (mu.user_id IS NULL OR mu.membership_id = 0) "
	elseif($l)
	$sqlQuery .= " AND mu.status = 'active' AND mu.membership_id = '" . $l . "' ";          
	else
	    $sqlQuery .= " AND mu.status = 'active' ";      
	
	$sqlQuery .= " GROUP BY u.ID ";
	
	if($l == "oldmembers")
	    $sqlQuery .= "ORDER BY enddate DESC ";
	else
	    $sqlQuery .= "ORDER BY u.user_registered DESC ";
	
	$sqlQuery .= "LIMIT $start, $limit";

	$sqlQuery = apply_filters("pmpro_members_list_sql", $sqlQuery);

	// Query assembled, now get the results
	$theusers = $wpdb->get_results($sqlQuery);
	$totalrows = $wpdb->get_var("SELECT FOUND_ROWS() as found_rows");
	
	?>
    <p class="clear"><?php printf(__("%d members found.", "pmpro"), $totalrows);?></span></p>
    <table class="widefat">
	<thead>
	    <tr class="thead">
		<th><?php _e('ID', 'pmpro');?></th>
		<th><?php _e('Username', 'pmpro');?></th>
		<th><?php _e('First&nbsp;Name', 'pmpro');?></th>
		<th><?php _e('Last&nbsp;Name', 'pmpro');?></th>
		<th><?php _e('Email', 'pmpro');?></th>
		<?php do_action("pmpro_memberslist_extra_cols_header", $theusers);?>
		<th><?php _e('Billing Address', 'pmpro');?></th>
		<th><?php _e('Shipping Address', 'pmpro');?></th>  
		<th><?php _e('Membership', 'pmpro');?></th>  
		<th><?php _e('Fee', 'pmpro');?></th>
		<th><?php _e('Joined', 'pmpro');?></th>
		<th>
		    <?php 
		    if($l == "oldmembers")
			_e('Ended', 'pmpro');
		    else
			_e('Expires', 'pmpro');
		    ?>
		</th>
		<th><?php _e('Orders', 'pmpro');?></th>		
	    </tr>
	</thead>
	<tbody id="users" class="list:user user-list">  
	    <?php  
	    $count = 0;              
	    foreach($theusers as $auser)
	    {
		//get meta                                          
		$theuser = get_userdata($auser->ID);  
	    ?>
		<tr <?php if($count++ % 2 == 0) { ?>class="alternate"<?php } ?>>
		    <td><?php echo $theuser->ID?></td>
		    <td>
			<?php echo get_avatar($theuser->ID, 32)?>
			<strong>
			    <?php
			    $userlink = '<a href="user-edit.php?user_id=' . $theuser->ID . '">' . $theuser->user_login . '</a>';
			    $userlink = apply_filters("pmpro_members_list_user_link", $userlink, $theuser);
			    echo $userlink;
			    ?>                  
			</strong>
		    </td>
		    <td><?php echo $theuser->first_name?></td>
		    <td><?php echo $theuser->last_name?></td>
		    <td><a href="mailto:<?php echo $theuser->user_email?>"><?php echo $theuser->user_email?></a></td>
		    <?php do_action("pmpro_memberslist_extra_cols_body", $theuser);?>
		    <td>
			<?php
			
			echo $woocommerce->countries->get_formatted_address(
			    array(
				'first_name' => $theuser->billing_first_name,
				'last_name' => $theuser->billing_last_name,
				'company' => $theuser->billing_company,
				'address_1' => $theuser->billing_address_1,
				'address_2' => $theuser->billing_address_2,
				'city' => $theuser->billing_city,
				'state' => $theuser->billing_state,
				'postcode' => $theuser->billing_postcode,
				'country' => $theuser->billing_country));
			?>                
		    </td>
		    <td>
			<?php
			
			echo $woocommerce->countries->get_formatted_address(
			    array(
				'first_name' => $theuser->shipping_first_name,
				'last_name' => $theuser->shipping_last_name,
				'company' => $theuser->shipping_company,
				'address_1' => $theuser->shipping_address_1,
				'address_2' => $theuser->shipping_address_2,
				'city' => $theuser->shipping_city,
				'state' => $theuser->shipping_state,
				'postcode' => $theuser->shipping_postcode,
				'country' => $theuser->shipping_country));
			?>                
		    </td>
		    <td><?php echo $auser->membership?></td>  
		    <td>                    
			<?php if((float)$auser->initial_payment > 0) { ?>
			    <?php echo $pmpro_currency_symbol; ?><?php echo $auser->initial_payment?>
			<?php } ?>
			<?php if((float)$auser->initial_payment > 0 && (float)$auser->billing_amount > 0) { ?>+<br /><?php } ?>
			<?php if((float)$auser->billing_amount > 0) { ?>
			    <?php echo $pmpro_currency_symbol; ?><?php echo $auser->billing_amount?>/<?php echo $auser->cycle_period?>
			<?php } ?>
			<?php if((float)$auser->initial_payment <= 0 && (float)$auser->billing_amount <= 0) { ?>
			    -
			<?php } ?>
		    </td>            
		    <td><?php echo date(get_option("date_format"), strtotime($theuser->user_registered, current_time("timestamp")))?></td>
		    <td>
			<?php 
			if($auser->enddate) 
			    echo apply_filters("pmpro_memberslist_expires_column", date(get_option('date_format'), $auser->enddate), $auser);
			else
			    echo __(apply_filters("pmpro_memberslist_expires_column", "Never", $auser), "pmpro");
			?>
		    </td>
		    <td>
			<a href="admin.php?page=<?php echo HC_MEMBER_PAGE_SLUG ?>&user_id=<?php echo $theuser->ID ?>">pmpro</a> |
			<a href="/wp-admin/edit.php?s=<?php echo $theuser->user_email ?>&post_status=all&post_type=shop_order">woo</a>
		    </td>
		</tr>
	    <?php
	    }
	    
	    if(!$theusers)
	    {
	    ?>
		<tr>
		    <td colspan="9"><p><?php _e("No members found.", "pmpro");?> <?php if($l) { ?><a href="?page=<?php echo HC_MEMBER_PAGE_SLUG ?>&s=<?php echo $s?>"><?php _e("Search all levels", "pmpro");?></a>.<?php } ?></p></td>
                </tr>
            <?php
            }
	    ?>    
	</tbody>
    </table>
</form>

<?php
echo pmpro_getPaginationString($pn, $totalrows, $limit, 1, add_query_arg(array("s" => urlencode($s), "l" => $l, "limit" => $limit)));

// The above should Just Work, if so remove the string below.
//    echo pmpro_getPaginationString($pn, $totalrows, $limit, 1, get_admin_url(NULL, "/admin.php?page=" . HC_MEMBER_PAGE_SLUG . "&s=" . urlencode($s)), "&l=$l&limit=$limit&pn=");


/*    require_once(dirname(__FILE__) . "/admin_footer.php"); */

