<?php
/**
 * Fired during plugin activation
 *
 * @link       https://kumapix.com
 * @since      1.0.0
 *
 * @package    Kocs
 * @subpackage Kocs/includes
 */
class KOCS_Activator {

	public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kocs_submissions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            submission_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            question text NOT NULL,
            answer text NOT NULL,
            country varchar(100) DEFAULT '' NOT NULL,
            city varchar(100) DEFAULT '' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // Set default options
        $default_options = array(
            'kocs_enabled' => 1,
            'kocs_question' => 'How did you find us?',
            'kocs_answers' => "Google\nSocial Media\nFriend\nOther",
            'kocs_trigger' => 'exit_intent',
            'kocs_trigger_delay' => 5, // 5 seconds
            'kocs_bg_color' => '#ffffff',
            'kocs_text_color' => '#333333',
            'kocs_btn_color' => '#0073aa',
            'kocs_btn_text_color' => '#ffffff',
        );

        foreach ($default_options as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
	}
}
