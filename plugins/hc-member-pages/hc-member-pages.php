<?php
/*
   Plugin Name: Paid Memberships Pro: HC Member Admin Pages
   Plugin URI: http://www.harpcolumn.com
   Description: Member list and CSV exports for HC/PMPro
   Version: 1.0
   Requires: 4.5.3
   Author: Hugh Brock <hbrock@harpcolumn.com>
   Author URI: http://www.harpcolumn.com
 */

define( 'HC_ML_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'HC_MEMBER_PAGE_SLUG', "hc-members-list" );

function hc_members_list_page_html() {
    //make sure PMPro is active
    if(!function_exists('pmpro_getMembershipLevelForUser')) {
	return;
    }
    // check user capabilities
    if (!current_user_can('manage_options') ||
	! current_user_can('pmpro_memberslist')) {
        return;
    }

    //vars
    global $wpdb, $pmpro_currency_symbol, $woocommerce;
    
    // headers first
    require_once(PMPRO_DIR . "/adminpages/admin_header.php");
    
    if(!empty($_REQUEST['user_id'])) {
	$user_id = $_REQUEST['user_id'];
	require( HC_ML_PLUGIN_PATH . '/views/one-user.php' );
    } else {
	require( HC_ML_PLUGIN_PATH . '/memberslist.php' );
    }
    require_once(PMPRO_DIR . "/adminpages/admin_footer.php");
}

function hc_members_list_page()
{
    //make sure PMPro is active
    if(!function_exists('pmpro_getMembershipLevelForUser'))
	return;

    add_submenu_page(
        'pmpro-membershiplevels',
        'Harp Column Members List',
        'Harp Column Members List',
        'pmpro_memberslist',
        HC_MEMBER_PAGE_SLUG,
        'hc_members_list_page_html'
    );
}
add_action('admin_menu', 'hc_members_list_page', 100);

function hc_members_list_admin_bar_menu() {

    global $wp_admin_bar;
    if(current_user_can('pmpro_memberslist')) {
	$wp_admin_bar->add_menu( array(
	    'id' => 'hc-members-list',
	    'parent' => 'paid-memberships-pro',
	    'title' => __( 'Harp Column Members List', 'pmpro'),
	    'href' => get_admin_url(NULL, '/admin.php?page=hc-members-list')));
    }
}
    
add_action('admin_bar_menu', 'hc_members_list_admin_bar_menu', 1001);
