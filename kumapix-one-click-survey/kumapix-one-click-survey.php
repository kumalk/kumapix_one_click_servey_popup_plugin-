<?php
/**
 * Plugin Name:       KumaPix One Click Survey
 * Plugin URI:        https://kumapix.com/kumapix-one-click-survey
 * Description:       A simple, modern, and mobile-friendly popup survey to collect quick feedback from your website visitors on exit intent.
 * Version:           1.0.0
 * Author:            Prashantha Kumanayake
 * Author URI:        https://kumapix.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kocs
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define Plugin Constants
define( 'KOCS_VERSION', '1.0.0' );
define( 'KOCS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KOCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_kocs() {
	require_once KOCS_PLUGIN_DIR . 'includes/class-kocs-activator.php';
	KOCS_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_kocs() {
	require_once KOCS_PLUGIN_DIR . 'includes/class-kocs-deactivator.php';
	KOCS_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_kocs' );
register_deactivation_hook( __FILE__, 'deactivate_kocs' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require KOCS_PLUGIN_DIR . 'includes/class-kocs.php';

/**
 * Begins execution of the plugin.
 */
function run_kocs() {
	$plugin = new KOCS();
	$plugin->run();
}

run_kocs();
