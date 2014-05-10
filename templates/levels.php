<?php 
global $wpdb, $pmpro_msg, $pmpro_msgt, $pmpro_levels, $current_user, $pmpro_currency_symbol;
if($pmpro_msg)
{
?>
  <div class="message <?php echo $pmpro_msgt?>"><?php echo $pmpro_msg?></div>
  <?php
  }
  ?>

  <div class="authors-list">
    
    <?php	
	 $count = 0;
	 foreach($pmpro_levels as $level)
	 {
	   if(isset($current_user->membership_level->ID))
	     $current_level = ($current_user->membership_level->ID == $level->id);
	   else
	     $current_level = false; ?>
      <section class="author-info">
	<h2><?php echo $current_level ? "<strong>{$level->name}: </strong>" : $level->name . ": "; ?>
          <span style="color: #cd0074">
            <?php
	          if(pmpro_isLevelFree($level)) 
	          {
                    _e('Free', 'pmpro');
                  } 
	          else 
	          { 
	            echo pmpro_getLevelCost($level);
	          }
	    ?></span>
        </h2>
        <div class="pmpro_level-price-select">

	  <p class="pmpro_level-select">
	    <?php if(empty($current_user->membership_level->ID)) { ?>
	      <a class="pmpro_btn pmpro_btn-select" href="<?php echo pmpro_url("checkout", "?level=" . $level->id, "https")?>"><?php _e('Select', 'Choose a level from levels page', 'pmpro');?></a>               
	    <?php } elseif ( !$current_level ) { ?>                	
	    <a class="pmpro_btn pmpro_btn-select"href="<?php echo pmpro_url("checkout", "?level=" . $level->id, "https")?>"><?php _e('Select', 'Choose a level from levels page', 'pmpro');?></a>       			
    <?php 
    } elseif($current_level) { 
      //if it's a one-time-payment level, offer a link to renew                               
      if(!pmpro_isLevelRecurring($current_user->membership_level))
      {
    ?>
      <a class="pmpro_btn pmpro_btn-select" href="<?php echo pmpro_url("checkout", "?level=" . $level->id, "https")?>"><?php _e('Renew', 'pmpro');?></a>
      <?php
      }
      else
      {
      ?>         
        <a class="pmpro_btn disabled"href="<?php echo pmpro_url("account")?>"><?php _e('Your&nbsp;Level', 'pmpro');?></a>
        <?php 
        } 
        ?>
	  </p>
        </div>
        <div class="description">
	  <?php 
	  if(!empty($level->description))
	    echo apply_filters("the_content", stripslashes($level->description));
	  ?>			
        </div>
      </section>
      <hr class="separator" />
      <?php
      }
      ?>
      <nav id="nav-below" class="navigation" role="navigation">
	<div class="nav-previous alignleft">
	  <?php if(!empty($current_user->membership_level->ID)) { ?>
	    <a href="<?php echo pmpro_url("account")?>"><?php _e('&larr; Return to Your Account', 'pmpro');?></a>
	  <?php } else { ?>
	  <a href="<?php echo home_url()?>"><?php _e('&larr; Return to Home', 'pmpro');?></a>
          <?php } ?>
	</div>
      </nav>
  </div>  
  <!-- end #pmpro_levels -->
