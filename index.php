<?php
/**
 * WP Tiger.
 *
 * WP Tiger plugin file.
 *
 * @copyright Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: WP Tiger
 * Plugin URI: http://www.smackcoders.com
 * Description: Easy Lead capture Vtiger Webforms and Contacts synchronization
 * Version: 4.1
 * Author: smackcoders.com
 * Author URI: http://www.smackcoders.com
 * Easy Lead capture Vtiger Webforms and Contacts synchronization is a tool
 * for capturing leads and contacts to VtigerCRM from WordPress developed by
 * Easy Lead capture Vtiger Webforms and Contacts synchronization is free
 * License:     GPL v3
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly
class VtigerCrmSmLBHandler {

	public $version = '4.1';

	protected static $_instance = null;

	/**
	 * Main WPLeadsBuilderForAnyCRMPro Instance.
	 *
	 * Ensures only one instance of WPLeadsBuilderForAnyCRMPro is loaded or can be loaded.
	 *
	 * @since 4.5
	 * @static
	 * @return VtigerCrmSmLBHandler - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init();
		$this->init_hooks();		
	}

	private function init_hooks() {

		add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ),  array($this, 'lb_plugin_row_meta'), 10, 2 );

		//User sync - on time creation
		$check_sync_value = get_option( 'Sync_value_on_off' );
		register_activation_hook(__FILE__, array($this, 'activate'));
		if(!function_exists('admin_notice_suite_addon')){
			function admin_notice_suite_addon() {
				global $pagenow;
				$active_plugins = get_option( "active_plugins" );
				if ( $pagenow == 'plugins.php' && !in_array('wp-leads-builder-any-crm/index.php', $active_plugins) ) {
?>
				    <div class="notice notice-warning is-dismissible" >
					<p> Wp Tiger is an addon of <a href="https://wordpress.org/plugins/wp-leads-builder-any-crm/?utm_source=plugin&utm_medium=wordpress&utm_campaign=leads_builder_addon" target="blank" style="cursor: pointer;text-decoration:none">WP Leads Builder for CRM</a> plugin, kindly install it to continue using WP Form to CRM integration. </p>
				    </div>
<?php 
				}
			}
		}

		add_action( 'admin_notices', 'admin_notice_suite_addon' );


	}
	public function activate()
	{
		update_option('WpLeadBuilderProActivatedPlugin','wptigerpro');
	}



	public function define_constants() {		                                           
		$this->define( 'SM_LB_VTIGER_PLUGIN_FILE', __FILE__ );
		$this->define( 'SM_LB_VTIGER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'SM_LB_VTIGER_DIR', plugin_dir_path(__FILE__));
		$this->define( 'SM_LB_VTIGER_SLUG', 'wp-tiger' );
		// $this->define( 'SM_LB_VTIGER_DIR', WP_PLUGIN_URL . '/' .SM_LB_VTIGER_SLUG. '/');
		$this->define( 'SM_LB_VTIGER_DIR',plugins_url(SM_LB_VTIGER_SLUG.'/',dirname(__FILE__,1)));
		$this->define( 'SM_LB_VTIGER_SETTINGS', 'WP Tiger' );
		$this->define( 'SM_LB_VTIGER_VERSION', '4.1');
		$this->define( 'SM_LB_VTIGER_NAME', 'WP Tiger' );
		$this->define( 'SM_LB_VTIGER_URL',site_url().'/wp-admin/admin.php?page='.SM_LB_VTIGER_SLUG.'/index.php');
	}

	public function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	public function init() {
		if(is_admin()) :
			// Init action.
			do_action( 'uci_init' );
		if(is_admin()) {

		}
endif;
	}

	public function includes() {
		include_once ( 'admin/lb-admin.php' );
	}


	public static function lb_plugin_row_meta( $links, $file ) {
		if ( $file == SM_LB_VTIGER_PLUGIN_BASENAME ) {
			$row_meta = array(
				'support'  => '<a href="' . esc_url( apply_filters( 'SM_LB_VTIGER_support_url', 'https://www.smackcoders.com/support.html?utm_source=lead_builder_free&utm_campaign=plugin_menu&utm_medium=plugin' ) ) . '" title="' . esc_attr( __( 'Contact Support', 'wp-tiger' ) ) . '" target="_blank">' . __( 'Support', 'wp-tiger' ) . '</a>',
			);
			unset( $links['edit'] );
			return array_merge( $row_meta, $links );
		}
	}

	public function includeFunction()
	{
		require_once("includes/wptigerproFunctions.php");
	}
}
function VtigerSmackLB() {
	return VtigerCrmSmLBHandler::instance();
}
// Global for backwards compatibility.
$GLOBALS['wp_leads_builder_for_any_crm'] = VtigerSmackLB();
