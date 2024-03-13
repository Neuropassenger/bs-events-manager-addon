<?php
class Bs_Events_Manager_Addon_Booking_Helper {
    public static function get_gateway_title_by_system_slug( $system_slug ) {

        switch ( $system_slug ) {
            case 'offline':
                $gateway_title = get_option( 'em_offline_option_name' );
                break;
            case 'direct_deposit':
                $gateway_title = get_option( 'em_direct_deposit_option_name' );
                break;
            case 'paypal':
                $gateway_title = get_option( 'em_paypal_option_name' );
                break;
            default:
                $gateway_title = __( 'none', 'bs-events-manager-addon' );
        }

        return $gateway_title;
    }
}