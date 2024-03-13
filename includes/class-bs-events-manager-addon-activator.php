<?php

/**
 * Fired during plugin activation
 *
 * @link       https://neuropassenger.ru/
 * @since      1.0.0
 *
 * @package    Bs_Events_Manager_Addon
 * @subpackage Bs_Events_Manager_Addon/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Bs_Events_Manager_Addon
 * @subpackage Bs_Events_Manager_Addon/includes
 * @author     Oleg Sokolov <turgenoid@gmail.com>
 */
class Bs_Events_Manager_Addon_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        $addon_settings = get_option( 'bs_em_addon_settings' );

        $addon_settings['booking_attendee_info'] = 'off';
        $addon_settings['gateway_readable_names'] = 'off';
        $addon_settings['select_payment_gateway'] = 'off';
        $addon_settings['gateway_placeholder'] = 'off';
        $addon_settings['dynamic_email_templates'] = 'off';
        $addon_settings['collapse_email_templates'] = 'off';

        $active_gateways = \EM\Payments\Gateways::active_gateways();
        $gateway_settings = array( 'free' => array(
            'subject'   =>  '',
            'header'    =>  '',
            'footer'    =>  ''
        ) );
        foreach ( $active_gateways as $gateway ) {
            $gateway_settings[$gateway::$gateway] = array(
                'subject'   =>  '',
                'header'    =>  '',
                'footer'    =>  ''
            );
        }

        if ( !isset( $addon_settings['email_templates'] ) ) {
            $addon_settings['email_templates'] = array(
                'owner'         =>  $gateway_settings,
                'attendee'      =>  $gateway_settings,
                'location_info' =>  array(
                    'url'           =>  '',
                    'zoom_room'     =>  '',
                    'zoom_meeting'  =>  '',
                    'zoom_webinar'  =>  ''
                )
            );
        }

        update_option( 'bs_em_addon_settings', $addon_settings );
	}

}
