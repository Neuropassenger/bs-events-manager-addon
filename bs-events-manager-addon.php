<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://neuropassenger.ru/
 * @since             1.0.0
 * @package           Bs_Events_Manager_Addon
 *
 * @wordpress-plugin
 * Plugin Name:       BS Events Manager Add-On
 * Plugin URI:        https://github.com/Neuropassenger/bs-events-manager-addon/
 * Description:       The plugin adds the ability to view booking details when opening a user profile via a link from the list of transactions. Adds a placeholder to display a payment gateway. Improves readability for payment gateway names in the transaction list. Adds functionality to quickly and automatically generate Custom Email Templates.
 * Version:           1.3.5
 * Author:            Oleg Sokolov
 * Author URI:        https://neuropassenger.ru/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bs-events-manager-addon
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BS_EVENTS_MANAGER_ADDON_VERSION', '1.3.5' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bs-events-manager-addon-activator.php
 */
function activate_bs_events_manager_addon() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bs-events-manager-addon-activator.php';
	Bs_Events_Manager_Addon_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bs-events-manager-addon-deactivator.php
 */
function deactivate_bs_events_manager_addon() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bs-events-manager-addon-deactivator.php';
	Bs_Events_Manager_Addon_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bs_events_manager_addon' );
register_deactivation_hook( __FILE__, 'deactivate_bs_events_manager_addon' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bs-events-manager-addon.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_bs_events_manager_addon() {

	$plugin = new Bs_Events_Manager_Addon();
	$plugin->run();

}
run_bs_events_manager_addon();
