<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="tml tml-register" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php $template->the_action_template_message( 'register' ); ?>
	<?php $template->the_errors(); ?>
	<?php $template->the_action_links( array( 'register' => false ) ); ?>

  <h1>To register for Harp Column, please visit:</h1>
  <p><a href="/membership-account/subscribe/">The subscription page.</a></p>
</div>
