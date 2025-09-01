<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://kumapix.com
 * @since      1.0.0
 *
 * @package    Kocs
 * @subpackage Kocs/public
 */
class KOCS_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function enqueue_styles() {
        if ( ! get_option('kocs_enabled') ) return;
		wp_enqueue_style( $this->plugin_name, KOCS_PLUGIN_URL . 'public/css/kocs-public.css', array(), $this->version, 'all' );
        $this->add_inline_styles();
	}
    
    private function add_inline_styles() {
        $bg_color = sanitize_hex_color(get_option('kocs_bg_color', '#ffffff'));
        $text_color = sanitize_hex_color(get_option('kocs_text_color', '#333333'));
        $btn_color = sanitize_hex_color(get_option('kocs_btn_color', '#0073aa'));
        $btn_text_color = sanitize_hex_color(get_option('kocs_btn_text_color', '#ffffff'));

        $custom_css = "
            :root {
                --kocs-bg-color: {$bg_color};
                --kocs-text-color: {$text_color};
                --kocs-btn-color: {$btn_color};
                --kocs-btn-text-color: {$btn_text_color};
            }
        ";
        wp_add_inline_style( $this->plugin_name, $custom_css );
    }

	public function enqueue_scripts() {
        if ( ! get_option('kocs_enabled') ) return;
		wp_enqueue_script( $this->plugin_name, KOCS_PLUGIN_URL . 'public/js/kocs-public.js', array( 'jquery' ), $this->version, true );

        $answers_raw = get_option('kocs_answers', '');
        $answers = array_map('trim', explode("\n", $answers_raw));
        $answers = array_filter($answers); // Remove empty lines
        
        $localized_data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kocs_survey_nonce'),
            'trigger' => get_option('kocs_trigger', 'exit_intent'),
            'delay' => get_option('kocs_trigger_delay', 5) * 1000, // in ms
            'question' => get_option('kocs_question'),
            'answers' => $answers,
        );
        wp_localize_script( $this->plugin_name, 'kocs_params', $localized_data );
	}

    public function render_survey_popup() {
        if ( ! get_option('kocs_enabled') ) return;
        include_once 'partials/kocs-public-display.php';
    }

    public function handle_survey_submission() {
        check_ajax_referer('kocs_survey_nonce', 'nonce');

        $answer = isset($_POST['answer']) ? sanitize_text_field(wp_unslash($_POST['answer'])) : '';
        $question = isset($_POST['question']) ? sanitize_text_field(wp_unslash($_POST['question'])) : '';

        if (empty($answer) || empty($question)) {
            wp_send_json_error(array('message' => 'Invalid data.'));
            return;
        }

        // --- Location Data ---
        // This is a simplified approach. A production plugin might use a GeoIP database
        // or a service API on the server-side for more accuracy and privacy.
        $country = '';
        $city = '';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }

        // Example using a free API (rate limits may apply).
        // It's better to host your own GeoIP DB for production use.
        $response = wp_remote_get("http://ip-api.com/json/{$ip_address}?fields=country,city");
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $geo_data = json_decode(wp_remote_retrieve_body($response));
            if($geo_data && $geo_data->status === 'success') {
                $country = sanitize_text_field($geo_data->country);
                $city = sanitize_text_field($geo_data->city);
            }
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'kocs_submissions';
        $result = $wpdb->insert(
            $table_name,
            array(
                'submission_time' => current_time('mysql'),
                'question'        => $question,
                'answer'          => $answer,
                'country'         => $country,
                'city'            => $city,
            )
        );

        if ($result) {
            wp_send_json_success(array('message' => 'Thank you for your feedback!'));
        } else {
            wp_send_json_error(array('message' => 'Could not save your response.'));
        }
    }
}
