<?php
  //only admins can get this
if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("pmpro_memberslist")))
  {
    die(__("You do not have permissions to perform this action.", "pmpro"));
  }  
  
/* Adding modes: 
 * Single-membership view -- show user's bill/ship addr, old and current memberships
 * Member-address-edit view -- edit user's address info
 * New-order/renewal view -- create a (check) new order or renewal for user
 */

//vars
global $wpdb, $pmpro_currency_symbol;
if(isset($_REQUEST['s']))
  $s = $_REQUEST['s'];
else
  $s = "";
  
if(isset($_REQUEST['l']))
  $l = $_REQUEST['l'];
else
  $l = false;
      
require_once(dirname(__FILE__) . "/admin_header.php");    
?>

<?php
// View one member
if(!empty($_REQUEST['user_id'])) {
  $user_id = $_REQUEST['user_id'];

  // We have a user. Go get all the relevant info.
  $user_subs = $wpdb->get_results($wpdb->prepare("SELECT u.ID,  u.user_login,  u.user_email,  u.user_registered as joindate,  u.user_login,  u.user_nicename,  u.user_url,  u.user_registered,  u.user_status,  u.display_name,  mu.membership_id,  mu.initial_payment,  mu.billing_amount,  mu.cycle_period, DATE(mu.startdate) as startdate, DATE(mu.enddate) as enddate,  m.name as membership, mu.status as membership_status FROM $wpdb->users u    LEFT JOIN $wpdb->pmpro_memberships_users mu    ON u.ID = mu.user_id   INNER JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id  WHERE u.ID = %s ORDER BY mu.id desc", $user_id));

  //no rows? Give up.

  if(count($user_subs) == 0) {
    $pmpro_msg = __("Could not find user with id " . $user_id, "pmpro");
    $pmpro_msgt = "error";
    return;
  }
  
  // Go get the metadata
  $sqlQuery = $wpdb->prepare("SELECT meta_key as `key`, meta_value as `value` FROM $wpdb->usermeta WHERE $wpdb->usermeta.user_id = %s", $user_id);
  $metavalues = pmpro_getMetavalues($sqlQuery);	
  
  // and the discount codes if any
  $sqlQuery = "SELECT c.id, c.code FROM $wpdb->pmpro_discount_codes_uses cu LEFT JOIN $wpdb->pmpro_discount_codes c ON cu.code_id = c.id WHERE cu.user_id = '" . $user_id . "' ORDER BY c.id DESC LIMIT 1";			
  $discount_code = $wpdb->get_row($sqlQuery);
  
  // Done getting user data, now decide what to do with it
  
  if(empty($_REQUEST['action'])) { 
    
?>
  <h2>Subscriptions for <?php echo $user_subs[0]->display_name?></h2>
  <form id="posts-filter" method="get" action="">  
    <div class="search-box" style="float: right;">
      <label class="hidden" for="post-search-input"><?php _e('Search Members', 'pmpro');?>:</label>
      <input id="post-search-input" type="text" value="<?php echo $s?>" name="s"/>
      <input class="button" type="submit" value="<?php _e('Search Members', 'pmpro');?>"/>
      <input type="hidden" name="page" value="pmpro-memberslist" />    
    </div>
  </form>
  <a href="?page=pmpro-memberslist">&lt;-back to member list</a>

  <table border="0" width="100%">
    <tr>				  
      <th style="text-align: left;">Billing address:</th>
      <th style="text-align: left;">Shipping address:</th>
    </tr>
    <tr>
      <td>
	<ul>
	  <li><?php echo $metavalues->pmpro_bfirstname?> <?php echo $metavalues->pmpro_blastname?></li>
	  <li><?php echo $metavalues->pmpro_baddress1?></li>
	  <li><?php echo $metavalues->pmpro_baddress2?></li>
	  <li><?php echo $metavalues->pmpro_bcity?></li>
	  <li><?php echo $metavalues->pmpro_bstate?></li>
	  <li><?php echo $metavalues->pmpro_bzipcode?></li>
	  <li><?php echo $metavalues->pmpro_bcountry?></li>
	  <li><?php echo $metavalues->pmpro_bphone?></li>
	</ul>
      </td>
      <td>
	<ul>
	  <li><?php echo $metavalues->pmpro_sfirstname?> <?php echo $metavalues->pmpro_slastname?></li>
	  <li><?php echo $metavalues->pmpro_saddress1?></li>
	  <li><?php echo $metavalues->pmpro_saddress2?></li>
	  <li><?php echo $metavalues->pmpro_scity?></li>
	  <li><?php echo $metavalues->pmpro_sstate?></li>
	  <li><?php echo $metavalues->pmpro_szipcode?></li>
	  <li><?php echo $metavalues->pmpro_scountry?></li>
	  <li><?php echo $metavalues->pmpro_sphone?></li>
	</ul>
      </td>
    </tr>
  </table>
  <p>
    <a href="?page=pmpro-memberslist&user_id=<?php echo $user_id?>&action=edit_address">change addresses</a>
  </p>
  <h3>Current and prior subscriptions:</h3>
  <table class="widefat">
    <thead>
      <tr class="thead">
	<th><?php _e('Membership', 'pmpro');?></th>
	<th><?php _e('Status', 'pmpro');?></th>
	<th><?php _e('Start Date', 'pmpro');?></th>
	<th><?php _e('Expiration Date', 'pmpro');?></th>
	<th><?php _e('Amount Paid', 'pmpro');?></th>
      </tr>
    </thead>
    <tbody id="users" class="list:user user-list">  
      <?php foreach($user_subs as $sub) { ?>
	<tr>
	  <td><?php echo $sub->membership?></td>
	  <td><?php echo $sub->membership_status?></td>
	  <td><?php echo $sub->startdate?></td>
	  <td><?php echo $sub->enddate?></td>
	  <td><?php echo $sub->initial_payment?></td>
	</tr>
	<?php 
	} ?>
    </tbody>
  </table>
  <p>
    <form method="get" action="">
      <label for="level">Subscribe or renew as:</label>
      <select name="level">
	<?php
	$levels = $wpdb->get_results("SELECT id, name FROM $wpdb->pmpro_membership_levels WHERE id > 1 ORDER BY name");
	foreach($levels as $level) {
	?>
	  <option value="<?php echo $level->id?>"><?php echo $level->name?>
	  </option>
        <?php } ?>
      </select>
      <input type="submit" value="Go" />
      <input type="hidden" name="page" value="pmpro-memberslist" />
      <input type="hidden" name="action" value="new_order" />
      <input type="hidden" name="user_id" value="<?php echo $user_id?>" />
    </form>
  </p>

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
  <?php 
  } else { 
  ?>
    <h3>No orders for this user</h3>
  
    <?php
    }
    } elseif($_REQUEST['action'] == "edit_address") {
      ?>
    
    <h2>Change address for <?php echo $user_subs[0]->display_name?></h2>
    <form method="post" action="">
      <table border="0" width="100%">
	<tr>				  
	  <th>Billing address:</th>
	  <th>Shipping address:</th>
	</tr>
	<tr>
	  <td>
	    <table class="form-table">
              <tr>
		<th scope="row" valign="top"><label for="pmpro_bfirstname"><?php _e('First Name', 'pmpro');?>:</label></th>
		<td>
		  <input id="billing_name" name="pmpro_bfirstname" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_bfirstname);?>" />
		</td>
              </tr>        
              <tr>
		<th scope="row" valign="top"><label for="pmpro_blastname"><?php _e('Last Name', 'pmpro');?>:</label></th>
		<td>
		  <input id="billing_name" name="pmpro_blastname" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_blastname);?>" />
		</td>
              </tr>        
              <tr>
		<th scope="row" valign="top"><label for="pmpro_baddress1"><?php _e('Address 1', 'pmpro');?>:</label></th>
		<td>
		  <input id="billing_street" name="pmpro_baddress1" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_baddress1);?>" /></td>
              </tr>
              <tr>
		<th scope="row" valign="top"><label for="pmpro_baddress2"><?php _e('Address 2', 'pmpro');?>:</label></th>
		<td>
		  <input id="billing_street" name="pmpro_baddress2" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_baddress2);?>" /></td>
              </tr>
              <tr>
		<th scope="row" valign="top"><label for="pmpro_bcity"><?php _e('City', 'pmpro');?>:</label></th>
		<td>
		  <input id="billing_city" name="pmpro_bcity" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_bcity);?>" /></td>
              </tr>
              <tr>
		<th scope="row" valign="top"><label for="pmpro_bstate"><?php _e('State', 'pmpro');?>:</label></th>
		<td>
		  <input id="billing_state" name="pmpro_bstate" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_bstate);?>" /></td>
              </tr>
              <tr>
		<th scope="row" valign="top"><label for="pmpro_bzipcode"><?php _e('Postal Code', 'pmpro');?>:</label></th>
		<td>
		  <input id="billing_zip" name="pmpro_bzipcode" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_bzipcode);?>" /></td>
              </tr>
              <tr>
		<th scope="row" valign="top"><label for="pmpro_bcountry"><?php _e('Country', 'pmpro');?>:</label></th>
		<td>
		  <input id="billing_country" name="pmpro_bcountry" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_bcountry);?>" />
		</td>
              </tr>
              <tr>
		<th scope="row" valign="top"><label for="pmpro_bphone"><?php _e('Phone', 'pmpro');?>:</label></th>
		<td>
		  <input id="billing_phone" name="pmpro_bphone" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_bphone);?>" />
		</td>
              </tr>
	    </table>
	  </td>
	  <td>
	    <table class="form-table">
	      <tr>
		<th scope="row" valign="top"><label for="pmpro_sfirstname"><?php _e('First Name', 'pmpro');?>:</label></th>
		<td>
		  <input id="shipping_name" name="pmpro_sfirstname" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_sfirstname);?>" />
		</td>
              </tr>        
              <tr>
		<th scope="row" valign="top"><label for="pmpro_slastname"><?php _e('Last Name', 'pmpro');?>:</label></th>
		<td>
		  <input id="shipping_name" name="pmpro_slastname" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_slastname);?>" />
		</td>
              </tr>        
              <tr>
		<th scope="row" valign="top"><label for="pmpro_saddress1"><?php _e('Address 1', 'pmpro');?>:</label></th>
		<td>
		  <input id="shipping_street" name="pmpro_saddress1" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_saddress1);?>" /></td>
              </tr>
              <tr>
		<th scope="row" valign="top"><label for="pmpro_saddress2"><?php _e('Address 2', 'pmpro');?>:</label></th>
		<td>
		  <input id="shipping_street" name="pmpro_saddress2" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_saddress2);?>" /></td>
              </tr>
              <tr>
		<th scope="row" valign="top"><label for="pmpro_scity"><?php _e('City', 'pmpro');?>:</label></th>
		<td>
		  <input id="shipping_city" name="pmpro_scity" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_scity);?>" /></td>
              </tr>
              <tr>
		<th scope="row" valign="top"><label for="pmpro_sstate"><?php _e('State', 'pmpro');?>:</label></th>
		<td>
		  <input id="shipping_state" name="pmpro_sstate" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_sstate);?>" /></td>
              </tr>
              <tr>
		<th scope="row" valign="top"><label for="pmpro_szipcode"><?php _e('Postal Code', 'pmpro');?>:</label></th>
		<td>
		  <input id="shipping_zip" name="pmpro_szipcode" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_szipcode);?>" /></td>
              </tr>
              <tr>
		<th scope="row" valign="top"><label for="pmpro_scountry"><?php _e('Country', 'pmpro');?>:</label></th>
		<td>
		  <input id="shipping_country" name="pmpro_scountry" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_scountry);?>" />
		</td>
              </tr>
              <tr>
		<th scope="row" valign="top"><label for="pmpro_sphone"><?php _e('Phone', 'pmpro');?>:</label></th>
		<td>
		  <input id="shipping_phone" name="pmpro_sphone" type="text" size="35" value="<?php echo esc_attr($metavalues->pmpro_sphone);?>" />
		</td>
              </tr>
	    </table>
	  </td>
	</tr>
      </table>
      <a href="?page=pmpro-memberslist&user_id=<?php echo $user_id?>">Cancel</a>
      <input type="submit" value="Change address" />
      <input type="hidden" name="page" value="pmpro-memberslist" />
      <input type="hidden" name="action" value="change_address" />
      <input type="hidden" name="user_id" value="<?php echo $user_id?>" />
    </form>
    <?php
    } elseif($_REQUEST['action'] == "change_address") {
      // Update billing and shipping address

      //save billing info ect, as user meta
      $meta_keys = array("pmpro_bfirstname", 
			 "pmpro_blastname", 
			 "pmpro_baddress1", 
			 "pmpro_baddress2", 
			 "pmpro_bcity", 
			 "pmpro_bstate", 
			 "pmpro_bzipcode", 
			 "pmpro_bcountry",
			 "pmpro_bphone",
			 "pmpro_bemail",
			 "pmpro_sfirstname",
			 "pmpro_slastname",
			 "pmpro_saddress1",
			 "pmpro_saddress2",
			 "pmpro_scity",
			 "pmpro_sstate",
			 "pmpro_szipcode",
 			 "pmpro_scountry",
 			 "pmpro_sphone");


      $meta_values = array($_REQUEST['pmpro_bfirstname'],
			   $_REQUEST['pmpro_blastname'],
			   $_REQUEST['pmpro_baddress1'],
			   $_REQUEST['pmpro_baddress2'],
			   $_REQUEST['pmpro_bcity'],
			   $_REQUEST['pmpro_bstate'],
			   $_REQUEST['pmpro_bzipcode'],
			   $_REQUEST['pmpro_bcountry'],
			   $_REQUEST['pmpro_bphone'],
			   $_REQUEST['pmpro_bemail'], 
			   $_REQUEST['pmpro_sfirstname'],
			   $_REQUEST['pmpro_slastname'],
			   $_REQUEST['pmpro_saddress1'],
			   $_REQUEST['pmpro_saddress2'],
			   $_REQUEST['pmpro_scity'],
			   $_REQUEST['pmpro_sstate'],
			   $_REQUEST['pmpro_szipcode'],
 			   $_REQUEST['pmpro_scountry'],
 			   $_REQUEST['pmpro_sphone']);

      pmpro_replaceUserMeta($user_id, $meta_keys, $meta_values);
      wp_redirect(
	get_admin_url(
	  NULL, '/admin.php?page=pmpro-memberslist&user_id=' . $user_id));
      exit;

    } elseif($_REQUEST['action'] == "new_order" 
	     && !empty($_REQUEST['level'])
	     // "recalculate" action will have submit == 'recalc'
	     && !isset($_REQUEST['submit'])) {

      // Create new order/renewal
      $level_id = $_REQUEST['level'];
      $latest_sub = $user_subs[0];
      if(isset($_REQUEST['initial_payment'])) {
	$initial_payment = $_REQUEST['initial_payment'];
      } 
      if(isset($_REQUEST['exp_day'])
	&& isset($_REQUEST['exp_month'])
	 && isset($_REQUEST['exp_year'])) {
	if(! $new_enddate = new DateTime($_REQUEST['exp_year'] 
				      . '-' . $_REQUEST['exp_month']
				      . '-' . $_REQUEST['exp_day'])) {
	  $new_enddate = "";
	}
      }

      // not going to actually save this -- 
      // just using it to show the admin price/tax totals
      $pmpro_level = $wpdb->get_row(
	$wpdb->prepare("SELECT * FROM $wpdb->pmpro_membership_levels WHERE id = '%s'", $level_id));

      // not going to actually save this -- 
      // just using it to show the admin price/tax totals
      $dummy_order = new MemberOrder();
      $dummy_order->membership_id = $pmpro_level->id;
      $dummy_order->membership_name = $pmpro_level->name;
      $dummy_order->discount_code = $discount_code;
      $dummy_order->PaymentAmount = $pmpro_level->billing_amount;
      $dummy_order->ProfileStartDate = $latest_sub->enddate;
      $dummy_order->BillingPeriod = $pmpro_level->cycle_period;
      $dummy_order->BillingFrequency = $pmpro_level->cycle_number;

      //other values -- need state for sales tax
      $dummy_order->billing = new stdClass();
      $dummy_order->billing->state = $metavalues->pmpro_bstate;

      // can override initial payment and end date
      if(isset($initial_payment)) {
	$dummy_order->InitialPayment = $initial_payment;
      } else {
	$dummy_order->InitialPayment = $pmpro_level->initial_payment;
      }
      if(empty($new_enddate)) {
	$date = new DateTime($latest_sub->enddate);
	// this assumes the expiration period is "year"...
	$date->add(new DateInterval('P' . $pmpro_level->expiration_number . 'Y'));
	$new_enddate = $date;
      } 

      // set the subtotal and tax
      $dummy_order->subtotal = $dummy_order->InitialPayment;
      $dummy_order->getTax();
      $dummy_order->total = (float)$dummy_order->subtotal + 
			     (float)$dummy_order->tax;

    ?>
      <h2>New order/Renewal for <?php echo $latest_sub->display_name?></h2>
      <h3>Current or most recent subscription:</h3>
      <ul>
	<li>Membership: <?php echo $latest_sub->membership?></li>
	<li>Pre-tax price: <?php echo money_format('%n', $latest_sub->initial_payment)?></li>
	<li>Expiration date: <?php echo $latest_sub->enddate?></li>
	<li>Status: <?php echo $latest_sub->membership_status?></li>
      </ul>

      <h3>Order details:</h3>

      <form method="post" action="">
	<table class="form-table">
	  <tr>
	    <th scope="row" valign="top">
	      <label for="initial_payment"><?php _e('Price', 'pmpro')?>:
	      </label>
	    </th>
	    <td>
	      <input id="initial_payment" name="initial_payment" type="text" size="10" value="<?php echo esc_attr($dummy_order->InitialPayment)?>" />
	    </td>
	  </tr>
	  <tr>
	    <th scope="row" valign="top">
	      <label for="tax"><?php _e('Tax', 'pmpro')?>:
	      </label>
	    </th>
	    <td>
	      <?php echo money_format('%n',$dummy_order->tax)?>
	    </td>
	  </tr>
	  <tr>
	    <th scope="row" valign="top">
	      <label for="total"><?php _e('Total', 'pmpro')?>:
	      </label>
	    </th>
	    <td>
	      <?php echo money_format('%n', $dummy_order->total)?>
	    </td>
	  </tr>
	  <tr>
	    <th scope="row" valign="top">
	      <label for="exp_month"><?php _e('Expiration Date', 'pmpro')?>:
	      </label>
	    </th>
	    <td>
              <?php
              // Now for the php date horror. Welcome to 1990.
              $year = $new_enddate->format('Y');
              $month = $new_enddate->format('m');
              $day = $new_enddate->format('d');
              ?>
              <select id="ts_month" name="exp_month">
		<?php                                
		for($i = 1; $i < 13; $i++) {
		?>
		  <option value="<?php echo $i?>" <?php if($i == $month) { ?>selected="selected"<?php } ?>>
		    <?php echo date("M", strtotime($i . "/1/" . $year))?>
		  </option>
		  <?php
		  }
		  ?>
              </select>
              <input name="exp_day" type="text" size="2" value="<?php echo $day?>" />
              <input name="exp_year" type="text" size="4" value="<?php echo $year?>" />
	    </td>
	  </tr>
	</table>
	<a href="?page=pmpro-memberslist&user_id=<?php echo $user_id?>">
	  Cancel</a>
	<input type="submit" name="recalc" value="Recalculate Tax/Total" />
	<input type="submit" name="submit" value="Submit" />
	<input type="hidden" name="page" value="pmpro-memberslist" />
	<input type="hidden" name="action" value="new_order" />
	<input type="hidden" name="user_id" value="<?php echo $user_id?>" />
	<input type="hidden" name="level" value="<?php echo $level_id?>" />
      </form>

      <?php    
      } elseif($_REQUEST['action'] == "new_order" 
	       && !empty($_REQUEST['level'])
	       && isset($_REQUEST['submit'])) {
	$level_id = $_REQUEST['level'];
	$latest_sub = end($user_subs);
	if(isset($_REQUEST['initial_payment'])) {
	  $initial_payment = $_REQUEST['initial_payment'];
	} 
	if(isset($_REQUEST['exp_day'])
	  && isset($_REQUEST['exp_month'])
	   && isset($_REQUEST['exp_year'])) {
	  if(! $new_enddate = new DateTime($_REQUEST['exp_year'] 
					  . '-' . $_REQUEST['exp_month']
					  . '-' . $_REQUEST['exp_day'])) {
	    $new_enddate = "";
	  }
	}

	// We have a winner!
	// Create and save the order and the new membership

	$pmpro_level = $wpdb->get_row(
	  $wpdb->prepare("SELECT * FROM $wpdb->pmpro_membership_levels WHERE id = '%s'", $level_id));

	$real_order = new MemberOrder();
	$real_order->user_id = $user_id;
	$real_order->membership_id = $pmpro_level->id;
	$real_order->membership_name = $pmpro_level->name;
	$real_order->discount_code = $discount_code;
	$real_order->PaymentAmount = $pmpro_level->billing_amount;
	$real_order->ProfileStartDate = $latest_sub->enddate;
	$real_order->BillingPeriod = $pmpro_level->cycle_period;
	$real_order->BillingFrequency = $pmpro_level->cycle_number;

	// set customer info on the order
        $real_order->billing = new stdClass();
        $real_order->billing->name = $metavalues->pmpro_bfirstname . " " . $metavalues->pmpro_blastname;
        $real_order->billing->street = trim($metavalues->pmpro_baddress1 . " " . $metavalues->pmpro_baddress2);
        $real_order->billing->city = $metavalues->pmpro_bcity;
        $real_order->billing->state = $metavalues->pmpro_bstate;
        $real_order->billing->country = $metavalues->pmpro_bcountry;
        $real_order->billing->zip = $metavalues->pmpro_bzipcode;
        $real_order->billing->phone = $metavalues->pmpro_bphone;

	// can override initial payment and end date
	if(isset($initial_payment)) {
	  $real_order->InitialPayment = $initial_payment;
	} else {
	  $real_order->InitialPayment = $pmpro_level->initial_payment;
	}
	if(empty($new_enddate)) {
	  $date = new DateTime($latest_sub->enddate);
	  // this assumes the expiration period is "year"...
	  $date->add(new DateInterval('P' . $pmpro_level->expiration_number . 'Y'));
	  $new_enddate = $date;
	} 
	
	// startdate may as well be now since we're 
	// going to cancel the old membership
	$startdate = new DateTime();


	// set the subtotal and tax
	$real_order->subtotal = $real_order->InitialPayment;
	$real_order->getTax();
	$real_order->total = (float)$real_order->subtotal + 
			      (float)$real_order->tax;

	$custom_level = array(
          'user_id' => $real_order->user_id,
          'membership_id' => $pmpro_level->id,
          'code_id' => "",
          'initial_payment' => $real_order->total,
          'billing_amount' => $pmpro_level->billing_amount,
          'cycle_number' => $pmpro_level->cycle_number,
          'cycle_period' => $pmpro_level->cycle_period,
          'billing_limit' => $pmpro_level->billing_limit,
          'trial_amount' => $pmpro_level->trial_amount,
          'trial_limit' => $pmpro_level->trial_limit,
          'startdate' => $startdate->format('Y-m-d'),
          'enddate' => $new_enddate->format('Y-m-d'));

	// set up the new membership
	$real_order->setGateway("check");
	$real_order->process();
	pmpro_changeMembershipLevel($custom_level, $real_order->user_id);

	//save
	if($real_order->saveOrder() !== false) {    
	  //handle timestamp
	  if($real_order->updateTimestamp(
	    $startdate->format('Y'), 
	    $startdate->format('m'),
	    $startdate->format('d')) !== false) {

            $pmpro_msg = __("Order saved successfully.", "pmpro");
            $pmpro_msgt = "success";
	  } else {
            $pmpro_msg = __("Error updating order timestamp.", "pmpro");
            $pmpro_msgt = "error";
	  }
	} else{
	  $pmpro_msg = __("Error saving order.", "pmpro");
	  $pmpro_msgt = "error";
	}

	// Order saved, back to the user view to verify
	wp_redirect(
	  get_admin_url(
	    NULL, '/admin.php?page=pmpro-memberslist&user_id=' . $user_id));
	exit;
      }
      } else {

  // list view
?>

      <form id="posts-filter" method="get" action="">  
	<h2>
	  <?php _e('Members List', 'pmpro');?>
	  <a target="_blank" href="<?php echo admin_url('admin-ajax.php');?>?action=memberslist_csv&s=<?php echo $s?>&l=<?php echo $l?>" class="add-new-h2"><?php _e('Export to CSV', 'pmpro');?></a>
	</h2>    
	<ul class="subsubsub">
	  <li>      
	    <?php _e('Show', 'pmpro');?>
	    <select name="l" onchange="jQuery('#posts-filter').submit();">
	      <option value="" <?php if(!$l) { ?>selected="selected"<?php } ?>><?php _e('All Levels', 'pmpro');?></option>
	      <option value="paid" <?php if($l == "paid") { ?>selected="selected"<?php } ?>><?php _e('Paid Subscribers', 'pmpro');?></option>
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
	  <input type="hidden" name="page" value="pmpro-memberslist" />    
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
        
	if($s)
	{
	  $sqlQuery = "SELECT SQL_CALC_FOUND_ROWS u.ID, u.user_login, u.user_email, UNIX_TIMESTAMP(u.user_registered) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate, m.name as membership FROM $wpdb->users u LEFT JOIN $wpdb->usermeta um ON u.ID = um.user_id LEFT JOIN $wpdb->pmpro_memberships_users mu ON u.ID = mu.user_id";

	  if($l == "exp_last_60_print") {
	    $sqlQuery .= " AND mu.status = 'inactive' AND mu.membership_id IN(2, 6) LEFT JOIN wp_pmpro_memberships_users mu2 ON u.ID = mu2.user_id AND mu2.status = 'active' AND mu2.membership_id = 1 "; 
	  } elseif($l == "oldmembers") {
	    $sqlQuery .= " LEFT JOIN $wpdb->pmpro_memberships_users mu2 ON u.ID = mu2.user_id AND mu2.status = 'active' ";
	  }

	  $sqlQuery .= " LEFT JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id WHERE mu.membership_id > 0 AND (u.user_login LIKE '%$s%' OR u.user_email LIKE '%$s%' OR um.meta_value LIKE '%$s%') ";        
	  
	  if($l == "oldmembers")
	    $sqlQuery .= " AND mu.status = 'inactive' AND mu2.status IS NULL ";

	  // This is horrific, I should make it better
	  elseif($l == "paid")
	  $sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 7)";
	  elseif($l == "paid_print_domestic")
	  $sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 4, 5, 7)";
	  elseif($l == "exp_last_60_print")
	  $sqlQuery .= " AND date(mu.enddate) < CURDATE() AND date(mu.enddate) > (DATE_SUB(CURDATE(), INTERVAL 2 MONTH))";

	  elseif($l == "exp_next_month")
          // This is for renewal notices -- only do them for domestic and foreign non-agency subscribers
	  $sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) AND (LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)) >= date(mu.enddate))";
	  elseif($l == "exp_next_2_3")
	  $sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) AND (mu.enddate >= STR_TO_DATE(((PERIOD_ADD(EXTRACT(YEAR_MONTH FROM CURDATE()),2)*100)+1), '%Y%m%d')) AND (mu.enddate <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 3 MONTH)))";
	  elseif($l == "exp_next_4_5")
	  $sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) AND (mu.enddate >= STR_TO_DATE(((PERIOD_ADD(EXTRACT(YEAR_MONTH FROM CURDATE()),4)*100)+1), '%Y%m%d')) AND (mu.enddate <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 5 MONTH)))";
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
	}
	else
	{
	  $sqlQuery = "SELECT SQL_CALC_FOUND_ROWS u.ID, u.user_login, u.user_email, UNIX_TIMESTAMP(u.user_registered) as joindate, mu.membership_id, mu.initial_payment, mu.billing_amount, mu.cycle_period, mu.cycle_number, mu.billing_limit, mu.trial_amount, mu.trial_limit, UNIX_TIMESTAMP(mu.startdate) as startdate, UNIX_TIMESTAMP(mu.enddate) as enddate, m.name as membership FROM $wpdb->users u LEFT JOIN $wpdb->pmpro_memberships_users mu ON u.ID = mu.user_id ";
	  
	  if($l == "exp_last_60_print") {
	    $sqlQuery .= " AND mu.status = 'inactive' AND mu.membership_id IN (2, 6) LEFT JOIN wp_pmpro_memberships_users mu2 ON u.ID = mu2.user_id AND mu2.status = 'active' AND mu2.membership_id = 1 "; 
	  } elseif($l == "oldmembers") {
	    $sqlQuery .= " LEFT JOIN $wpdb->pmpro_memberships_users mu2 ON u.ID = mu2.user_id AND mu2.status = 'active' ";
	  }
	  
	  $sqlQuery .= " LEFT JOIN $wpdb->pmpro_membership_levels m ON mu.membership_id = m.id WHERE mu.membership_id > 0  ";
	  
	  if($l == "oldmembers")
	    $sqlQuery .= " AND mu.status = 'inactive' AND mu2.status IS NULL ";
	  // This is horrific, I should make it better
	  elseif($l == "paid")
	  $sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 7)";
	  elseif($l == "paid_print_domestic")
	  $sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 4, 5, 7)";
	  elseif($l == "exp_last_60_print")
	  $sqlQuery .= " AND date(mu.enddate) < CURDATE() AND date(mu.enddate) > (DATE_SUB(CURDATE(), INTERVAL 2 MONTH))";
	  elseif($l == "exp_next_month")
	  $sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) AND (LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 1 MONTH)) >= date(mu.enddate))";
	  elseif($l == "exp_next_2_3")
	  $sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) AND (date(mu.enddate) >= STR_TO_DATE(((PERIOD_ADD(EXTRACT(YEAR_MONTH FROM CURDATE()),2)*100)+1), '%Y%m%d')) AND (date(mu.enddate) <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 3 MONTH)))";
	  elseif($l == "exp_next_4_5")
	  $sqlQuery .= " AND mu.status = 'active' AND mu.membership_id NOT IN(0, 1, 3, 7, 8, 9) AND (date(mu.enddate) >= STR_TO_DATE(((PERIOD_ADD(EXTRACT(YEAR_MONTH FROM CURDATE()),4)*100)+1), '%Y%m%d')) AND (date(mu.enddate) <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 5 MONTH)))";
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
	}

	$sqlQuery = apply_filters("pmpro_members_list_sql", $sqlQuery);
        
	$theusers = $wpdb->get_results($sqlQuery);
	$totalrows = $wpdb->get_var("SELECT FOUND_ROWS() as found_rows");
	
	if($theusers)
	{
	  $calculate_revenue = apply_filters("pmpro_memberslist_calculate_revenue", false);
	  if($calculate_revenue)
	  {
	    $initial_payments = pmpro_calculateInitialPaymentRevenue($s, $l);
	    $recurring_payments = pmpro_calculateRecurringRevenue($s, $l);      
	?>
	  <p class="clear"><?php echo strval($totalrows)?> members found. These members have paid <strong>$<?php echo number_format($initial_payments)?> in initial payments</strong> and will generate an estimated <strong>$<?php echo number_format($recurring_payments)?> in revenue over the next year</strong>, or <strong>$<?php echo number_format($recurring_payments/12)?>/month</strong>. <span class="pmpro_lite">(This estimate does not take into account trial periods or billing limits.)</span></p>
	  <?php
	  }
	  else
	  {
	  ?>
	    <p class="clear"><?php printf(__("%d members found.", "pmpro"), $totalrows);?></span></p>      
            <?php
            }
	    }    
	    ?>
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
		  <th><?php _e('Action', 'pmpro');?></th>		
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
		      echo pmpro_formatAddress(trim($theuser->pmpro_bfirstname . " " . $theuser->pmpro_blastname), $theuser->pmpro_baddress1, $theuser->pmpro_baddress2, $theuser->pmpro_bcity, $theuser->pmpro_bstate, $theuser->pmpro_bzipcode, $theuser->pmpro_bcountry, $theuser->pmpro_bphone);
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
		      <a href="admin.php?page=pmpro-memberslist&user_id=<?php echo $theuser->ID ?>"><?php _e('view', 'pmpro');?></a>
		    </td>
		  </tr>
		  <?php
		  }
		  
		  if(!$theusers)
		  {
		  ?>
		    <tr>
		      <td colspan="9"><p><?php _e("No members found.", "pmpro");?> <?php if($l) { ?><a href="?page=pmpro-memberslist&s=<?php echo $s?>"><?php _e("Search all levels", "pmpro");?></a>.<?php } ?></p></td>
                    </tr>
                    <?php
                    }
		    ?>    
	      </tbody>
	    </table>
      </form>
      
      <pre>
        <?php echo $sqlquery; ?>
      </pre>
      <?php
      echo pmpro_getPaginationString($pn, $totalrows, $limit, 1, get_admin_url(NULL, "/admin.php?page=pmpro-memberslist&s=" . urlencode($s)), "&l=$l&limit=$limit&pn=");
      }

      ?>
      
      <?php
      require_once(dirname(__FILE__) . "/admin_footer.php");  
      ?>
