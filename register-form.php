<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="tml tml-register" id="theme-my-login<?php $template->the_instance(); ?>">
  <h1>To register for Harp Column, please visit: <a href="/membership-account/subscribe/">The subscription page.</a></h1>
	<?php $template->the_action_links( array( 'register' => false ) ); ?>

</div>
