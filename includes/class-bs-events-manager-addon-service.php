<?php
class Bs_Events_Manager_Addon_Service {
    public static function logit( $data, $description = '[INFO]' ) {
        $filename = WP_CONTENT_DIR . '/bs-events-manager-addon.log';
        if ( defined( 'BS_EVENTS_MANAGER_ADDON_VERSION' ) ) {
            $version = ' v. ' . BS_EVENTS_MANAGER_ADDON_VERSION;
        } else {
            $version = '';
        }

        $text = "===[ " . 'BS Events Manager Add-On' . $version . " ]===\n";
        $text .= "===[ " . $description . " ]===\n";
        $text .= "===[ " . date( 'M d Y, G:i:s', time() ) . " ]===\n";
        $text .= print_r( $data, true ) . "\n";
        $file = fopen( $filename, 'a' );
        fwrite( $file, $text );
        fclose( $file );
    }

    public static function is_plugin_version_greater_than( $plugin_path, $version = '3.2' ) {
		if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		$installed_plugins = get_plugins();
	
		if ( is_plugin_active( $plugin_path ) && isset( $installed_plugins[$plugin_path] ) ) {
			$installed_version = $installed_plugins[$plugin_path]['Version'];
			return version_compare( $installed_version, $version, '>' );
		}
	
		return false;
	}
}