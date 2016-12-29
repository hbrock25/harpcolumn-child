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

<!-- form to choose users -->

<form id="posts-filter" method="get" action="">  
    <h2>
	<?php _e('Members List', 'pmpro');?>
	<a target="_blank" href="<?php echo admin_url('admin-ajax.php');?>?action=hc_memberslist_csv&s=<?php echo $s?>&l=<?php echo $l?>" class="add-new-h2"><?php _e('Export to CSV', 'pmpro');?></a>
    </h2>    
    <ul class="subsubsub">
	<li>      
	    <?php _e('Show', 'pmpro');?>

	    <!-- These are the custom queries -->
	    <select name="l" onchange="jQuery('#posts-filter').submit();">
		<option value="" <?php if(!$l) { ?>selected="selected"<?php } ?>><?php _e('All Levels', 'pmpro');?></option>
		<option value="paid" <?php if($l == "paid") { ?>selected="selected"<?php } ?>><?php _e('Paid Subscribers', 'pmpro');?></option>
		<option value="new_non_subs" <?php if($l == "new_non_subs") { ?>selected="selected"<?php } ?>><?php _e('New Non-Subscribers', 'pmpro');?></option>
		<option value="paid_print_domestic" <?php if($l == "paid_print_domestic") { ?>selected="selected"<?php } ?>><?php _e('Paid Domestic Print Subscribers', 'pmpro');?></option>
		<option value="exp_last_60_print" <?php if($l == "exp_last_60_print") { ?>selected="selected"<?php } ?>><?php _e('Recently Expired Domestic Print Subs', 'pmpro');?></option>
		<option value="exp_next_month" <?php if($l == "exp_next_month") { ?>selected="selected"<?php } ?>><?php _e('Expires next month', 'pmpro');?></option>
		<option value="exp_next_2_3" <?php if($l == "exp_next_2_3") { ?>selected="selected"<?php } ?>><?php _e('Expires 2-3 months out', 'pmpro');?></option>
		<option value="exp_next_4_5" <?php if($l == "exp_next_4_5") { ?>selected="selected"<?php } ?>><?php _e('Expires 4-5 months out', 'pmpro');?></option>

		<!-- These are the standard level queries -->
		<?php
		
		foreach($levels as $level)
		{
		?>
		    <option value="<?php echo $level->id?>" <?php if($l == $level->id) { ?>selected="selected"<?php } ?>><?php echo $level->name?></option>
                <?php
                }
		?>
	    </select>      
	</li>
    </ul>
    <p class="search-box">
	<label class="hidden" for="post-search-input"><?php _e('Search Members', 'pmpro');?>:</label>
	<input type="hidden" name="page" value="hc-members-list" />    
	<input id="post-search-input" type="text" value="<?php echo $s?>" name="s"/>
	<input class="button" type="submit" value="<?php _e('Search Members', 'pmpro');?>"/>
    </p>
</form>
