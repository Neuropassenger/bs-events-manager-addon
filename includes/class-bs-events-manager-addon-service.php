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
}