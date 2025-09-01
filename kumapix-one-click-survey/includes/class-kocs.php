<?php
/**
 * The file that defines the core plugin class
 *
 * @link       https://kumapix.com
 * @since      1.0.0
 *
 * @package    Kocs
 * @subpackage Kocs/includes
 */

class KOCS {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		if ( defined( 'KOCS_VERSION' ) ) {
			$this->version = KOCS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'kumapix-one-click-survey';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once KOCS_PLUGIN_DIR . 'includes/class-kocs-loader.php';
		require_once KOCS_PLUGIN_DIR . 'admin/class-kocs-admin.php';
		require_once KOCS_PLUGIN_DIR . 'public/class-kocs-public.php';
		$this->loader = new KOCS_Loader();
	}

	private function define_admin_hooks() {
		$plugin_admin = new KOCS_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'register_and_build_fields' );
        $this->loader->add_action( 'wp_ajax_kocs_export_csv', $plugin_admin, 'export_submissions_csv' );
	}

	private function define_public_hooks() {
		$plugin_public = new KOCS_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'wp_footer', $plugin_public, 'render_survey_popup' );
        $this->loader->add_action( 'wp_ajax_kocs_submit_survey', $plugin_public, 'handle_survey_submission' );
        $this->loader->add_action( 'wp_ajax_nopriv_kocs_submit_survey', $plugin_public, 'handle_survey_submission' );
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}
}
