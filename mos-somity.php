<?php
/**
 * Plugin Name:       Mos Somity
 * Plugin URI:        http://www.mdmostakshahid.com/
 * Description:       Base of future plugin
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Md. Mostak Shahid
 * Author URI:        http://www.mdmostakshahid.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        http://www.mdmostakshahid.com/
 * Text Domain:       mos-form-pdf
 * Domain Path:       /languages
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define MOS_SOMITY_FILE.
if ( ! defined( 'MOS_SOMITY_FILE' ) ) {
	define( 'MOS_SOMITY_FILE', __FILE__ );
}
// Define MOS_SOMITY_SETTINGS.
if ( ! defined( 'MOS_SOMITY_SETTINGS' ) ) {
  //define( 'MOS_SOMITY_SETTINGS', admin_url('/edit.php?post_type=post_type&page=plugin_settings') );
	define( 'MOS_SOMITY_SETTINGS', admin_url('/admin.php?page=mos-somity') );
}

require_once ( plugin_dir_path( MOS_SOMITY_FILE ) . 'mos-somity-custom-tables.php' );
require_once ( plugin_dir_path( MOS_SOMITY_FILE ) . 'mos-somity-admin-pages.php' );

require_once('plugins/update/plugin-update-checker.php');
$pluginInit = Puc_v4_Factory::buildUpdateChecker(
	'https://raw.githubusercontent.com/mostak-shahid/update/master/mos-somity.json',
	MOS_SOMITY_FILE,
	'mos-somity'
);

add_action( 'after_setup_theme', 'mos_somity_crb_load' );
function mos_somity_crb_load() {
    require_once( 'vendor/autoload.php' );
    \Carbon_Fields\Carbon_Fields::boot();
}

function mos_somity_account_page_template( $page_template ){    
    $mos_somity_account_page = carbon_get_theme_option( 'mos_somity_account_page' );

    if ( $mos_somity_account_page[0]['id'] ==  get_the_ID() ) {
        $page_template = dirname( __FILE__ ) . '/page-template/account-page-template.php';
    }
    return $page_template;
}
add_filter( 'page_template', 'mos_somity_account_page_template' );

function mos_somity_enqueue_scripts(){
	wp_enqueue_script( 'jquery' );
	wp_enqueue_style( 'mos-somity', plugins_url( 'css/mos-somity.css', __FILE__ ) );
	wp_enqueue_script( 'mos-somity', plugins_url( 'js/mos-somity.js', __FILE__ ), array('jquery') );
}
add_action( 'wp_enqueue_scripts', 'mos_somity_enqueue_scripts' );
function mos_somity_ajax_scripts(){
	wp_enqueue_script( 'mos-somity-ajax', plugins_url( 'js/mos-somity-ajax.js', __FILE__ ), array('jquery') );
	$ajax_params = array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'ajax_nonce' => wp_create_nonce('mos_somity_verify'),
	);
	wp_localize_script( 'mos-somity-ajax', 'ajax_obj', $ajax_params );
}
add_action( 'wp_enqueue_scripts', 'mos_somity_ajax_scripts' );
add_action( 'admin_enqueue_scripts', 'mos_somity_ajax_scripts' );