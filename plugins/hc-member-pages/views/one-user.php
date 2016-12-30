<?php

// expects: $user_id, $wpdb, $woocommerce, $woobaddr, $woosaddr, $pmbaddr, $pmsaddr
// View one member

$user_data = $wpdb->get_row($wpdb->prepare("SELECT u.ID,  u.user_login,  u.user_email,  u.user_registered as joindate,  u.user_login,  u.user_nicename,  u.user_url,  u.user_registered,  u.user_status,  u.display_name FROM $wpdb->users u WHERE u.ID = %s ", $user_id));

// This will now return at least one row (for the user) whether they
// have ever had a membership or not, assuming there is a user.

    if(! $user_data) {
	$pmpro_msg = __("Could not find user with id " . $user_id, "pmpro");
	$pmpro_msgt = "error";
	return;
    }
    
// Get the subscriptions, if any

$user_subs = $wpdb->get_results($wpdb->prepare("SELECT mu.membership_id,  mu.initial_payment,  mu.billing_amount,  mu.cycle_period, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate,  m.name as membership, mu.status as membership_status FROM $wpdb->pmpro_memberships_users mu LEFT JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id WHERE mu.user_id = %s ORDER BY mu.id desc ", $user_id));

    // Done getting user data, now decide what to do with it

?>

<h2>Subscriptions for <?php echo $user_data->display_name?></h2>
<form id="posts-filter" method="get" action="">  
    <div class="search-box" style="float: right;">
	<label class="hidden" for="post-search-input"><?php _e('Search Members', 'pmpro');?>:</label>
	<input id="post-search-input" type="text" value="<?php echo $s?>" name="s"/>
	<input class="button" type="submit" value="<?php _e('Search Members', 'pmpro');?>"/>
	<input type="hidden" name="page" value="<?php echo HC_MEMBER_PAGE_SLUG ?>" />    
    </div>
</form>
<br clear="all" />
<form id="posts-filter-2" method="get" action="">  
    <div class="search-box" style="float: right;">
	<label class="hidden" for="post-search-input"><?php _e('Search Members', 'pmpro');?>:</label>
	<input id="post-search-input-2" type="text" value="<?php echo $user_id?>" name="user_id"/>
	<input class="button" type="submit" value="<?php _e('Go to user id', 'pmpro');?>"/>
	<input type="hidden" name="page" value="<?php echo HC_MEMBER_PAGE_SLUG ?>" />    
    </div>
</form>
<a href="?page=<?php echo HC_MEMBER_PAGE_SLUG ?>">&lt;-back to member list</a>

<h3>PMPro vs. WooCommerce Addresses</h3>
<table class="widefat">
    <thead>
	<tr class="thead">
	    <th>PMPro shipping address</th>
	    <th>PMPro billing address</th>
	    <th>WooCommerce shipping address</th>
	    <th>WooCommerce billing address</th>
	</tr>
    </thead>
    <tbody>
	<tr class="tbody">
	    <td><?php echo $pmsaddr ?></td>
	    <td><?php echo $pmbaddr ?></td>
	    <td><?php echo $woosaddr ?></td>
	    <td><?php echo $woobaddr ?></td>
	</tr>
	<!-- actions -->
	<tr class="tbody">
	    <td>
		<a href="admin.php?page=<?php echo HC_MEMBER_PAGE_SLUG ?>&amp;user_id=<?php echo $user_id ?>&amp;copy_saddr_to_woo_ship=true">Copy to woo shipping</a> | <a href="admin.php?page=<?php echo HC_MEMBER_PAGE_SLUG ?>&amp;user_id=<?php echo $user_id ?>&amp;copy_saddr_to_woo_bill=true">Copy to woo billing</a> | <a href="admin.php?page=<?php echo HC_MEMBER_PAGE_SLUG ?>&amp;user_id=<?php echo $user_id ?>&amp;copy_saddr_to_woo_both=true">Copy to both woo</a>
	    </td>
	    <td>
		<a href="admin.php?page=<?php echo HC_MEMBER_PAGE_SLUG ?>&amp;user_id=<?php echo $user_id ?>&amp;copy_baddr_to_woo_ship=true">Copy to woo shipping</a> | <a href="admin.php?page=<?php echo HC_MEMBER_PAGE_SLUG ?>&amp;user_id=<?php echo $user_id ?>&amp;copy_baddr_to_woo_bill=true">Copy to woo billing</a> | <a href="admin.php?page=<?php echo HC_MEMBER_PAGE_SLUG ?>&amp;user_id=<?php echo $user_id ?>&amp;copy_baddr_to_woo_both=true">Copy to both woo</a>
	    </td>
	</tr>
    </tbody>
</table>

<p>
 <a href="admin.php?page=<?php echo HC_MEMBER_PAGE_SLUG ?>&amp;user_id=<?php echo $user_id ?>&amp;copy_baddr=true">Copy PMPro billing</a>
</p>

<h3>Current and prior subscriptions:</h3>
<table class="widefat">
    <thead>
	<tr class="thead">
	    <th><?php _e('Membership', 'pmpro');?></th>
	    <th><?php _e('Status', 'pmpro');?></th>
	    <th><?php _e('Start Date', 'pmpro');?></th>
	    <th><?php _e('Expiration Date', 'pmpro');?></th>
	</tr>
    </thead>
    <tbody id="users" class="list:user user-list">  
	<?php foreach($user_subs as $sub) { ?>
	    <tr>
		<td><?php echo $sub->membership?></td>
		<td><?php echo $sub->membership_status?></td>
		<td><?php echo date(get_option("date_format"), $sub->startdate)?></td>
		<td><?php echo date(get_option("date_format"), $sub->enddate)?></td>
	    </tr>
	<?php 
	} ?>
    </tbody>
</table>

<?php
$id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $wpdb->pmpro_membership_orders WHERE user_id = %s ORDER BY id DESC LIMIT 1", $user_id));
if($id != NULL) {
    $last_order = new MemberOrder($id)

?>

    <h3>Most recent order</h3>
    <ul>
	<li>ID: <a href="admin.php?page=pmpro-orders&order=<?php echo $last_order->id;?>"><?php echo $last_order->id?></a>

	    <li>Subtotal: <?php echo 
			  money_format('%n', $last_order->subtotal)?></li>
	    <li>Tax: <?php echo money_format('%n', $last_order->tax)?></li>
	    <li>Total: <?php echo money_format('%n', $last_order->total)?></li>
	    <li>Payment Type: <?php echo $last_order->payment_type?></li>
	    <li>Status: <?php echo $last_order->status?></li>
    </ul>

<?php } else { ?>
    <h3>No orders for this user</h3>
    
<?php } ?>

<p>
    View user's WooCommerce <a href="/wp-admin/edit.php?s=<?php echo $sub->user_email ?>&post_status=all&post_type=shop_order">orders</a>
</p>


