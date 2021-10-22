<?php
/**
 * Plugin Name: Web3Vn - Wallet Connect
 * Plugin URI: https://www.web3vn.network/insight
 * Description: Web3Vn Wallet Connect for Wordpress
 * Version: 1.0.0
 * Author: CDM
 * Author URI: https://www.web3vn.network/insight
 **/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


if ( ! class_exists( 'Web3vn_Wallet_Connect' ) ) :

    final class Web3vn_Wallet_Connect {
        const VERSION = '1.0.0';

        /**
         * Web3vn_Wallet_Connect constructor.
         */
        public function __construct() {
            /*-------------------------------------
                DEFINE CONSTANTS
            ---------------------------------------*/
            $this->define_constants();

            /*-------------------------------------
                INIT
            ---------------------------------------*/
            add_action( 'init', array( $this, 'init' ), 50 );
        }


        /**
         * Init the addon
         */
        function init() {
            require_once( WEB3VN_WACO_DIR . 'class-waco-installer.php' );
            new Web3vn_WaCo_Installer();

            require_once( WEB3VN_WACO_DIR . 'RestAPI.php' );
            new RestAPI();

            /*-------------------------------------
                LOAD TEXT DOMAIN
            ---------------------------------------*/
            $this->load_text_domain();

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

            add_action( 'wp_enqueue_scripts', array( $this, 'admin_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'admin_styles' ) );

            include WEB3VN_WACO_DIR . '/class-waco-admin-profile.php';
        }

        /**
         * define some constants
         */
        function define_constants() {
            defined( 'WEB3VN_WACO' ) or define( 'WEB3VN_WACO', 'web3vn-wallet-connect' );
            defined( 'WEB3VN_WACO_VERSION' ) or define( 'WEB3VN_WACO_VERSION', '1.0.0' );
            defined( 'WEB3VN_WACO_URL' ) or define( 'WEB3VN_WACO_URL', plugins_url( WEB3VN_WACO ) );
            defined( 'WEB3VN_WACO_FILE' ) or define( 'WEB3VN_WACO_FILE', __FILE__ );
            defined( 'WEB3VN_WACO_DIR' ) or define( 'WEB3VN_WACO_DIR', plugin_dir_path( __FILE__ ) );
        }

        /**
         * Load text domain
         */
        function load_text_domain() {
            load_plugin_textdomain( WEB3VN_WACO, false, WEB3VN_WACO_DIR . 'languages/' );
        }

        public function admin_scripts() {
            //todo: to chuc lai cho nay

//            $suffix  = Constants::is_true( 'SCRIPT_DEBUG' ) ? '' : '.min';
            wp_register_script( 'web3vn-waco-polkadot', WEB3VN_WACO_URL . '/assets/js/polkadot.js', [], WEB3VN_WACO_VERSION );

            wp_enqueue_script( 'web3vn-waco-polkadot' );
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'bootstrap-modal', WEB3VN_WACO_URL . '/assets/libs/bootstrap/bootstrap-modal.js', [], WEB3VN_WACO_VERSION );

            wp_localize_script(
                'web3vn-waco-polkadot',
                'web3vnWacoApi',
                array(
                    'nonce'          => wp_create_nonce( 'wp_rest' ),
                    'root' => get_rest_url( null, '' ),
                )
            );
        }

        public function admin_styles() {
            //todo: to chuc lai cho nay
            wp_enqueue_style( 'bootstrap-modal', WEB3VN_WACO_URL . '/assets/libs/bootstrap/bootstrap-modal.css', [], WEB3VN_WACO_VERSION );
        }

//        static function plugin_dir( $file = __FILE__ ) {
//            return trailingslashit( plugin_dir_path( $file ) );
//        }
//
//        static function plugin_url( $file = __FILE__ ) {
//            return trailingslashit( plugin_dir_url( $file ) );
//        }
    }

    new Web3vn_Wallet_Connect();
endif;