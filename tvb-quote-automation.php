<?php
/**
 * Plugin Name: TVB - Quote Automation
 * Description: Quote Automation using Infusionsoft
 * Version: 1.0.0
 * Author: FusedSoftware
 * Author URI:
 * Text Domain: tvb_quote_automation
 *
 *
 * FusedSoftware
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Currently plugin version.
 */
define( 'TVB_QUOTE_AUTOMATION_VERSION', '1.0.0' );


if( !defined( 'TVB_QUOTE_AUTOMATION_DIR' ) ) {
    define( 'TVB_QUOTE_AUTOMATION_DIR', dirname( __FILE__ ) ); // plugin dir
}

if( !defined( 'TVB_QUOTE_AUTOMATION_URL' ) ) {
    define( 'TVB_QUOTE_AUTOMATION_URL', plugin_dir_url( __FILE__ ) ); // plugin url
}

/**
 * Activation hook
 *
 */
register_activation_hook( __FILE__, 'activate_tvb_quote_automation' );

/**
 * Plugin Setup (On Activation)
 *
 */
function activate_tvb_quote_automation() {

    $tvb_version = get_option( 'tvb_quote_automation_version' );
    if( empty($tvb_version) ) {
        update_option( 'tvb_quote_automation_version', '1.0.0' );
    }

    $tvb_version = get_option( 'tvb_quote_automation_version' );
    if( $tvb_version == '1.0.0' ) {
        // feature update
    }
}

/**
 * Plugin Deactivation Hook
 *
 */
register_deactivation_hook( __FILE__, 'deactivate_tvb_quote_automation' );

/**
 * Deactivation function
 */
function deactivate_tvb_quote_automation(){

}

add_action( 'load_plugins', 'tvb_quote_automation_load_plugin_textdomain' );
/**
 * Load Text Domain
 *
 * This gets the plugin ready for translation.
 *
 * @package CBC Quote Automation
 * @since 1.0.0
 */
function tvb_quote_automation_load_plugin_textdomain() {
    load_plugin_textdomain( 'tvb_quote_automation', false, TVB_QUOTE_AUTOMATION_DIR . '/languages/' );
}

add_action( 'plugins_loaded', 'tvb_load_required_files', 999 );

/**
 * load required plugin files
 */
function tvb_load_required_files(){
    require_once (TVB_QUOTE_AUTOMATION_DIR .'/includes/config/default_settings.php');
    require_once( TVB_QUOTE_AUTOMATION_DIR . '/includes/functions.php' );

    require_once (TVB_QUOTE_AUTOMATION_DIR .'/includes/infusionsoft/infusionsoftAPI/src/isdk.php'); //load the infusionsoft api library
    require_once (TVB_QUOTE_AUTOMATION_DIR .'/includes/class-infusionsoft-request.php');
    require_once (TVB_QUOTE_AUTOMATION_DIR .'/includes/class-tvb-uploadexception.php');


    //admin functions file
    require_once TVB_QUOTE_AUTOMATION_DIR . '/admin/class-tvb-quote-automation-admin.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    require_once TVB_QUOTE_AUTOMATION_DIR . '/public/class-tvb-quote-automation-public.php';

}
