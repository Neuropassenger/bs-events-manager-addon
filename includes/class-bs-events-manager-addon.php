<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://neuropassenger.ru/
 * @since      1.0.0
 *
 * @package    Bs_Events_Manager_Addon
 * @subpackage Bs_Events_Manager_Addon/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Bs_Events_Manager_Addon
 * @subpackage Bs_Events_Manager_Addon/includes
 * @author     Oleg Sokolov <turgenoid@gmail.com>
 */
class Bs_Events_Manager_Addon {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Bs_Events_Manager_Addon_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'BS_EVENTS_MANAGER_ADDON_VERSION' ) ) {
			$this->version = BS_EVENTS_MANAGER_ADDON_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'bs-events-manager-addon';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Bs_Events_Manager_Addon_Loader. Orchestrates the hooks of the plugin.
	 * - Bs_Events_Manager_Addon_i18n. Defines internationalization functionality.
	 * - Bs_Events_Manager_Addon_Admin. Defines all hooks for the admin area.
	 * - Bs_Events_Manager_Addon_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

        $addon_settings = get_option( 'bs_em_addon_settings' );

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bs-events-manager-addon-service.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bs-events-manager-addon-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bs-events-manager-addon-i18n.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-bs-events-manager-addon-booking-helper.php';

        require_once plugin_dir_path( dirname(__FILE__ ) ) . 'includes/class-em-gateways-transactions.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-bs-events-manager-addon-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-bs-events-manager-addon-public.php';

		$this->loader = new Bs_Events_Manager_Addon_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Bs_Events_Manager_Addon_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Bs_Events_Manager_Addon_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
        $addon_settings = get_option( 'bs_em_addon_settings' );

		$plugin_admin = new Bs_Events_Manager_Addon_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        // Events Manager Modifications

        if ( isset( $addon_settings['gateway_placeholder'] ) && $addon_settings['gateway_placeholder'] == 'on' ) {
            $this->loader->add_action( 'em_event_output_placeholder', $plugin_admin, 'add_payment_gateway_placeholder', 1, 3 );
        }

        if ( isset( $addon_settings['booking_attendee_info'] ) && $addon_settings['booking_attendee_info'] == 'on' ) {
            $this->loader->add_filter( 'em_bookings_table_rows_col_user_name', $plugin_admin, 'change_bookings_user_name_col', 10, 5 );
            $this->loader->add_action( 'em_bookings_single_footer', $plugin_admin, 'show_bookings_list_section', 100 );
        }

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'addon_em_submenu', 20 );
        $this->loader->add_action( 'bs_events_manager_addon/render_email_templates_forms_after', $plugin_admin, 'render_awaiting_payment_table', 10, 2 );

        if ( $addon_settings['dynamic_email_templates'] == 'on' ) {
            $this->loader->add_action('wp_ajax_get_em_payment_gateways', $plugin_admin, 'ajax_em_payment_methods');
            $this->loader->add_action('wp_ajax_generate_single_email_template', $plugin_admin, 'ajax_generate_single_email_template');
        }

		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'check_plugin_updates' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Bs_Events_Manager_Addon_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		// The plugin should only work with Events Manager version older than 3.2
		$upper_version = Bs_Events_Manager_Addon_Service::is_plugin_version_greater_than( 'events-manager-pro/events-manager-pro.php', '3.2' );
		if ( ! $upper_version ) return;
		
		// and New Gateways API
        $dbem_settings = get_option( 'dbem_data' );
        if ( empty( $dbem_settings ) ) return;
        $legacy = $dbem_settings['legacy-gateways'] ?? false;
        $legacy_constant = defined( 'EMP_GATEWAY_LEGACY' ) ? EMP_GATEWAY_LEGACY : null;
		$legacy = $legacy || $legacy_constant;
		if ( $legacy ) return;
		
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Bs_Events_Manager_Addon_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
