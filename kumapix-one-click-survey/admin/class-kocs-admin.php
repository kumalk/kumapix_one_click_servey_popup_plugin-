<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://kumapix.com
 * @since      1.0.0
 *
 * @package    Kocs
 * @subpackage Kocs/admin
 */
class KOCS_Admin {

	private $plugin_name;
	private $version;
    private $options;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->options = get_option('kocs_options');
	}

	public function enqueue_styles( $hook ) {
        if ( 'toplevel_page_kocs-survey' !== $hook ) {
            return;
        }
		wp_enqueue_style( $this->plugin_name, KOCS_PLUGIN_URL . 'admin/css/kocs-admin.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'wp-color-picker' );
	}

	public function enqueue_scripts( $hook ) {
        if ( 'toplevel_page_kocs-survey' !== $hook ) {
            return;
        }
		wp_enqueue_script( $this->plugin_name, KOCS_PLUGIN_URL . 'admin/js/kocs-admin.js', array( 'jquery', 'wp-color-picker', 'jquery-ui-datepicker' ), $this->version, false );
        wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.0', true );
        wp_localize_script( $this->plugin_name, 'kocs_chart_data', $this->get_chart_data() );
        wp_enqueue_style('jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css');
	}

    public function add_plugin_admin_menu() {
        add_menu_page(
            'KumaPix One Click Survey',
            'One Click Survey',
            'manage_options',
            'kocs-survey',
            array( $this, 'display_plugin_setup_page' ),
            'dashicons-chart-pie',
            26
        );
    }

    public function display_plugin_setup_page() {
        include_once( 'partials/kocs-admin-display.php' );
    }

    public function register_and_build_fields() {
        // Register settings
        $settings = [
            'kocs_enabled', 'kocs_question', 'kocs_answers', 'kocs_trigger',
            'kocs_trigger_delay', 'kocs_bg_color', 'kocs_text_color',
            'kocs_btn_color', 'kocs_btn_text_color'
        ];
        foreach($settings as $setting) {
            register_setting('kocs_option_group', $setting);
        }
    }

    private function get_chart_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'kocs_submissions';
        $results = $wpdb->get_results( "SELECT answer, COUNT(id) as count FROM {$table_name} GROUP BY answer", ARRAY_A );

        $labels = [];
        $data = [];
        if($results) {
            foreach($results as $row) {
                $labels[] = $row['answer'];
                $data[] = (int) $row['count'];
            }
        }

        return ['labels' => $labels, 'data' => $data];
    }

    public function export_submissions_csv() {
        check_ajax_referer('kocs_export_nonce', 'nonce');

        global $wpdb;
        $table_name = $wpdb->prefix . 'kocs_submissions';

        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';

        $query = "SELECT * FROM {$table_name}";
        $conditions = [];
        if(!empty($start_date)) {
            $conditions[] = $wpdb->prepare("submission_time >= %s", $start_date . ' 00:00:00');
        }
        if(!empty($end_date)) {
            $conditions[] = $wpdb->prepare("submission_time <= %s", $end_date . ' 23:59:59');
        }
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $results = $wpdb->get_results($query, ARRAY_A);

        if (!$results) {
             wp_send_json_error('No data to export for the selected period.');
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="kocs-submissions-'.date('Y-m-d').'.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('ID', 'Submission Time', 'Question', 'Answer', 'Country', 'City'));

        foreach ($results as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        wp_die();
    }
}
