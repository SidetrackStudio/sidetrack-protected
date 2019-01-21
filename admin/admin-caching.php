<?php

/**
 * @package     Sidetrack Protected
 * @subpackage  Admin Caching
 *
 * @since  2.1
 */

class Sidetrack_Protected_Admin_Caching {

	/**
	 * Plugin
	 *
	 * @since  2.1
	 *
	 * @var  Sidetrack_Protected|null
	 */
	private $plugin = null;

	/**
	 * Constructor
	 *
	 * @since  2.1
	 *
	 * @internal  Private. This class should only be instantiated once by the plugin.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;

		add_action( 'admin_init', array( $this, 'cache_settings_info' ) );

	}

	/**
	 * Cache Settings Info
	 *
	 * Displays information on the settings page for helping
	 * to configure Sidetrack Protected to work with caching setups.
	 *
	 * @since  2.1
	 */
	public function cache_settings_info() {

		// Caching Section
		add_settings_section(
			'sidetrack_protected_compat_caching',
			__( 'Caching', 'sidetrack-protected' ),
			array( $this, 'section_caching' ),
			'password-protected-compat'
		);

		// Cookies
		add_settings_field(
			'sidetrack_protected_compat_caching_cookie',
			__( 'Cookies', 'sidetrack-protected' ),
			array( $this, 'field_cookies' ),
			'password-protected-compat',
			'sidetrack_protected_compat_caching'
		);

		// WP Engine Hosting
		if ( $this->test_wp_engine() ) {

			add_settings_field(
				'sidetrack_protected_compat_caching_wp_engine',
				__( 'WP Engine Hosting', 'sidetrack-protected' ),
				array( $this, 'field_wp_engine' ),
				'password-protected-compat',
				'sidetrack_protected_compat_caching'
			);

		}

		// W3 Total Cache
		if ( $this->test_w3_total_cache() ) {

			add_settings_field(
				'sidetrack_protected_compat_caching_w3_total_cache',
				__( 'W3 Total Cache', 'sidetrack-protected' ),
				array( $this, 'field_w3_total_cache' ),
				'password-protected-compat',
				'sidetrack_protected_compat_caching'
			);

		}

	}

	/**
	 * Caching Section
	 *
	 * @since  2.1
	 */
	public function section_caching() {

		echo '<p>' . __( 'Sidetrack Protected does not always work well with sites that use caching.', 'sidetrack-protected' ) . '<br />
			' . __( 'If your site uses a caching plugin or your web hosting uses server-side caching, you may need to configure your setup to disable caching for the Sidetrack Protected cookie:', 'sidetrack-protected' ) . '</p>';

	}

	/**
	 * Password Protection Status Field
	 *
	 * @since  2.1
	 */
	public function field_cookies() {

		echo '<p><input type="text" value="' . esc_attr( $this->plugin->cookie_name() ) . '" class="regular-text code" /></p>';

	}

	/**
	 * WP Engine Hosting
	 *
	 * @since  2.1
	 */
	public function field_wp_engine() {

		echo '<p>' . __( 'We have detected your site may be running on WP Engine hosting.', 'sidetrack-protected' ) . '<br />
			' . __( 'In order for Sidetrack Protected to work with WP Engine\'s caching configuration you must ask them to disable caching for the Sidetrack Protected cookie.', 'sidetrack-protected' ) . '</p>';

	}

	/**
	 * W3 Total Cache Plugin
	 *
	 * @since  2.1
	 */
	public function field_w3_total_cache() {

		echo '<p>' . __( 'It looks like you may be using the W3 Total Cache plugin?', 'sidetrack-protected' ) . '<br />
			' . __( 'In order for Sidetrack Protected to work with W3 Total Cache you must disable caching when the Sidetrack Protected cookie is set.', 'sidetrack-protected' ) . ' 
			' . sprintf( __( 'You can adjust the cookie settings for W3 Total Cache under <a href="%s">Performance > Page Cache > Advanced > Rejected Cookies</a>.', 'sidetrack-protected' ), admin_url( '/admin.php?page=w3tc_pgcache#advanced' ) ) . '</p>';

	}

	/**
	 * Test: WP Engine
	 *
	 * @since  2.1
	 *
	 * @return  boolean
	 */
	private function test_wp_engine() {

		return ( function_exists( 'is_wpe' ) && is_wpe() ) || ( function_exists( 'is_wpe_snapshot' ) && is_wpe_snapshot() );

	}

	/**
	 * Test: W3 Total Cache
	 *
	 * @since  2.1
	 *
	 * @return  boolean
	 */
	private function test_w3_total_cache() {

		return defined( 'W3TC' ) && W3TC;

	}

}
