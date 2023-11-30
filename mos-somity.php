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
	define( 'MOS_SOMITY_SETTINGS', admin_url('/admin.php?page=mos-somity-settings') );
}

require_once ( plugin_dir_path( MOS_SOMITY_FILE ) . 'mos-somity-custom-tables.php' );
require_once ( plugin_dir_path( MOS_SOMITY_FILE ) . 'mos-somity-admin-pages.php' );
require_once ( plugin_dir_path( MOS_SOMITY_FILE ) . 'mos-somity-admin-deposits-table.php' );
require_once ( plugin_dir_path( MOS_SOMITY_FILE ) . 'mos-somity-admin-skim-user-table.php' );

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
	wp_enqueue_style( 'bootstrap.min', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' );
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



add_action( 'init', 'mos_somity_blockusers_init' );
function mos_somity_blockusers_init() {		
	$redirect_url = (carbon_get_theme_option( 'mos_somity_account_page' ))?get_the_permalink(carbon_get_theme_option( 'mos_somity_account_page' )[0]['id']):home_url();

	if ( is_admin() && ! current_user_can( 'administrator' ) &&	! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		if (is_user_logged_in()) {
		wp_redirect( $redirect_url );
		exit;
		}
		else {
			wp_redirect( home_url() );
			exit;
		}
	}
	 
}
function handle_upload($file_name) {
	$post_id = 0;
	// You can use WP's wp_handle_upload() function:
	$file = $_FILES[$file_name];
	$file_attr = wp_handle_upload($file, array('test_form' => false));

	$attachment = array('guid' => $file_attr['url'], 'post_mime_type' => $file_attr['type'], 'post_title' => preg_replace('/\\.[^.]+$/', '', basename($file['name'])), 'post_content' => '', 'post_status' => 'inherit');
	// Adds file as attachment to WordPress
	$id = wp_insert_attachment($attachment, $file_attr['file'], $post_id);
	if (!is_wp_error($id)) {
		wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $file_attr['file']));
	}
 }


function mos_somity_upload_image($f){
	if (!function_exists('wp_generate_attachment_metadata')){
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
	}
	$image_url = 'adress img';

	$upload_dir = wp_upload_dir();

	$image_data = file_get_contents( $image_url );

	$filename = basename( $image_url );

	if ( wp_mkdir_p( $upload_dir['path'] ) ) {
	$file = $upload_dir['path'] . '/' . $filename;
	}
	else {
	$file = $upload_dir['basedir'] . '/' . $filename;
	}

	file_put_contents( $file, $image_data );

	$wp_filetype = wp_check_filetype( $filename, null );

	$attachment = array(
		'post_mime_type' => $wp_filetype['type'],
		'post_title' => sanitize_file_name( $filename ),
		'post_content' => '',
		'post_status' => 'inherit'
	);

	$attach_id = wp_insert_attachment( $attachment, $file );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
	wp_update_attachment_metadata( $attach_id, $attach_data );
}