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

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Access denied.' );
}

define( 'HCML_NAME','HC Member List Plugin' );
define( 'HC_ML_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'HC_MEMBER_PAGE_SLUG', "hc-members-list" );
define( 'HCML_REQUIRED_PHP_VERSION', '5.3' ); // because of get_called_class()
define( 'HCML_REQUIRED_WP_VERSION',  '3.1' ); // because of esc_textarea()


/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */

function HCML_requirements_met() {
    global $wp_version;
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' ); // to get is_plugin_active() early
    if ( version_compare( PHP_VERSION, HCML_REQUIRED_PHP_VERSION, '<' ) ) {
	return false;
    }
    if ( version_compare( $wp_version, HCML_REQUIRED_WP_VERSION, '<' ) ) {
	return false;
    }
    
    if ( ! is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' ) ) {
	return false;
    }
	
    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	return false;
    }
    return true;
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */

if ( wpps_requirements_met() ) {
    //vars
    global $wpdb, $pmpro_currency_symbol, $woocommerce;
    require_once( __DIR__ . '/includes/user-addresses.php' );
    require_once( __DIR__ . '/includes/hc-member-pages.php' );
} else {
    die("Plugin requirements not met");
}

