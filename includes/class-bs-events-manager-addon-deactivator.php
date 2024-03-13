<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://neuropassenger.ru/
 * @since      1.0.0
 *
 * @package    Bs_Events_Manager_Addon
 * @subpackage Bs_Events_Manager_Addon/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Bs_Events_Manager_Addon
 * @subpackage Bs_Events_Manager_Addon/includes
 * @author     Oleg Sokolov <turgenoid@gmail.com>
 */
class Bs_Events_Manager_Addon_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
        $addon_settings = get_option( 'bs_em_addon_settings' );

        $addon_settings['booking_attendee_info'] = 'off';
        $addon_settings['gateway_readable_names'] = 'off';
        $addon_settings['gateway_placeholder'] = 'off';
        $addon_settings['dynamic_email_templates'] = 'off';
        $addon_settings['collapse_email_templates'] = 'off';

        update_option( 'bs_em_addon_settings', $addon_settings );
	}

}
