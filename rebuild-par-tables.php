<?php
/*
 Plugin Name: Rebuild the Points and rewards missing database tables
 Plugin URI: https://profiles.wordpress.org/rynald0s
 Description: This plugin will let you rebuild the points and rewards database tables
 Author: Rynaldo Stoltz
 Author URI: https://github.com/rynaldos
 Version: 1.0
 License: GPLv3 or later License
 URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

 if ( ! defined( 'ABSPATH' ) ) {
  exit;
}
if (!class_exists('WooCommerce_rebuild_PAR_tables')) {
  class WooCommerce_rebuild_PAR_tables {
    public static $instance;
    public static function init() {
      if ( is_null( self::$instance ) ) {
        self::$instance = new WooCommerce_rebuild_PAR_tables();
      }
      return self::$instance;
    }
    private function __construct() {
      add_filter( 'admin_init', array( $this, 'handle_woocommerce_tool' ) );
      add_filter( 'woocommerce_debug_tools', array( $this, 'add_woocommerce_tool' ) );
    }

public function rebuild_par_db_tables() {

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
	  $this->user_points_log_db_tablename = $wpdb->prefix . 'wc_points_rewards_user_points_log';
	  $this->user_points_db_tablename     = $wpdb->prefix . 'wc_points_rewards_user_points';
	
	$sql =
		"CREATE TABLE {$this->user_points_log_db_tablename} (
		  id bigint(20) NOT NULL AUTO_INCREMENT,
		  user_id bigint(20) NOT NULL,
		  points bigint(20) NOT NULL,
		  type varchar(255) DEFAULT NULL,
		  user_points_id bigint(20) DEFAULT NULL,
		  order_id bigint(20) DEFAULT NULL,
		  admin_user_id bigint(20) DEFAULT NULL,
		  data longtext DEFAULT NULL,
		  date datetime NOT NULL,
		  KEY idx_wc_points_rewards_user_points_log_date (date),
		  KEY idx_wc_points_rewards_user_points_log_type (type),
		  KEY idx_wc_points_rewards_user_points_log_points (points),
		  PRIMARY KEY  (id)
		) $charset_collate;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
	  
    $sql =
		"CREATE TABLE {$this->user_points_db_tablename} (
		  id bigint(20) NOT NULL AUTO_INCREMENT,
		  user_id bigint(20) NOT NULL,
		  points bigint(20) NOT NULL,
		  points_balance bigint(20) NOT NULL,
		  order_id bigint(20) DEFAULT NULL,
		  date datetime NOT NULL,
		  KEY idx_wc_points_rewards_user_points_user_id_points_balance (user_id,points_balance),
		  KEY `idx_wc_points_rewards_user_points_date_points_balance` (`date`,`points_balance`),
		  PRIMARY KEY  (id)
		) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

    public function add_woocommerce_tool( $tools ) {
      $tools['rebuild_par_db_tables'] = array(
        'name'    => __( 'Rebuild Points and Rewards tables', 'woocommerce' ),
        'button'  => __( 'Rebuild Points and Rewards tables', 'woocommerce' ),
        'desc'    => __( 'This option will rebuild the Points and Rewards missing tables!', 'woocommerce' ),
        'callback' => array( $this, 'rebuild_notice_success' ),
      );
      return $tools;
    }

    public function handle_woocommerce_tool() {
      if( empty( $_REQUEST['page'] ) || empty( $_REQUEST['tab'] ) ) {
          return;
      }
      
      if( 'wc-status' != $_REQUEST['page'] ) {
        return;
      }

      if( 'tools' != $_REQUEST['tab'] ) {
        return;
      }

      if( ! is_user_logged_in() || ! current_user_can('manage_woocommerce') ) {
        return;
      }
      if ( ! empty( $_GET['action'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'debug_action' ) ) {
        if( $_GET['action'] === 'rebuild_par_db_tables' ) {
          //$this->rebuild_par_db_tables();
        }
      }
    }

    function rebuild_notice_success() {
		add_action( 'admin_notices', 'rebuild_notice_success' );
    ?>
<div class="notice notice-success is-dismissible">
  <p><?php echo wp_sprintf( __('Tables were created successfully!', 'woocommerce') ); ?></p>
</div>
    <?php
    }
  }
}

$woocommerce_delete_orders = WooCommerce_rebuild_PAR_tables::init();
