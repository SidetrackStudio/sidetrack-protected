<?php

class Sidetrack_Protected_Admin {

	var $settings_page_id;
	var $options_group = 'sidetrack-protected';

	/**
	 * Constructor
	 */
	public function __construct() {

		global $wp_version;

		add_action( 'admin_init', array( $this, 'sidetrack_protected_settings' ), 5 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'sidetrack_protected_help_tabs', array( $this, 'help_tabs' ), 5 );
		add_action( 'admin_notices', array( $this, 'sidetrack_protected_admin_notices' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );
		add_filter( 'plugin_action_links_sidetrack-protected/sidetrack-protected.php', array( $this, 'plugin_action_links' ) );
		add_filter( 'pre_update_option_sidetrack_protected_password', array( $this, 'pre_update_option_sidetrack_protected_password' ), 10, 2 );

	}

	/**
	 * Admin Menu
	 */
	public function admin_menu() {

		$this->settings_page_id = add_options_page( __( 'Sidetrack Protected', 'sidetrack-protected' ), __( 'Sidetrack Protected', 'sidetrack-protected' ), 'manage_options', 'sidetrack-protected', array( $this, 'settings_page' ) );
		add_action( 'load-' . $this->settings_page_id, array( $this, 'add_help_tabs' ), 20 );

	}

	/**
	 * Settings Page
	 */
	public function settings_page() {
		?>

		<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>
			<h2><?php _e( 'Sidetrack Protected Settings', 'sidetrack-protected' ); ?></h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'sidetrack-protected' );
				do_settings_sections( 'sidetrack-protected' );
				?>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes' ); ?>"></p>
			</form>
			<?php do_settings_sections( 'sidetrack-protected-compat' ); ?>
		</div>

		<?php
	}

	/**
	 * Add Help Tabs
	 */
	public function add_help_tabs() {

		global $wp_version;

		if ( version_compare( $wp_version, '3.3', '<' ) ) {
			return;
		}

		do_action( 'sidetrack_protected_help_tabs', get_current_screen() );

	}

	/**
	 * Help Tabs
	 *
	 * @param  object $current_screen  Screen object.
	 */
	public function help_tabs( $current_screen ) {

		$current_screen->add_help_tab(
			array(
				'id'      => 'SIDETRACK_PROTECTED_SETTINGS',
				'title'   => __( 'Sidetrack Protected', 'sidetrack-protected' ),
				'content' => __( '<p><strong>Sidetrack Protected Status</strong><br />Turn on/off password protection.</p>', 'sidetrack-protected' )
					. __( '<p><strong>Protected Permissions</strong><br />Allow access for logged in users and administrators without needing to enter a password. You will need to enable this option if you want administrators to be able to preview the site in the Theme Customizer. Also allow RSS Feeds to be accessed when the site is password protected.</p>', 'sidetrack-protected' )
					. __( '<p><strong>Password Fields</strong><br />To set a new password, enter it into both fields. You cannot set an `empty` password. To disable password protection uncheck the Enabled checkbox.</p>', 'sidetrack-protected' ),
			)
		);

	}

	/**
	 * Settings API
	 */
	public function sidetrack_protected_settings() {

		add_settings_section(
			'password_protected',
			'',
			array( $this, 'sidetrack_protected_settings_section' ),
			$this->options_group
		);

		add_settings_field(
			'sidetrack_protected_status',
			__( 'Sidetrack Protected Status', 'sidetrack-protected' ),
			array( $this, 'sidetrack_protected_status_field' ),
			$this->options_group,
			'password_protected'
		);

		add_settings_field(
			'sidetrack_protected_permissions',
			__( 'Protected Permissions', 'sidetrack-protected' ),
			array( $this, 'sidetrack_protected_permissions_field' ),
			$this->options_group,
			'password_protected'
		);

		add_settings_field(
			'sidetrack_protected_password',
			__( 'New Password', 'sidetrack-protected' ),
			array( $this, 'sidetrack_protected_password_field' ),
			$this->options_group,
			'password_protected'
		);

		add_settings_field(
			'sidetrack_protected_allowed_ip_addresses',
			__( 'Allow IP Addresses', 'sidetrack-protected' ),
			array( $this, 'sidetrack_protected_allowed_ip_addresses_field' ),
			$this->options_group,
			'password_protected'
		);

		add_settings_field(
			'sidetrack_protected_remember_me',
			__( 'Allow Remember me', 'sidetrack-protected' ),
			array( $this, 'sidetrack_protected_remember_me_field' ),
			$this->options_group,
			'password_protected'
		);

		add_settings_field(
			'sidetrack_protected_remember_me_lifetime',
			__( 'Remember for this many days', 'sidetrack-protected' ),
			array( $this, 'sidetrack_protected_remember_me_lifetime_field' ),
			$this->options_group,
			'password_protected'
		);

		register_setting( $this->options_group, 'sidetrack_protected_status', 'intval' );
		register_setting( $this->options_group, 'sidetrack_protected_feeds', 'intval' );
		register_setting( $this->options_group, 'sidetrack_protected_rest', 'intval' );
		register_setting( $this->options_group, 'sidetrack_protected_administrators', 'intval' );
		register_setting( $this->options_group, 'sidetrack_protected_users', 'intval' );
		register_setting( $this->options_group, 'sidetrack_protected_password', array( $this, 'sanitize_sidetrack_protected_password' ) );
		register_setting( $this->options_group, 'sidetrack_protected_allowed_ip_addresses', array( $this, 'sanitize_ip_addresses' ) );
		register_setting( $this->options_group, 'sidetrack_protected_remember_me', 'boolval' );
		register_setting( $this->options_group, 'sidetrack_protected_remember_me_lifetime', 'intval' );

	}

