<?php

/**
* Show the table of users
* Expects:
* $l: type of users to show
* $s: search string
* $start: row to start with
* $pn: page number
* $theusers: sql result array
* $totalrows: number rows returned
* $levels: available membership levels
 **/

//only admins can get this
if(!function_exists("current_user_can") || (!current_user_can("manage_options") && !current_user_can("pmpro_memberslist")))
{
    die(__("You do not have permissions to perform this action.", "pmpro"));
}  

?>


<!-- The actual user table -->
<p class="clear"><?php printf(__("%d members found.", "pmpro"), $totalrows);?></span></p>
<table class="widefat">
    <thead>
	<tr class="thead">
	    <th><?php _e('ID', 'pmpro');?></th>
	    <th><?php _e('Username', 'pmpro');?></th>
	    <th><?php _e('First&nbsp;Name', 'pmpro');?></th>
	    <th><?php _e('Last&nbsp;Name', 'pmpro');?></th>
	    <th><?php _e('Email', 'pmpro');?></th>
	    <th><?php _e('Billing Address', 'pmpro');?></th>
	    <th><?php _e('Shipping Address', 'pmpro');?></th>  
	    <th><?php _e('Membership', 'pmpro');?></th>  
	    <th><?php _e('Fee', 'pmpro');?></th>
	    <th><?php _e('Joined', 'pmpro');?></th>
	    <th><?php _e('Expires', 'pmpro');?></th>
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
			echo $userlink;
			?>                  
		    </strong>
		</td>
		<td><?php echo $theuser->first_name?></td>
		<td><?php echo $theuser->last_name?></td>
		<td><a href="mailto:<?php echo $theuser->user_email?>"><?php echo $theuser->user_email?></a></td>
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
			echo date(get_option("date_format"), $auser->enddate);
		    else
			echo "Never";
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

