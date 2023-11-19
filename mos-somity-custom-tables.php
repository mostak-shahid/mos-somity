<?php
if (!function_exists('create_necessary_mos_somity_table')){
    function create_necessary_mos_somity_table () {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_name = $wpdb->prefix.'mos_deposits';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,   
            user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0, 
            skim_id bigint(20) UNSIGNED NOT NULL DEFAULT 0, 
            photo varchar(255) DEFAULT '' NOT NULL,
            source varchar(255) DEFAULT '' NOT NULL,
            amount bigint(20) UNSIGNED NOT NULL DEFAULT 0, 
            approved_by bigint(20) UNSIGNED NOT NULL DEFAULT 0, 
            apply_date date DEFAULT '0000-00-00' NOT NULL,
            approved_date date DEFAULT '0000-00-00' NOT NULL,
            comment longtext NOT NULL,
            status varchar(20) DEFAULT '' NOT NULL,
            PRIMARY KEY  (ID)
        ) $charset_collate;";
        dbDelta( $sql );
        
        /*$table_name = $wpdb->prefix.'mos_skims';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,   
            title varchar(255) DEFAULT '' NOT NULL,
            rate int(255) DEFAULT '' NOT NULL,
            time_frame varchar(255) DEFAULT '' NOT NULL,
            p_amount varchar(255) DEFAULT '' NOT NULL,
            p_type varchar(255) DEFAULT '' NOT NULL,
            user_group date DEFAULT '0000-00-00' NOT NULL,
            status varchar(20) DEFAULT '' NOT NULL,
            PRIMARY KEY  (ID)
        ) $charset_collate;";
        dbDelta( $sql );*/
        
        $table_name = $wpdb->prefix.'mos_skim_user';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,   
            user_id bigint(20) UNSIGNED NOT NULL DEFAULT 0, 
            status varchar(20) DEFAULT '' NOT NULL,
            skim_details longtext NOT NULL,
            apply_date date DEFAULT '0000-00-00' NOT NULL,
            approved_date date DEFAULT '0000-00-00' NOT NULL,
            end_date date DEFAULT '0000-00-00' NOT NULL,
            PRIMARY KEY  (ID)
        ) $charset_collate;";
        dbDelta( $sql );
    }
}
add_action('after_setup_theme', 'create_necessary_mos_somity_table');