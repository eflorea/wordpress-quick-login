<?php
/*
 * Plugin Name: WordPress Quick Login
 * Version: 1.0
 * Plugin URI: https://github.com/eflorea/wordpress-quick-login
 * Description: WordPress Quick Login provides a quick way to auto login as an administrator without a password. Perfect tool for developers.
 * Author: Eduard Florea
 * Author URI: https://eduardflorea.com/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: wordpress-quick-login
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Eduard Florea
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
};

/**
 * Class WP_Easy_Login
 */
class WP_Quick_Login {

	/**
	 * WP_Easy_Login constructor
	 */
	public function __construct() {
		add_action( 'login_form', array( $this, 'add_login_button' ) );

		add_action( 'init', array( $this, 'login' ) );
	}

	/**
	 * Add login button on WP Login page
	 */
	public function add_login_button() {

		$admin_user = $this->get_admin_user();

		if ( ! empty( $admin_user ) ) {
			$user_name = (string)$admin_user->user_login . '(' . (string)$admin_user->user_email . ')';
			$button_text = __( 'Login as %1$s', 'wordpress-quick-login' );
			$button_text = sprintf( $button_text, $user_name );
			echo sprintf( '<p><a class="button button-primary" href="%1$s" style="float: none; display: block; margin-bottom: 10px; text-align: center;">%2$s</a></p>',
				esc_url( wp_login_url() . '?action=ef-auto-login&_wpnonce=' . wp_create_nonce( 'wordpress-easy-login' ) ),
				esc_html( $button_text )
			);
		}
	}

	/**
	 * Login action
	 *
	 * @return void;
	 */
	public function login() {

		if ( isset( $_GET['action'] ) && 'ef-auto-login' === $_GET['action'] && wp_verify_nonce( $_GET['_wpnonce'], 'wordpress-easy-login' ) ) {

			$admin_user = $this->get_admin_user();

			if ( ! empty( $admin_user ) ) {

				wp_clear_auth_cookie();
				wp_set_current_user( absint( $admin_user->ID ) );
				wp_set_auth_cookie( absint( $admin_user->ID ) );

				$redirect_to = get_dashboard_url();
				wp_safe_redirect( $redirect_to );
				exit();
			}
		}
	}

	/**
	 * Get an admin user
	 *
	 * @return mixed
	 */
	public function get_admin_user() {
		global $wpdb;

		$results = $wpdb->get_results( "
			SELECT u.ID, u.user_login, u.user_email
			FROM {$wpdb->base_prefix}users u, {$wpdb->base_prefix}usermeta m
			WHERE u.ID = m.user_id
			AND m.meta_key LIKE '{$wpdb->base_prefix}capabilities'
			AND m.meta_value LIKE '%administrator%'
			 AND u.deleted = 0 limit 1"
		);

		if ( ! empty( $results ) ) {
			return $results[0];
		}

		return false;
	}

}

new WP_Quick_Login();
