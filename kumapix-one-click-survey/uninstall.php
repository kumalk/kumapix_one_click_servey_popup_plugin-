<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://kumapix.com
 * @since      1.0.0
 *
 * @package    Kocs
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// 1. Delete plugin options from options table
$options_to_delete = array(
    'kocs_enabled',
    'kocs_question',
    'kocs_answers',
    'kocs_trigger',
    'kocs_trigger_delay',
    'kocs_bg_color',
    'kocs_text_color',
    'kocs_btn_color',
    'kocs_btn_text_color',
);

foreach ( $options_to_delete as $option_name ) {
    delete_option( $option_name );
}

// 2. Drop custom database table
global $wpdb;
$table_name = $wpdb->prefix . 'kocs_submissions';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
