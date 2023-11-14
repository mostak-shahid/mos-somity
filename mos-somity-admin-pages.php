<?php

use Carbon_Fields\Container;
use Carbon_Fields\Field;

function mos_plugin_options_page() {
	add_menu_page('Somity', 'Mos Somity', 'manage_options', 'mos-somity', 'mos_somity_options_page_html', 'dashicons-tickets', 2);
	add_submenu_page('mos-somity', 'Deposits', 'Deposits', 'manage_options', 'mos-somity', 'mos_somity_options_page_html');
	//add_submenu_page('mos-somity', 'Deposits', 'Deposits', 'manage_options', 'mos-somity-deposit', 'mos_somity_options_page_html');
}
add_action('admin_menu', 'mos_plugin_options_page');

function mos_somity_options_page_html() {
	if (! current_user_can('manage_options')) {
		return;
	}
	if (isset($_GET['settings-updated'])) {
		add_settings_error('mos_plugin_messages', 'mos_plugin_message', __('Settings Saved', 'mos_plugin'), 'updated');
	}
	settings_errors('mos_plugin_messages');
	?>
	<div class="wrap mos-plugin-wrapper">
		<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
	</div>
	<?php
}

register_activation_hook(MOS_SOMITY_FILE, 'mos_somity_activate');
 
function mos_somity_activate() {
    $mos_somity_option = array();
    // $mos_somity_option['mos_login_type'] = 'basic';
    // update_option('mos_somity_option', $mos_somity_option, false);
    add_option('mos_somity_do_activation_redirect', true);
}
 
add_action('admin_init', 'mos_somity_redirect');
function mos_somity_redirect() {
    if (get_option('mos_somity_do_activation_redirect', false)) {
        delete_option('mos_somity_do_activation_redirect');
        if(!isset($_GET['activate-multi'])){
            wp_safe_redirect(MOS_SOMITY_SETTINGS);
        }
    }
}

// Add settings link on plugin page

$plugin = plugin_basename(MOS_SOMITY_FILE); 
function mos_somity_settings_link($links) { 
  $settings_link = '<a href="'.MOS_SOMITY_SETTINGS.'">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
} 
add_filter("plugin_action_links_$plugin", 'mos_somity_settings_link');


add_action('carbon_fields_register_fields', 'mos_somity_theme_options');
function mos_somity_theme_options() {
    Container::make('theme_options', __('Settings'))
        ->set_page_parent('mos-somity')
        ->add_fields(array(
            Field::make( 'association', 'mos_somity_account_page', __( 'Account page' ) )
            ->set_types( array(
                array(
                    'type'      => 'post',
                    'post_type' => 'page',
                )
            ))
            ->set_max(1), 
            Field::make('complex', 'mos_somity_source', __('Source'))
            ->set_default_value( [['title'=>'Bank', 'number'=>'0000-0000-0000']] )
                ->add_fields(array(
                    Field::make('text', 'title', __('Title')),
                    Field::make('text', 'number', __('Account Number')),
                )),
            Field::make('complex', 'mos_somity_skim', __('Skim'))
                ->set_default_value( [['title'=>'Default', 'rate'=>'0', 'time'=>'0', 'penalty'=>'0']] )
                ->add_fields(array(
                    Field::make('text', 'title', __('Title')),
                    Field::make('text', 'rate', __('Rate')),
                    Field::make('text', 'time', __('Time (Month)')),
                    Field::make('text', 'penalty', __('Penalty')),
                )),

            Field::make( 'rich_text', 'mos_somity_notiece', __( 'Notiece for all user' ) )
       ));
}