	/**
	 * Sanitize Password Field Input
	 *
	 * @param   string $val  Password.
	 * @return  string        Sanitized password.
	 */
	public function sanitize_sidetrack_protected_password( $val ) {

		$old_val = get_option( 'sidetrack_protected_password' );

		if ( is_array( $val ) ) {
			if ( empty( $val['new'] ) ) {
				return $old_val;
			} elseif ( empty( $val['confirm'] ) ) {
				add_settings_error( 'sidetrack_protected_password', 'sidetrack_protected_password', __( 'New password not saved. When setting a new password please enter it in both fields.', 'sidetrack-protected' ) );
				return $old_val;
			} elseif ( $val['new'] != $val['confirm'] ) {
				add_settings_error( 'sidetrack_protected_password', 'sidetrack_protected_password', __( 'New password not saved. Password fields did not match.', 'sidetrack-protected' ) );
				return $old_val;
			} elseif ( $val['new'] == $val['confirm'] ) {
				add_settings_error( 'sidetrack_protected_password', 'sidetrack_protected_password', __( 'New password saved.', 'sidetrack-protected' ), 'updated' );
				return $val['new'];
			}
			return get_option( 'sidetrack_protected_password' );
		}

		return $val;

	}

	/**
	 * Sanitize IP Addresses
	 *
	 * @param   string $val  IP addresses.
	 * @return  string        Sanitized IP addresses.
	 */
	public function sanitize_ip_addresses( $val ) {

		$ip_addresses = explode( "\n", $val );
		$ip_addresses = array_map( 'sanitize_text_field', $ip_addresses );
		$ip_addresses = array_map( 'trim', $ip_addresses );
		$ip_addresses = array_map( array( $this, 'validate_ip_address' ), $ip_addresses );
		$ip_addresses = array_filter( $ip_addresses );

		$val = implode( "\n", $ip_addresses );

		return $val;

	}

	/**
	 * Validate IP Address
	 *
	 * @param   string $ip_address  IP Address.
	 * @return  string               Validated IP Address.
	 */
	private function validate_ip_address( $ip_address ) {

		return filter_var( $ip_address, FILTER_VALIDATE_IP );

	}

	/**
	 * Sidetrack Protected Section
	 */
	public function sidetrack_protected_settings_section() {

		echo '<p>' . __( 'Password protect your web site. Users will be asked to enter a password to view the site.', 'sidetrack-protected' ) . '<br />
			' . __( 'For more information about Sidetrack Protected settings, view the "Help" tab at the top of this page.', 'sidetrack-protected' ) . '</p>';

	}

	/**
	 * Password Protection Status Field
	 */
	public function sidetrack_protected_status_field() {

		echo '<label><input name="sidetrack_protected_status" id="sidetrack_protected_status" type="checkbox" value="1" ' . checked( 1, get_option( 'sidetrack_protected_status' ), false ) . ' /> ' . __( 'Enabled', 'sidetrack-protected' ) . '</label>';

	}

