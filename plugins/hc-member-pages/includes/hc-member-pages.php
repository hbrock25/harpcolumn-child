<?php 

/* Main controller -- pages and functions */

function hc_members_list_page_html() {

/* Define the actions for the main page */

    // check user capabilities
    if (!current_user_can('manage_options') ||
	! current_user_can('pmpro_memberslist')) {
	return;
    }

    // vars
    global $wpdb, $pmpro_currency_symbol, $woocommerce;

    // headers first
    require_once(PMPRO_DIR . "/adminpages/admin_header.php");

    if(!empty($_REQUEST['user_id'])) {
	$user_id = $_REQUEST['user_id'];
	
	// get the vars
	if(isset($_REQUEST['s']))
	    $s = $_REQUEST['s'];
	else
	    $s = "";

	
	// any actions?

	if(!empty($_REQUEST['copy_baddr_to_woo_bill'])) {
	    // copy the pmpro billing address to woocommerce
	    copy_bill_addr_pmpro_to_woo_bill($user_id);
	} elseif (!empty($_REQUEST['copy_baddr_to_woo_ship'])) {
	    // copy the pmpro billing address to woocommerce ship
	    copy_bill_addr_pmpro_to_woo_ship($user_id);
	} elseif (!empty($_REQUEST['copy_baddr_to_woo_both'])) {
	    // copy the pmpro billing address to woocommerce ship
	    copy_bill_addr_pmpro_to_woo_both($user_id);
	} elseif (!empty($_REQUEST['copy_saddr_to_woo_ship'])) {
	    // copy the pmpro shipping address to woocommerce
	    copy_ship_addr_pmpro_to_woo_ship($user_id);
	} elseif(!empty($_REQUEST['copy_saddr_to_woo_bill'])) {
	    // copy the pmpro shipping address to woocommerce bill
	    copy_ship_addr_pmpro_to_woo_bill($user_id);
	} elseif(!empty($_REQUEST['copy_saddr_to_woo_both'])) {
	    // copy the pmpro shipping address to woocommerce bill
	    copy_ship_addr_pmpro_to_woo_both($user_id);
	}

	
	// get the data from the model
	$pmbaddr = pretty_pmpro_billing_address( $user_id );
	$pmsaddr = pretty_pmpro_shipping_address( $user_id );
	$woobaddr = pretty_woo_billing_address( $user_id );
	$woosaddr = pretty_woo_shipping_address( $user_id );

	
	
	// show the page
	require( HC_ML_PLUGIN_PATH . '/views/one-user.php' );
    } else {

	// returns $s, $l, $pn, $limit, $start, $end
	extract(hcml_parse_request($_REQUEST));
	$theusers = get_members($l, $s, $limit, $start);
	$totalrows = get_rowcount_last_query();
	$levels = get_levels();

	// first the choose-level form
	require(HC_ML_PLUGIN_PATH . '/views/list-users-form.php');
	// now the main page
	require(HC_ML_PLUGIN_PATH . '/views/list-users.php');
	// and the paginator
	require(HC_ML_PLUGIN_PATH . '/views/list-users-pagination.php');
    }
    require_once(PMPRO_DIR . "/adminpages/admin_footer.php");
}

function hc_members_list_page() {

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

/* responds to an ajax request for a csv of the current members list */

function hcml_wp_ajax_hc_memberslist_csv() {
    // check user capabilities
    if (!current_user_can('manage_options') ||
	! current_user_can('pmpro_memberslist')) {
	return;
    }
    global $wpdb, $pmpro_currency_symbol, $woocommerce;
    // returns $s, $l, $pn, $limit, $start, $end
    extract(hcml_parse_request($_REQUEST, "list-users-csv"));
    $theusers = get_members($l, $s, $limit, $start);
    require_once( HC_ML_PLUGIN_PATH . '/views/list-users-csv.php');	
    exit;	

}
add_action('wp_ajax_hc_memberslist_csv', 'hcml_wp_ajax_hc_memberslist_csv');

function hcml_parse_request($request, $page = "list-users") {
    // parse the request vars for a list page.
    /* if(isset($request['s']))
       $s = $request['s'];
     * else
       $s = false;
     */
    $s = (isset($request['s']) ? $request['s'] : false);
    
    if(isset($request['l']))
	$l = $request['l'];
    else
	$l = false;

    if(!empty($request['pn']))
	$pn = $request['pn'];
    else
	$pn = 1;

    if(!empty($request['limit'])) {
	$limit = $request['limit'];
    } else {
	if($page == "list-users") {
	    $limit = 15;
	} else {
	    $limit = false;
	}
    }
    
    if($limit) {	
	$end = $pn * $limit;
	$start = $end - $limit;		
    } else {
	$end = NULL;
	$start = NULL;
    }
    return array(
	"s" => $s,
	"l" => $l,
	"pn" => $pn,
	"limit" => $limit,
	"start" => $start,
	"end" => $end);
}
