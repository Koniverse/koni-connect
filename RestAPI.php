<?php

class RestAPI
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public static function privileged_permission_callback()
    {
        return current_user_can('manage_options');
    }

    public function sanitize_key($key, $request, $param)
    {
        return trim($key);
    }

    public function register_routes()
    {
        register_rest_route(
            'web3vn-waco',
            'profile',
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'permission_callback' => array($this, 'privileged_permission_callback'),
                    'callback' => array($this, 'checkProfile'),
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'permission_callback' => array($this, 'privileged_permission_callback'),
                    'callback' => array($this, 'updateProfile'),
                    'args' => array(
                        'name' => array(
                            'required' => true,
                            'type' => 'string',
                            'sanitize_callback' => array($this, 'sanitize_key'),
                        ),
                        'address' => array(
                            'required' => true,
                            'type' => 'string',
                            'sanitize_callback' => array($this, 'sanitize_key'),
                        ),
                    ),
                )
            )
        );
    }

    public function checkProfile($request)
    {
        global $wpdb;
        $target_table = $wpdb->prefix . 'web3vn_wallet_connect';
        $current_user = wp_get_current_user();

        $empty_data = array('data' => null);

        if (empty($current_user) || empty($current_user->get('ID'))) {
            return $empty_data;
        }

        $query = 'SELECT * FROM ' . $target_table . ' WHERE user_id = ' . $current_user->get('ID');

        $query_results = $wpdb->get_results($query);

        if (empty($query_results)) {
            return $empty_data;
        }

        $response = array('data' => array(
            'name' => $query_results[0]->wallet_name,
            'address' => $query_results[0]->wallet_address
        ));

        return rest_ensure_response($response);
    }

    public function updateProfile($request)
    {
        try {
            $current_user = wp_get_current_user();

            if (empty($current_user) || empty($current_user->get('ID'))) {
                throw new Exception("No user found");
            }

            global $wpdb;
            $name = $request->get_param('name');
            $address = $request->get_param('address');

            $target_table = $wpdb->prefix . 'web3vn_wallet_connect';

            $info = array(
                'type' => 1,
                'user_id' => $current_user->get('ID'),
                'wallet_name' => $name,
                'wallet_address' => $address
            );

            $query = 'SELECT * FROM ' . $target_table . ' WHERE user_id = ' . $current_user->get('ID');
            $query_results = $wpdb->get_results($query);

            if (empty($query_results)) {
                $wpdb->insert($target_table, $info);
            } else {
                $wpdb->update($target_table, $info, array('user_id' => $info['user_id']));
            }

            return rest_ensure_response(array('data' => 'ok'));
        } //catch exception
        catch (Exception $e) {
            return rest_ensure_response(new WP_Error('invalid', 'There is problem with your request', array('status' => 500)));
        }
    }
}