	/**
	 * Password Protection Permissions Field
	 */
	public function sidetrack_protected_permissions_field() {

		echo '<label><input name="sidetrack_protected_administrators" id="sidetrack_protected_administrators" type="checkbox" value="1" ' . checked( 1, get_option( 'sidetrack_protected_administrators' ), false ) . ' /> ' . __( 'Allow Administrators', 'sidetrack-protected' ) . '</label>';
		echo '<label><input name="sidetrack_protected_users" id="sidetrack_protected_users" type="checkbox" value="1" ' . checked( 1, get_option( 'sidetrack_protected_users' ), false ) . ' style="margin-left: 20px;" /> ' . __( 'Allow Logged In Users', 'sidetrack-protected' ) . '</label>';
		echo '<label><input name="sidetrack_protected_feeds" id="sidetrack_protected_feeds" type="checkbox" value="1" ' . checked( 1, get_option( 'sidetrack_protected_feeds' ), false ) . ' style="margin-left: 20px;" /> ' . __( 'Allow RSS Feeds', 'sidetrack-protected' ) . '</label>';
		echo '<label><input name="sidetrack_protected_rest" id="sidetrack_protected_rest" type="checkbox" value="1" ' . checked( 1, get_option( 'sidetrack_protected_rest' ), false ) . ' style="margin-left: 20px;" /> ' . __( 'Allow REST API Access', 'sidetrack-protected' ) . '</label>';

	}

	/**
	 * Password Field
	 */
	public function sidetrack_protected_password_field() {

		echo '<input type="password" name="sidetrack_protected_password[new]" id="sidetrack_protected_password_new" size="16" value="" autocomplete="off"> <span class="description">' . __( 'If you would like to change the password type a new one. Otherwise leave this blank.', 'sidetrack-protected' ) . '</span><br>
			<input type="password" name="sidetrack_protected_password[confirm]" id="sidetrack_protected_password_confirm" size="16" value="" autocomplete="off"> <span class="description">' . __( 'Type your new password again.', 'sidetrack-protected' ) . '</span>';

	}

	/**
	 * Allowed IP Addresses Field
	 */
	public function sidetrack_protected_allowed_ip_addresses_field() {

		echo '<textarea name="sidetrack_protected_allowed_ip_addresses" id="sidetrack_protected_allowed_ip_addresses" rows="3" class="large-text" />' . get_option( 'sidetrack_protected_allowed_ip_addresses' ) . '</textarea>';
		echo '<p class="description">' . esc_html__( 'Enter one IP address per line.', 'sidetrack-protected' ) . ' ' . esc_html( sprintf( __( 'Your IP is address %s.', 'sidetrack-protected' ), $_SERVER['REMOTE_ADDR'] ) ) . '</p>';

	}

	/**
	 * Remember Me Field
	 */
	public function sidetrack_protected_remember_me_field() {

		echo '<label><input name="sidetrack_protected_remember_me" id="sidetrack_protected_remember_me" type="checkbox" value="1" ' . checked( 1, get_option( 'sidetrack_protected_remember_me' ), false ) . ' /></label>';

	}

	/**
	 * Remember Me lifetime field
	 */
	public function sidetrack_protected_remember_me_lifetime_field() {

		echo '<label><input name="sidetrack_protected_remember_me_lifetime" id="sidetrack_protected_remember_me_lifetime" type="number" value="' . get_option( 'sidetrack_protected_remember_me_lifetime', 14 ) . '" /></label>';

	}

	/**
	 * Pre-update 'sidetrack_protected_password' Option
	 *
	 * Before the password is saved, MD5 it!
	 * Doing it in this way allows developers to intercept with an earlier filter if they
	 * need to do something with the plaintext password.
	 *
	 * @param   string $newvalue  New Value.
	 * @param   string $oldvalue  Old Value.
	 * @return  string             Filtered new value.
	 */
	public function pre_update_option_sidetrack_protected_password( $newvalue, $oldvalue ) {

		global $sidetrack_protected;

		if ( $newvalue != $oldvalue ) {
			$newvalue = $sidetrack_protected->encrypt_password( $newvalue );
		}

		return $newvalue;

	}

