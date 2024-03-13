<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://neuropassenger.ru/
 * @since      1.0.0
 *
 * @package    Bs_Events_Manager_Addon
 * @subpackage Bs_Events_Manager_Addon/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bs_Events_Manager_Addon
 * @subpackage Bs_Events_Manager_Addon/admin
 * @author     Oleg Sokolov <turgenoid@gmail.com>
 */
class Bs_Events_Manager_Addon_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bs_Events_Manager_Addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bs_Events_Manager_Addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/bs-events-manager-addon-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bs_Events_Manager_Addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bs_Events_Manager_Addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/bs-events-manager-addon-admin.js', array( 'jquery' ), $this->version, false );
        wp_localize_script( $this->plugin_name, 'bsAdminTranslations', array(
            'confirmDeletion'   =>  __( 'Are you sure? The current field value will be deleted.', 'bs-events-manager-addon' ),
        ) );

        $addon_settings = get_option( 'bs_em_addon_settings' );

        $screen = get_current_screen();
        if ( isset( $screen ) && $screen->base == 'post' && $screen->post_type == 'event' && $addon_settings['dynamic_email_templates'] == 'on' ) {
            wp_enqueue_script( $this->plugin_name . '-edit-event', plugin_dir_url( __FILE__ ) . 'js/bs-events-manager-addon-admin-edit-event.js', array( 'jquery' ), $this->version, false );

            $get_payment_method_nonce = wp_create_nonce( 'bs_get_payment_method_nonce' );
            $generate_email_template_nonce = wp_create_nonce( 'bs_generate_email_template_nonce' );
            wp_localize_script( $this->plugin_name . '-edit-event', 'bsEEVars', array(
                'generateAllTemplatesButton'            =>  __( 'Generate all Custom Email Templates', 'bs-events-manager-addon' ),
                'generateSingleTemplateButton'          =>  __( 'Generate this Custom Email Template', 'bs-events-manager-addon' ),
                'generateSingleTemplateButtonAlert'     =>  __( 'Are you sure? Template generation will erase existing field values.', 'bs-events-manager-addon' ),
                'generateAllTemplatesButtonAlert'       =>  __( 'Are you sure? Template generation will erase existing custom email templates. It will take about 30 seconds.', 'bs-events-manager-addon' ),
                'getPaymentMethodNonce'                 =>  $get_payment_method_nonce,
                'generateEmailTemplateNonce'            =>  $generate_email_template_nonce
            ) );

            $ajax_url = admin_url( 'admin-ajax.php' );
            wp_add_inline_script( $this->plugin_name . '-edit-event', "window.ajaxUrl = '{$ajax_url}';" );

            if ( $addon_settings['collapse_email_templates'] == 'on' ) {
                $collapse_js = "(function( $ ) {
                    'use strict';
                
                    $(window).load(function () {
                         $(\"#em-event-custom-emails div.handle-actions button.handlediv[aria-expanded='true']\").click();
                     });
                })( jQuery );";
                wp_add_inline_script( $this->plugin_name . '-edit-event', $collapse_js );
            }
        }

	}

    public function add_payment_gateway_placeholder( $content, $EM_Event, $tag ) {
        if( $tag == '#_BS_PAYMENTGATEWAY' ){
            $content = __( 'none', 'bs-events-manager-addon' );

            if ( ! get_option( 'dbem_multiple_bookings' ) ) {
                if ( isset( $_POST['booking_id'] ) ) {
                    $booking = em_get_booking( $_POST['booking_id'] );
                    $gateway_slug = $booking->booking_meta['gateway'];
	                $content = Bs_Events_Manager_Addon_Booking_Helper::get_gateway_title_by_system_slug( $gateway_slug );
                } else if ( isset( $_GET['em_payment_gateway'] ) ) {
                    $gateway_slug = sanitize_key( $_GET['em_payment_gateway'] );
                    $content = Bs_Events_Manager_Addon_Booking_Helper::get_gateway_title_by_system_slug( $gateway_slug );
                } else if ( isset( $_POST['gateway'] ) ) {
	                $gateway_slug = sanitize_key( $_POST['gateway'] );
	                $content = Bs_Events_Manager_Addon_Booking_Helper::get_gateway_title_by_system_slug( $gateway_slug );
                }
                $bookings = new EM_Bookings( $EM_Event );
                $bookings->load( true );
                $dev_booking = em_get_booking( $_REQUEST['booking_id'] );
                $user_id = get_current_user_id();
                $current_booking = $bookings->has_booking( $user_id );
                if ( isset( $current_booking->booking_meta['gateway'] ) ) {
                    $content = Bs_Events_Manager_Addon_Booking_Helper::get_gateway_title_by_system_slug( $current_booking->booking_meta['gateway'] );
                }
            }
        }

        return $content;
    }

    public function change_bookings_user_name_col( $val, $EM_Booking, $EM_Bookings_Table, $format, $object ) {
        if( $format == 'csv' ){
            $val = $EM_Booking->get_person()->get_name();
        } elseif( $EM_Booking->is_no_user() ) {
            $val = esc_html($EM_Booking->get_person()->get_name());
        } else {
            $val = '<a href="'.esc_url( add_query_arg( array(
                'person_id'     =>  $EM_Booking->person_id,
                'event_id'      =>  null,
                'booking_id'    =>  $EM_Booking->booking_id
            ), $EM_Booking->get_event()->get_bookings_url())).'">'. esc_html($EM_Booking->person->get_name()) .'</a>';
        }

        return $val;
    }

    public function show_bookings_list_section() {
        if ( ! empty( $_REQUEST['booking_id'] ) && ! empty( $_REQUEST['person_id'] ) ) {
            $this->em_short_bookings_person();
        }
    }

    /**
     * Shows all bookings made by one person.
     */
    function em_short_bookings_person() {
        global $EM_Person;
        $EM_Person->get_bookings();
        $has_booking = false;
        foreach( $EM_Person->get_bookings() as $EM_Booking ){
            if( $EM_Booking->can_manage( 'manage_bookings','manage_others_bookings' ) ) {
                $has_booking = true;
            }
        }
        if( ! $has_booking && !current_user_can( 'manage_others_bookings' ) ) {
            ?>
            <div class="wrap"><h2><?php esc_html_e( 'Unauthorized Access','events-manager' ); ?></h2><p><?php esc_html_e( 'You do not have the rights to manage this event.','events-manager' ); ?></p></div>
            <?php
            return false;
        }
        ?>
        <div class='wrap'>
            <br style="clear:both;" />
            <?php do_action( 'em_bookings_person_body_1' ); ?>
            <h2><?php esc_html_e( 'Past And Present Bookings','events-manager' ); ?></h2>
            <?php
            $EM_Bookings_Table = new EM_Bookings_Table();
            $EM_Bookings_Table->status = 'all';
            $EM_Bookings_Table->scope = 'all';
            $EM_Bookings_Table->output();
            ?>
            <?php //do_action('em_bookings_person_footer', $EM_Person); ?>
        </div>
        <?php
    }

    public function addon_em_submenu() {
        $plugin_page = add_submenu_page(
                'edit.php?post_type='.EM_POST_TYPE_EVENT,
                'BS Events Manager Add-On Settings',
                'BS Add-On',
                'edit_events',
                'bs_em_addon_settings',
                array( $this, 'bs_em_addon_settings_page' )
        );
    }

    function bs_em_addon_settings_page() {
        global $EM_Notices, $addon_settings, $dbem_location_types;

        $addon_settings = is_array( get_option( 'bs_em_addon_settings' ) ) ? get_option( 'bs_em_addon_settings' ) : array();
        $dbem_location_types = is_array( get_option( 'dbem_location_types' ) ) ? get_option( 'dbem_location_types' ) : array();

        if ( key_exists( 'location', $dbem_location_types ) ) {
            unset( $dbem_location_types['location'] );
        }

        if ( !empty( $_REQUEST['action'] ) ) {
            if ( $_REQUEST['action'] == 'settings_save' && wp_verify_nonce( $_REQUEST['bs_em_addon_settings_nonce'], 'settings_save' ) ) {
                // Sanitize
                $input_settings = $_REQUEST['bs_em_addon_settings'];
                $input_settings = $this->sanitize_settings_array( $input_settings );

                update_option( 'bs_em_addon_settings', $input_settings );
                $addon_settings = $input_settings;
                $EM_Notices->add_confirm( __( 'Settings Saved', 'bs-events-manager-addon' ) );
            }
        }

        echo '<div class="wrap">';
        echo '<h2>'.get_admin_page_title().'</h2>';
        $EM_Notices->display();
        ?>
        <div class="bs_settings-page-container">
            <div class="bs_settings-page-content">
                <form method="post" class="bs_em-addon-settings-form">
                    <input type='hidden' name='action' value='settings_save'>
                    <input type='hidden' name='bs_em_addon_settings_nonce' value='<?php echo wp_create_nonce( 'settings_save' ); ?>'>
                    <?php
                    $this->render_addon_settings_tweaks_section();
                    submit_button();
                    $this->render_addon_settings_placeholders_section();
                    submit_button();
                    $this->render_addon_settings_email_templates_section();
                    ?>
                </form>
            </div>
            <div class="bs_settings-page-navigation">
                <h3><?php _e( 'Navigation', 'bs-events-manager-addon' ); ?></h3>
                <ul>
                    <li><a class="bs_settings-page-navigation-link" href="#bs_settings-page-tweaks-section"><?php _e( 'Tweaks', 'bs-events-manager-addon' ); ?></a></li>
                    <li><a class="bs_settings-page-navigation-link" href="#bs_settings-page-placeholders-section"><?php _e( 'Placeholders', 'bs-events-manager-addon' ); ?></a></li>
                    <li><a class="bs_settings-page-navigation-link" href="#bs_settings-page-email-templates-section"><?php _e( 'Email Templates', 'bs-events-manager-addon' ); ?></a>
                        <ul>
                            <?php $this->render_location_info_for_menu(); ?>
                            <li><a class="bs_settings-page-navigation-link" href="#bs_settings-page-free-emails-section"><?php _e( 'Default or Free Booking Emails', 'bs-events-manager-addon' ); ?></a></li>
                            <?php $this->render_payment_gateways_for_menu(); ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
        <?php
        echo '</div>';
    }

    function render_addon_settings_tweaks_section() {
        global $addon_settings;
        ?>
        <h3 id="bs_settings-page-tweaks-section"><?php _e( 'Tweaks', 'bs-events-manager-addon' ); ?></h3>
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'Booking and Attendee Information', 'bs-events-manager-addon' ); ?>
                </th>
                <td>
                    <input name="bs_em_addon_settings[booking_attendee_info]" type="checkbox" <?php checked( $addon_settings['booking_attendee_info'], 'on' ); ?>>
                    <p>
                        <?php _e( 'Display Booking and Attendee information when opening a user profile via a link from the list of transactions.', 'bs-events-manager-addon' ); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e( 'Readable names of payment gateways', 'bs-events-manager-addon' ); ?>
                </th>
                <td>
                    <input name="bs_em_addon_settings[gateway_readable_names]" type="checkbox" <?php checked( $addon_settings['gateway_readable_names'], 'on' ); ?>>
                    <p>
                        <?php _e( 'Display in the list of transactions the names of payment gateways from the settings instead of the system name.', 'bs-events-manager-addon' ); ?>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    function render_addon_settings_placeholders_section() {
        global $addon_settings;
        ?>
        <h3 id="bs_settings-page-placeholders-section"><?php _e( 'Placeholders', 'bs-events-manager-addon' ); ?></h3>
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'Payment gateway placeholder', 'bs-events-manager-addon' ); ?>
                </th>
                <td>
                    <input name="bs_em_addon_settings[gateway_placeholder]" type="checkbox" <?php checked( $addon_settings['gateway_placeholder'], 'on' ); ?>>
                    <p>
                        <?php _e( 'Enable placeholder for payment gateway output in emails. Use #_BS_PAYMENTGATEWAY.', 'bs-events-manager-addon' ); ?>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    function render_addon_settings_email_templates_section() {
        global $addon_settings; ?>
        <h3 id="bs_settings-page-email-templates-section"><?php _e( 'Email Templates', 'bs-events-manager-addon' ); ?></h3>
        <table class="form-table bs_email-templates-checkboxes" role="presentation">
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'Enable dynamic email template generation', 'bs-events-manager-addon' ); ?>
                </th>
                <td>
                    <input name="bs_em_addon_settings[dynamic_email_templates]" type="checkbox" <?php checked( $addon_settings['dynamic_email_templates'], 'on' ); ?>>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e( 'Collapse email templates', 'bs-events-manager-addon' ); ?>
                </th>
                <td>
                    <input name="bs_em_addon_settings[collapse_email_templates]" type="checkbox" <?php checked( $addon_settings['collapse_email_templates'], 'on' ); ?>>
                    <p>
                        <?php _e( 'Automatically collapse the section with email templates when editing an event.', 'bs-events-manager-addon' ); ?>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
        <?php submit_button(); ?>

        <?php $this->render_location_info_forms(); ?>

        <h3 id="bs_settings-page-free-emails-section" class="bs_email-template-header"><?php _e( 'Default or Free Booking Emails', 'bs-events-manager-addon' ); ?></h3>
        <p><?php _e( 'Enter email subject lines. Specify Header content and Footer content for emails. Subsequently, generated content with Events Manager placeholders will be inserted between the Header and Footer based on the selected Booking and Attendee forms.', 'bs-events-manager-addon' ); ?></p>

        <!-- OWNER -->
        <h4 class="bs_owner-header"><?php _e( 'Owner', 'bs-events-manager-addon' ); ?></h4>
        <?php $this->render_email_templates_forms( 'owner', 'free' ); ?>

        <!-- ATTENDEE -->
        <h4 class="bs_attendee-header"><?php _e( 'Attendee', 'bs-events-manager-addon' ); ?></h4>
        <?php $this->render_email_templates_forms( 'attendee', 'free' ); ?>

        <?php
        $active_gateways = \EM\Payments\Gateways::active_gateways();
        foreach ( $active_gateways as $key => $gateway ) : ?>

            <h3 id="bs_settings-page-<?php echo $gateway::$gateway; ?>-emails-section" class="bs_email-template-header"><?php echo $gateway::$title . ' ' .  __( 'Gateway Emails', 'bs-events-manager-addon' ); ?></h3>
            <p><?php _e( 'Enter email subject lines. Specify Header content and Footer content for emails. Subsequently, generated content with Events Manager placeholders will be inserted between the Header and Footer based on the selected Booking and Attendee forms.', 'bs-events-manager-addon' ); ?></p>

            <!-- OWNER -->
            <h4 class="bs_owner-header"><?php _e( 'Owner', 'bs-events-manager-addon' ); ?></h4>
            <?php $this->render_email_templates_forms( 'owner', $gateway::$gateway ); ?>

            <!-- ATTENDEE -->
            <h4 class="bs_attendee-header"><?php _e( 'Attendee', 'bs-events-manager-addon' ); ?></h4>
            <?php $this->render_email_templates_forms( 'attendee', $gateway::$gateway ); ?>

        <?php endforeach;
    }

    function sanitize_settings_array( $array ) {
        foreach ( $array as $key => $item ) {
            if ( is_array( $item ) ) {
                $array[$key] = $this->sanitize_settings_array( $item );
            } else if ( $key === 'subject' ) {
                $array[$key] = sanitize_text_field( $item );
            } else {
                $array[$key] = $this->sanitize_setting_single( $item );
            }
        }

        return $array;
    }

    function sanitize_setting_single( $setting ) {
        return wp_kses( $setting, array(
                'a'         =>  array(
                    'href'  =>  array(),
                    'title' =>  array()
                ),
                'br'        =>  array(),
                'em'        =>  array(),
                'strong'    =>  array(),
                'b'         =>  array()
            )
        );
    }

    function render_field_buttons( $buttons ) {
        ?>
            <p>
                <?php if ( in_array( 'copy', $buttons ) ) : ?>
                <button class="button bs_copy_field_from_free_template_button">
                    <?php _e( 'Copy from the Free Booking Emails section', 'bs-events-manager-addon' ); ?>
                </button>
                <?php endif;

                if ( in_array( 'clear', $buttons ) ) : ?>
                <button class="button bs_clear_field_button">
                    <?php _e( 'Clear', 'bs-events-manager-addon' ); ?>
                </button>
                <?php endif; ?>
            </p>
        <?php
    }

    function render_location_info_for_menu() {
        global $dbem_location_types;

        // Is at least one type of remote location activated?
        if ( in_array( 1, $dbem_location_types ) ) {
            echo '<li><a class="bs_settings-page-navigation-link" href="#bs_settings-page-location-info">' . __( "Location Information", "bs-events-manager-addon" ) . '</a></li>';
        }
    }

    function render_payment_gateways_for_menu() {
        $active_gateways = \EM\Payments\Gateways::active_gateways();
        foreach ( $active_gateways as $gateway ) : ?>
            <li><a class="bs_settings-page-navigation-link" href="#bs_settings-page-<?php echo $gateway::$gateway; ?>-emails-section"><?php echo $gateway::$title . ' ' .  __( 'Gateway Emails', 'bs-events-manager-addon' ); ?></a></li>
        <?php endforeach;
    }

    function render_location_info_forms() {
        global $addon_settings, $dbem_location_types;

        // Is at least one type of remote location activated?
        if ( in_array( 1, $dbem_location_types ) ) : ?>
        <h3 id="bs_settings-page-location-info" class="bs_email-template-header"><?php _e( 'Location Information', 'bs-events-manager-addon' ); ?></h3>
        <p><?php _e( 'Here you can specify templates that will be used to display information about a location of an event. Each field is used to display a specific type of location.', 'bs-events-manager-addon' ); ?></p>
        <?php endif; ?>

        <?php if ( key_exists( 'url', $dbem_location_types ) ) : ?>
        <table class="form-table bs_location-info-url-form bs_email-template-settings" role="presentation">
            <thead>
            <tr>
                <th>
                    <h5 class="bs_location-info-url-header"><?php _e( 'URL', 'bs-events-manager-addon' ); ?></h5>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'Content', 'bs-events-manager-addon' ); ?>
                </th>
                <td>
                    <textarea
                            class="bs_location-info-url-field"
                            name="bs_em_addon_settings[email_templates][location_info][url]"
                    ><?php echo $addon_settings['email_templates']['location_info']['url']; ?></textarea>
                </td>
            </tr>
            </tbody>
        </table>
        <?php endif;

        if ( key_exists( 'zoom_room', $dbem_location_types ) ) : ?>
        <table class="form-table bs_location-info-zoom-room-form bs_email-template-settings" role="presentation">
            <thead>
            <tr>
                <th>
                    <h5 class="bs_location-info-zoom-room-header"><?php _e( 'Zoom Room', 'bs-events-manager-addon' ); ?></h5>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'Content', 'bs-events-manager-addon' ); ?>
                </th>
                <td>
                    <textarea
                            class="bs_location-info-zoom-room-field"
                            name="bs_em_addon_settings[email_templates][location_info][zoom_room]"
                    ><?php echo $addon_settings['email_templates']['location_info']['zoom_room']; ?></textarea>
                </td>
            </tr>
            </tbody>
        </table>
        <?php endif;

        if ( key_exists( 'zoom_meeting', $dbem_location_types ) ) : ?>
        <table class="form-table bs_location-info-zoom-meeting-form bs_email-template-settings" role="presentation">
            <thead>
            <tr>
                <th>
                    <h5 class="bs_location-info-zoom-meeting-header"><?php _e( 'Zoom Meeting', 'bs-events-manager-addon' ); ?></h5>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'Content', 'bs-events-manager-addon' ); ?>
                </th>
                <td>
                    <textarea
                            class="bs_location-info-zoom-meeting-field"
                            name="bs_em_addon_settings[email_templates][location_info][zoom_meeting]"
                    ><?php echo $addon_settings['email_templates']['location_info']['zoom_meeting']; ?></textarea>
                </td>
            </tr>
            </tbody>
        </table>
        <?php endif;

        if ( key_exists( 'zoom_webinar', $dbem_location_types ) ) : ?>
        <table class="form-table bs_location-info-zoom-webinar-form bs_email-template-settings" role="presentation">
            <thead>
            <tr>
                <th>
                    <h5 class="bs_location-info-zoom-webinar-header"><?php _e( 'Zoom Webinar', 'bs-events-manager-addon' ); ?></h5>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'Content', 'bs-events-manager-addon' ); ?>
                </th>
                <td>
                    <textarea
                            class="bs_location-info-zoom-webinar-field"
                            name="bs_em_addon_settings[email_templates][location_info][zoom_webinar]"
                    ><?php echo $addon_settings['email_templates']['location_info']['zoom_webinar']; ?></textarea>
                </td>
            </tr>
            </tbody>
        </table>
        <?php endif;

        // Is at least one type of remote location activated?
        if ( in_array( 1, $dbem_location_types ) ) {
            submit_button();
        }
    }

    function render_email_templates_forms( $reciever, $payment_method ) {
        global $addon_settings; ?>
        <table class="form-table bs_<?php echo $reciever; ?>-pending-email-<?php echo $payment_method; ?> bs_email-template-settings bs_pending-email-template-<?php echo $reciever; ?>" role="presentation">
            <thead>
            <tr>
                <th>
                    <h5 class="bs_pending-email-header"><?php _e( 'Pending Booking Email', 'bs-events-manager-addon' ); ?></h5>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'Subject', 'bs-events-manager-addon' ); ?>
                </th>
                <td class="bs_subject-email-field-column">
                    <input
                            class="bs_subject-email-field"
                            name="bs_em_addon_settings[email_templates][<?php echo $reciever; ?>][<?php echo $payment_method; ?>][pending][subject]"
                            type="text"
                            value="<?php echo $addon_settings['email_templates'][$reciever][$payment_method]['pending']['subject']; ?>"
                    >
                    <?php $payment_method !== 'free' ? $this->render_field_buttons( array( 'copy', 'clear' ) ) : $this->render_field_buttons( array( 'clear' ) ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e( 'Header', 'bs-events-manager-addon' ); ?>
                </th>
                <td class="bs_header-email-field-column">
                    <textarea
                            class="bs_header-email-field"
                            name="bs_em_addon_settings[email_templates][<?php echo $reciever; ?>][<?php echo $payment_method; ?>][pending][header]"
                    ><?php echo $addon_settings['email_templates'][$reciever][$payment_method]['pending']['header']; ?></textarea>
                    <?php $payment_method !== 'free' ? $this->render_field_buttons( array( 'copy', 'clear' ) ) : $this->render_field_buttons( array( 'clear' ) ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e( 'Footer', 'bs-events-manager-addon' ); ?>
                </th>
                <td class="bs_footer-email-field-column">
                    <textarea
                            class="bs_footer-email-field"
                            name="bs_em_addon_settings[email_templates][<?php echo $reciever; ?>][<?php echo $payment_method; ?>][pending][footer]"
                    ><?php echo $addon_settings['email_templates'][$reciever][$payment_method]['pending']['footer']; ?></textarea>
                    <?php $payment_method !== 'free' ? $this->render_field_buttons( array( 'copy', 'clear' ) ) : $this->render_field_buttons( array( 'clear' ) ); ?>
                </td>
            </tr>
            </tbody>
        </table>

        <table class="form-table bs_<?php echo $reciever; ?>-confirmed-email-<?php echo $payment_method; ?> bs_email-template-settings bs_confirmed-email-template-<?php echo $reciever; ?>" role="presentation">
            <thead>
            <tr>
                <th>
                    <h5 class="bs_confirmed-email-header"><?php _e( 'Confirmed Booking Email', 'bs-events-manager-addon' ); ?></h5>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'Subject', 'bs-events-manager-addon' ); ?>
                </th>
                <td class="bs_subject-email-field-column">
                    <input
                            class="bs_subject-email-field"
                            name="bs_em_addon_settings[email_templates][<?php echo $reciever; ?>][<?php echo $payment_method; ?>][confirmed][subject]"
                            type="text"
                            value="<?php echo $addon_settings['email_templates'][$reciever][$payment_method]['confirmed']['subject']; ?>"
                    >
                    <?php $payment_method !== 'free' ? $this->render_field_buttons( array( 'copy', 'clear' ) ) : $this->render_field_buttons( array( 'clear' ) ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e( 'Header', 'bs-events-manager-addon' ); ?>
                </th>
                <td class="bs_header-email-field-column">
                    <textarea
                            class="bs_header-email-field"
                            name="bs_em_addon_settings[email_templates][<?php echo $reciever; ?>][<?php echo $payment_method; ?>][confirmed][header]"
                    ><?php echo $addon_settings['email_templates'][$reciever][$payment_method]['confirmed']['header']; ?></textarea>
                    <?php $payment_method !== 'free' ? $this->render_field_buttons( array( 'copy', 'clear' ) ) : $this->render_field_buttons( array( 'clear' ) ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e( 'Footer', 'bs-events-manager-addon' ); ?>
                </th>
                <td class="bs_footer-email-field-column">
                    <textarea
                            class="bs_footer-email-field"
                            name="bs_em_addon_settings[email_templates][<?php echo $reciever; ?>][<?php echo $payment_method; ?>][confirmed][footer]"
                    ><?php echo $addon_settings['email_templates'][$reciever][$payment_method]['confirmed']['footer']; ?></textarea>
                    <?php $payment_method !== 'free' ? $this->render_field_buttons( array( 'copy', 'clear' ) ) : $this->render_field_buttons( array( 'clear' ) ); ?>
                </td>
            </tr>
            </tbody>
        </table>

        <table class="form-table bs_<?php echo $reciever; ?>-cancelled-email-<?php echo $payment_method; ?> bs_email-template-settings bs_cancelled-email-template-<?php echo $reciever; ?>" role="presentation">
            <thead>
            <tr>
                <th>
                    <h5 class="bs_cancelled-email-header"><?php _e( 'Booking Cancelled Email', 'bs-events-manager-addon' ); ?></h5>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'Subject', 'bs-events-manager-addon' ); ?>
                </th>
                <td class="bs_subject-email-field-column">
                    <input
                            class="bs_subject-email-field"
                            name="bs_em_addon_settings[email_templates][<?php echo $reciever; ?>][<?php echo $payment_method; ?>][cancelled][subject]"
                            type="text"
                            value="<?php echo $addon_settings['email_templates'][$reciever][$payment_method]['cancelled']['subject']; ?>"
                    >
                    <?php $payment_method !== 'free' ? $this->render_field_buttons( array( 'copy', 'clear' ) ) : $this->render_field_buttons( array( 'clear' ) ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e( 'Header', 'bs-events-manager-addon' ); ?>
                </th>
                <td class="bs_header-email-field-column">
                    <textarea
                            class="bs_header-email-field"
                            name="bs_em_addon_settings[email_templates][<?php echo $reciever; ?>][<?php echo $payment_method; ?>][cancelled][header]"
                    ><?php echo $addon_settings['email_templates'][$reciever][$payment_method]['cancelled']['header']; ?></textarea>
                    <?php $payment_method !== 'free' ? $this->render_field_buttons( array( 'copy', 'clear' ) ) : $this->render_field_buttons( array( 'clear' ) ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e( 'Footer', 'bs-events-manager-addon' ); ?>
                </th>
                <td class="bs_footer-email-field-column">
                    <textarea
                            class="bs_footer-email-field"
                            name="bs_em_addon_settings[email_templates][<?php echo $reciever; ?>][<?php echo $payment_method; ?>][cancelled][footer]"
                    ><?php echo $addon_settings['email_templates'][$reciever][$payment_method]['cancelled']['footer']; ?></textarea>
                    <?php $payment_method !== 'free' ? $this->render_field_buttons( array( 'copy', 'clear' ) ) : $this->render_field_buttons( array( 'clear' ) ); ?>
                </td>
            </tr>
            </tbody>
        </table>

        <?php do_action( "bs_events_manager_addon/render_email_templates_forms_after", $reciever, $payment_method );

        submit_button();
    }

    public function render_awaiting_payment_table( $reciever, $payment_method ) {
        if ( $payment_method !== 'offline' && $payment_method !== 'direct_deposit' )
            return;

        global $addon_settings;
        ?>
        <table class="form-table bs_<?php echo $reciever; ?>-awaiting-email-<?php echo $payment_method; ?> bs_email-template-settings bs_awaiting-email-template-<?php echo $reciever; ?>" role="presentation">
            <thead>
            <tr>
                <th>
                    <h5 class="bs_awaiting-email-header"><?php _e( 'Awaiting Payment Email', 'bs-events-manager-addon' ); ?></h5>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row">
                    <?php _e( 'Subject', 'bs-events-manager-addon' ); ?>
                </th>
                <td class="bs_subject-email-field-column">
                    <input
                            class="bs_subject-email-field"
                            name="bs_em_addon_settings[email_templates][<?php echo $reciever; ?>][<?php echo $payment_method; ?>][awaiting][subject]"
                            type="text"
                            value="<?php echo $addon_settings['email_templates'][$reciever][$payment_method]['awaiting']['subject']; ?>"
                    >
                    <?php $this->render_field_buttons( array( 'clear' ) ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e( 'Header', 'bs-events-manager-addon' ); ?>
                </th>
                <td class="bs_header-email-field-column">
                    <textarea
                            class="bs_header-email-field"
                            name="bs_em_addon_settings[email_templates][<?php echo $reciever; ?>][<?php echo $payment_method; ?>][awaiting][header]"
                    ><?php echo $addon_settings['email_templates'][$reciever][$payment_method]['awaiting']['header']; ?></textarea>
                    <?php $this->render_field_buttons( array( 'clear' ) ); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php _e( 'Footer', 'bs-events-manager-addon' ); ?>
                </th>
                <td class="bs_footer-email-field-column">
                    <textarea
                            class="bs_footer-email-field"
                            name="bs_em_addon_settings[email_templates][<?php echo $reciever; ?>][<?php echo $payment_method; ?>][awaiting][footer]"
                    ><?php echo $addon_settings['email_templates'][$reciever][$payment_method]['awaiting']['footer']; ?></textarea>
                    <?php $this->render_field_buttons( array( 'clear' ) ); ?>
                </td>
            </tr>
            </tbody>
        </table>
    <?php
    }

    public function ajax_em_payment_methods() {
        if ( wp_verify_nonce( $_POST['security'], 'bs_get_payment_method_nonce' ) ) {

            $active_gateways = \EM\Payments\Gateways::active_gateways();
            $gateways_data_for_return = array('default');
            foreach ( $active_gateways as $gateway ) {
                $gateways_data_for_return[] = $gateway::$gateway;
            }

            wp_send_json( $gateways_data_for_return );
        } else {
            wp_send_json( array(
                'result'    =>  'error',
                'message'   =>  'Access denied'
            ) );
        }
    }

    public function ajax_generate_single_email_template() {
        if ( ! isset( $_POST['receiver'] ) || ! isset( $_POST['gateway'] ) || ! isset( $_POST['templateType'] ) ) {
            $response = array(
                'result'    =>  'error',
                'message'   =>  'Empty field'
            );
        } else if ( ! wp_verify_nonce( $_POST['security'], 'bs_generate_email_template_nonce' ) ) {
            $response = array(
                'result'    =>  'error',
                'message'   =>  'Access denied'
            );
        } else {
            $addon_settings = get_option( 'bs_em_addon_settings' );
            $receiver = sanitize_text_field( $_POST['receiver'] );
            $gateway = sanitize_text_field( $_POST['gateway'] );
            $templateType = sanitize_text_field( $_POST['templateType'] );
            $bookingFormId = (int) $_POST['bookingFormId'];
            if ( !isset( $_POST['attendeeFormId'] ) ) {
                $attendeeFormId = false;
            } else {
                $attendeeFormId = (int) $_POST['attendeeFormId'];
            }
            $locationType = sanitize_key( $_POST['locationType'] );

            switch ($templateType) {
                case 0:
                    $templateType = 'pending';
                    break;
                case 1:
                    $templateType = 'confirmed';
                    break;
                case 2:
                    $templateType = 'rejected';
                    break;
                case 3:
                    $templateType = 'cancelled';
                    break;
                case 5:
                    $templateType = 'awaiting';
                    break;
            }

            if ( $gateway == 'default' ) {
                $gateway_for_settings = 'free';
            } else {
                $gateway_for_settings = $gateway;
            }

            if ( $receiver == 'user' ) {
                $receiver_for_settings = 'attendee';
            } else {
                $receiver_for_settings = 'owner';
            }

            if ( !empty( $addon_settings['email_templates']['location_info'][$locationType] ) && $templateType == 'confirmed' ) {
                $location_placeholder = "\n\n" . 'STANDORTINFORMATIONEN';
                $location_placeholder .= "\n\n" . $addon_settings['email_templates']['location_info'][$locationType] . "\n\n";
            } else {
                $location_placeholder = '';
            }

            $EM_Booking_Form = EM_Booking_Form::get_form( false, $bookingFormId );
            $booking_form_generated_message = $addon_settings['email_templates'][$receiver_for_settings][$gateway_for_settings][$templateType]['header'] . "\n\n";
            $booking_form_generated_message .= "\n\n" . 'BUCHUNGSDETAILS' . "\n\n";
            foreach ( $EM_Booking_Form->form_fields as $form_field ) {
                $booking_form_generated_message .= $form_field['label'] . ': <b>#_BOOKINGFORMCUSTOM{' . $form_field['fieldid'] . '}' . "</b>\n\n";
            }
            if ( $attendeeFormId !== false && ( isset( EM_Attendees_Form::get_forms()[$attendeeFormId] ) || $attendeeFormId == 0 ) ) {
                $booking_form_generated_message .= "\n\n" . 'TEILNEHMERLISTE' . "\n\n";
                $booking_form_generated_message .= '#_BOOKINGATTENDEES' . "\n\n";
            }
            $booking_form_generated_message .= $location_placeholder;
            $booking_form_generated_message .= "\n\n" . $addon_settings['email_templates'][$receiver_for_settings][$gateway_for_settings][$templateType]['footer'];
            $booking_form_generated_subject = $addon_settings['email_templates'][$receiver_for_settings][$gateway_for_settings][$templateType]['subject'];

            $response = array(
                'receiver'          =>  $receiver,
                'gateway'           =>  $gateway,
                'templateType'      =>  $templateType,
                'generatedSubject'  =>  $booking_form_generated_subject,
                'generatedMessage'  =>  $booking_form_generated_message,
                'result'            => 'ok'
            );
        }

        wp_send_json( $response );
    }

}
