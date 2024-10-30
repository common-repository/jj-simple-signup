<?php
/*
Plugin Name: JJ Simple Signup
Plugin URI: http://www.jarsater.com/jj-simple-signup-wp-plugin
Version: 0.2.3
Author: <a href="http://www.jarsater.com/" target="_blank">Joakim Jarsäter</a>
Description: Simple Signup är en webbtjänst som gör det enkelt för dig som arrangör att ta emot anmälningar och betalningar till dina evenemang.
*/
set_include_path( realpath(dirname(__FILE__)) . '/libs' . PATH_SEPARATOR . get_include_path() );

require_once 'class.Simplesignup.php';
require_once 'admin/class.admin.Simplesignup.php';
require_once 'jj-simple-signup-widget.php';
require_once 'libs/Zend/Cache.php';
require_once 'libs/Simplepie/Simplepie.php';

if (!class_exists("JJ_Simplesignup")) {
	class JJ_Simplesignup {
		
		protected $tablename = 'jj_simplesignup';
		
		protected $version = '0.2.3';
		
		function JJ_SimpleSignup() {
			
			add_action('init', array(&$this, 'init'));
			add_action('admin_init', array(&$this, 'admin_init'));
			add_action('admin_menu', array(&$this, 'admin_menu'));
			add_action('widgets_init', array(&$this, 'register_widget'));
			
			register_activation_hook(__FILE__, array(&$this, 'install'));
		}	
		
		function __construct() {
			$this->JJ_SimpleSignup();
		}
		
		/**
		 * This function is executed init-event.
		 * It adds shortcode, register styles
		 * and sets the correct localization.
		 *
		 */
		public function init() {
			// Fixes a bug where WP dont use the correct timezone
			date_default_timezone_set(get_option('timezone_string'));
			
			// Adding Shortcode
			add_shortcode('jj-simplesignup-event', array(&$this, 'jj_simplesignup_func'));
			
			// Adding CSS style
			wp_register_style('jj-simplesignup', WP_PLUGIN_URL . '/jj-simple-signup/style.css');
			wp_enqueue_style('jj-simplesignup');
			
			// Load localization
			load_plugin_textdomain('jj-simplesignup', '/wp-content/plugins/jj-simple-signup//languages' );	
		}
		
		public function admin_init() {
			// Adding CSS style
			wp_register_style('jj-simplesignup-admin', WP_PLUGIN_URL . '/jj-simple-signup/admin/style.css');
			wp_enqueue_style('jj-simplesignup-admin');
			wp_enqueue_style('dashboard');
		}
		
		/**
		 * Installing & Actiovation
		 */
		public function install() {
			global $wpdb;
		
			$this->upgrade();
			
			  $table_name = $wpdb->prefix . "jj_simplesignup";
			  if($wpdb->get_var("show tables like '$table_name'") != $table_name) {    
			  	$sql = "CREATE TABLE $table_name (
						  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
						  `simplesignup_id` bigint(11) NOT NULL DEFAULT '0',
						  `simplesignup_token` varchar(25) NOT NULL,
						  UNIQUE KEY `id` (`id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

			    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			    dbDelta($sql);
			  }
			  
			  add_option('jj_simplesignup_print_guestlist', 0);
			  add_option("jj_simplesignup_version", $this->version);
			  add_option('jj_simplesignup_widget', array('id' => 0, 'token' => '', 'title' => 'My Event'));
			  
		}
		
		/**
		 * Upgrade plugin
		 */
		public function upgrade() {
			$installed_version = get_option('jj_simplesignup_version');
			if($this->version != $installed_version) {
				// Create SQL
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				//do function dbDelta($sql);
				update_option('jj_simplesignup_version', $this->version);
			}
		}
		
		/**
		 * Loads the Admin Menu 
		 *
		 */
		public function admin_menu() {
			// Top-Level Menu
			add_menu_page( __('Simple Signup'), __('Simple Signup'), 8, basename(__FILE__), array(&$this, 'admin_top_level') );
			
			// Sub-Level menu
			add_submenu_page( basename(__FILE__), __('Information', 'jj-simplesignup'), __('Information', 'jj-simplesignup'), 8, basename(__FILE__),  array(&$this, 'admin_top_level') );
			add_submenu_page( basename(__FILE__), __('Settings', 'jj-simplesignup'), __('Settings', 'jj-simplesignup'), 8, 'jj-simplesignup-menu-settings', array(&$this, 'admin_settings') );
			add_submenu_page( basename(__FILE__), __('My Events', 'jj-simplesignup'), __('My Events', 'jj-simplesignup'), 8, 'jj-simplesignup-menu-listevents', array(&$this, 'admin_list_events') );
		}
		
		/**
		 * 
		 */
		public function admin_top_level() { ?>
			<div class="wrap">
				<?php screen_icon( 'page' ); ?>
				<h2><?php _e('Information', 'jj-simplesignup') ;?></h2>
				<?php $adminSimplesignup = new adminSimplesignup(); ?>
				<?php $adminSimplesignup->adminInformation(); ?>
			</div>
		<?php }
		
	public function admin_settings() { ?>
			<div class="wrap">
				<?php screen_icon( 'page' ); ?>
				<h2><?php _e('Settings', 'jj-simplesignup') ;?></h2>
				<?php $adminSimplesignup = new adminSimplesignup(); ?>
				<?php $adminSimplesignup->adminSettings(); ?>
			</div>
		<?php }
		
		public function admin_list_events() { ?>
			<div class="wrap">
				<?php screen_icon( 'page' ); ?>
				<h2><?php _e('My Events', 'jj-simplesignup'); ?> <a class="button add-new-h2" href="#"><?php _e('Add new'); ?></a> </h2>
				<?php $adminSimplesignup = new adminSimplesignup(); ?>
				<?php $adminSimplesignup->adminListEvents(); ?>
			</div>
		<?php }
		
		/**
		 * Function for shortcode
		 * [jj-simplesignup]
		 * 
		 * @param string $atts
		 * @return string 
		 */
		public function jj_simplesignup_func($atts) {
			global $wpdb;
			extract(shortcode_atts(array(
				'id' 				=> '',
				'print_guestlist' 	=> get_option('jj_simplesignup_print_guestlist'),
				'date_format'		=> get_option('date_format') . ' ' .  get_option('time_format')
			), $atts));
			
			// If ID or Token is empty, return error string
			if(empty($id))
				return '<small class="jj-simplesignup-error">' . __('Error loading event data', 'jj-simplesignup') . '</small>';
			
			$myEvent = $wpdb->get_row('SELECT * FROM ' .$wpdb->prefix . $this->tablename . ' WHERE id = ' . $id);

			$simplesignupEvent = new Simplesignup($myEvent->simplesignup_id, $myEvent->simplesignup_token);
			$simplesignupEvent->setDateTimeFormat(get_option('date_format') . ' ' . get_option('time_format'));
			
			$return = '<h3>' . __('Information', 'jj-simplesignup') . '</h3>';
			$return .= $simplesignupEvent->eventInformation();
			if($print_guestlist)
				$return .= '<h5>' . __('Attendees', 'jj-simplesignup') . '</h5>' . $simplesignupEvent->eventAttendees();
			
			return $return;
		}
		
		/**
		 * Registers multiwidget
		 */
		public function register_widget() {
			register_widget('JJ_Simplesignup_Widget');
			wp_register_widget_control( 'jj_simplesignup_widget', __('Widget'), array('jj_simplesignup_widget', 'form'), get_option('jj_simplesignup_widget') );
		} // register_widget
		
	}
}

$JJ_Simplesignup = new JJ_Simplesignup();