	/**
	 * Plugin Row Meta
	 *
	 * Adds GitHub and translate links below the plugin description on the plugins page.
	 *
	 * @param   array  $plugin_meta  Plugin meta display array.
	 * @param   string $plugin_file  Plugin reference.
	 * @param   array  $plugin_data  Plugin data.
	 * @param   string $status       Plugin status.
	 * @return  array                 Plugin meta array.
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

		if ( 'sidetrack-protected/sidetrack-protected.php' == $plugin_file ) {
			$plugin_meta[] = sprintf( '<a href="%s">%s</a>', __( 'http://github.com/benhuson/sidetrack-protected', 'sidetrack-protected' ), __( 'GitHub', 'sidetrack-protected' ) );
			$plugin_meta[] = sprintf( '<a href="%s">%s</a>', __( 'https://translate.wordpress.org/projects/wp-plugins/sidetrack-protected', 'sidetrack-protected' ), __( 'Translate', 'sidetrack-protected' ) );
		}

		return $plugin_meta;

	}

	/**
	 * Plugin Action Links
	 *
	 * Adds settings link on the plugins page.
	 *
	 * @param   array $actions  Plugin action links array.
	 * @return  array            Plugin action links array.
	 */
	public function plugin_action_links( $actions ) {

		$actions[] = sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=sidetrack-protected' ), __( 'Settings', 'sidetrack-protected' ) );
		return $actions;

	}

	/**
	 * Password Admin Notice
	 * Warns the user if they have enabled password protection but not entered a password
	 */
	public function sidetrack_protected_admin_notices() {

		global $sidetrack_protected;

		// Check Support
		$screens = $this->plugin_screen_ids( array( 'dashboard', 'plugins' ) );
		if ( $this->is_current_screen( $screens ) ) {
			$supported = $sidetrack_protected->is_plugin_supported();
			if ( is_wp_error( $supported ) ) {
				echo $this->admin_error_display( $supported->get_error_message( $supported->get_error_code() ) );
			}
		}

		// Settings
		if ( $this->is_current_screen( $this->plugin_screen_ids() ) ) {
			$status = get_option( 'sidetrack_protected_status' );
			$pwd = get_option( 'sidetrack_protected_password' );

			if ( (bool) $status && empty( $pwd ) ) {
				echo $this->admin_error_display( __( 'You have enabled password protection but not yet set a password. Please set one below.', 'sidetrack-protected' ) );
			}

			if ( current_user_can( 'manage_options' ) && ( (bool) get_option( 'sidetrack_protected_administrators' ) || (bool) get_option( 'sidetrack_protected_users' ) ) ) {
				if ( (bool) get_option( 'sidetrack_protected_administrators' ) && (bool) get_option( 'sidetrack_protected_users' ) ) {
					echo $this->admin_error_display( __( 'You have enabled password protection and allowed administrators and logged in users - other users will still need to enter a password to view the site.', 'sidetrack-protected' ) );
				} elseif ( (bool) get_option( 'sidetrack_protected_administrators' ) ) {
					echo $this->admin_error_display( __( 'You have enabled password protection and allowed administrators - other users will still need to enter a password to view the site.', 'sidetrack-protected' ) );
				} elseif ( (bool) get_option( 'sidetrack_protected_users' ) ) {
					echo $this->admin_error_display( __( 'You have enabled password protection and allowed logged in users - other users will still need to enter a password to view the site.', 'sidetrack-protected' ) );
				}
			}
		}

	}

	/**
	 * Admin Error Display
	 *
	 * Returns a string wrapped in HTML to display an admin error.
	 *
	 * @param   string $string  Error string.
	 * @return  string           HTML error.
	 */
	private function admin_error_display( $string ) {

		return '<div class="error"><p>' . $string . '</p></div>';

	}

	/**
	 * Is Current Screen
	 *
	 * Checks wether the admin is displaying a specific screen.
	 *
	 * @param   string|array $screen_id  Admin screen ID(s).
	 * @return  boolean
	 */
	public function is_current_screen( $screen_id ) {

		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();
			if ( ! is_array( $screen_id ) ) {
				$screen_id = array( $screen_id );
			}
			if ( in_array( $current_screen->id, $screen_id ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Plugin Screen IDs
	 *
	 * @param   string|array $screen_id  Additional screen IDs to add to the returned array.
	 * @return  array                     Screen IDs.
	 */
	public function plugin_screen_ids( $screen_id = '' ) {

		$screen_ids = array( 'options-' . $this->options_group, 'settings_page_' . $this->options_group );

		if ( ! empty( $screen_id ) ) {
			if ( is_array( $screen_id ) ) {
				$screen_ids = array_merge( $screen_ids, $screen_id );
			} else {
				$screen_ids[] = $screen_id;
			}
		}

		return $screen_ids;

	}

}
