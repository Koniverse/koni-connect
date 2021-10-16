<?php

defined('ABSPATH') || exit;

class Web3vn_WaCo_Installer
{
    private static $db_updates = [];

    public function __construct()
    {
        $this->install();
    }

    function install()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'web3vn_wallet_connect';
        $user_table = $wpdb->prefix . 'users';

        $collate = '';

        if ($wpdb->has_cap('collation')) {
            $collate = $wpdb->get_charset_collate();
        }

        $sql = "CREATE TABLE $table_name (
		id BIGINT UNSIGNED NOT NULL auto_increment,
		user_id BIGINT UNSIGNED,
		type smallint,
		wallet_name text,
		wallet_address text,
		PRIMARY KEY  (id),
        FOREIGN KEY  (user_id) REFERENCES $user_table(ID)
	) $collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        $db_version = get_option('web3vn_waco_db_version');

        if (is_null($db_version)) {
            add_option('web3vn_waco_db_version', WEB3VN_WACO_VERSION);
        } else {
            update_option('web3vn_waco_db_version', WEB3VN_WACO_VERSION);
        }
    }
}