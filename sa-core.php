<?php
/*
 * Plugin Name: SA-Core - Dashboard Plugin by CloudPixel
 * Version: 1.0.0
 * Plugin URI: https://cloudpixelinfotech.com/
 * Description: Dashboard Plugin from CloudPixel
 * Author: Cloud Pixel
 * Author URI: https://cloudpixelinfotech.com/
 * Requires at least: 4.7
 * Tested up to: 5.3
 *
 * Text Domain: sa_core
 * Domain Path: /languages/
 *
 * @package WordPress
 * @author Cloud Pixel
 * @since 1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) exit;
 
define( 'REALTEO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

define('REMINDER_LOG_SHEET_PATH', wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR);
define('REMINDER_LOG_SHEET_URL', wp_upload_dir()['baseurl']);

// Sypht Credentials
define( 'SYPHT_CLIENT_KEY', "iEJbZgERxKbkhC5pUazLCLsRPqRaPsan" );
define( 'SYPHT_CLIENT_SECRET', "cu56uQfDzp1uiMho8anVtIoM9MPy05ou3f9iPuHzOsIj0iKbD1BKCqkiYzZicooj" );

// Load plugin class files
require_once( 'includes/class-sa-core-admin.php' );
require_once( 'includes/class-sa-core.php' );

/**
 * Returns the main instance of listeo_core to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object sa_core
 */
function Sa_Core () {
	$instance = Sa_Core::instance( __FILE__, '1.0.0' );

	/*if ( is_null( $instance->settings ) ) {
		$instance->settings =  Listeo_Core_Settings::instance( $instance );
	}*/
	

	return $instance;
}
$GLOBALS['sa_core'] = Sa_Core();

/* load template engine*/
if ( ! class_exists( 'Gamajo_Template_Loader' ) ) {
	require_once dirname( __FILE__ ) . '/lib/class-gamajo-template-loader.php';
}
include( dirname( __FILE__ ) . '/includes/class-sa-core-templates.php' );

/* load sypht */
if ( ! class_exists( 'SyphtClass' ) ) {
	require_once dirname( __FILE__ ) . '/lib/sypht.class.php';
}

Sa_Core();