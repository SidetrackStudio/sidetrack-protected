<?php

defined( 'ABSPATH' ) || die( 'File cannot be accessed directly' );

class Sidetrack_IP_AJAX {

	public static function init() {
		add_action( 'wp_ajax_add_new_ip_action', array( __CLASS__, 'add_new_ip' ) );
		add_action( 'wp_ajax_addnew_ip_action', array( __CLASS__, 'add_new_ip_function' ) );
		add_action( 'admin_menu', array( __CLASS__, 'ip_access_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'add_new_ip_scripts' ) );
	}

	public static function add_new_ip() {
		$to_add = $_POST;
		$unrestricted_ip = get_option( 'unrestricted_ip' );
		if ( ! filter_var( $to_add['current_ip'], FILTER_VALIDATE_IP ) ) {
			$to_add['message'] = 'Not a Valid IP';
		} elseif ( ! in_array( $to_add['current_ip'], $unrestricted_ip ) ) {
			$unrestricted_ip[] = $to_add['current_ip'];
			$to_add['message'] = 'IP added to array of Open IPs<br>Refresh page to see full list';
		} else {
			$to_add['message'] = 'IP address is already in the list of Open IPs';
		}
			update_option( 'unrestricted_ip', $unrestricted_ip );
		// }
		$to_add['function'] = __FUNCTION__;
		echo '<h4>' . $to_add['message'] . '</h4>';
		// echo '<pre>';
		// print_r( $to_add );
		// echo '</pre>';
		exit;
	}

	public static function add_new_ip_function() {
		$new_ip = $_POST;
		set_transient( 'open_access', $new_ip['new_ip'] );
		$new_ip['added'] = 'new one added';
		echo '<pre>';
		print_r( $new_ip );
		echo '</pre>';
		exit();
	}

	public static function ip_access_admin_menu() {
		global $add_new_ip_settings;
		$add_new_ip_settings = add_dashboard_page( __( 'Add IPs', 'add-new-ip' ), __( 'Add IPs', 'add-new-ip' ), 'manage_options', 'add-ip-ajax.php', array( __CLASS__, 'ip_access_admin_page' ) );
	}

	public static function ip_access_admin_page() {
		$show_ip = self::get_and_validate_ip_address();
		$accepted = get_transient( 'accepted' );
		?>
		<div class="wrap">
			<h2><?php esc_attr_e( 'Add IPs to Provide Open Access', 'add-new-ip' ); ?></h2>
		</div>
		<h3>Add the organization IP in the window below to add it to the list.</h3>
		<p class="description">Refresh the page to see the new IP added.</p>	<p class="description">Don't forget to test!</p>
		<?php
		$unrestricted_ip = get_option( 'unrestricted_ip' );
		if ( ! empty( $unrestricted_ip ) ) {
			foreach ( $unrestricted_ip as $key => $value ) {
				echo '<input class="open-ip" disabled name="open-ip-' . $key . '" id="open-ip-' . $key . '" value="' . $value . '" /><br>';
			}
		}

		?>

		<div id="add-new-ip-results"></div>
		<form id="add-new-ip-form" method="POST" style="padding: 2rem;">
		<div>
			<input type="text" name="get-current-ip" id="get-current-ip" value="<?php echo $show_ip; ?>"/>
			<input type="submit" name="add-new-ip-submit" id="add-new-ip-submit" class="button-primary" value="<?php esc_attr_e( 'Add this IP', 'add-new-ip' ); ?>"/>
			<img src="<?php echo esc_url( admin_url( '/images/wpspin_light.gif' ) ); ?>" class="waiting" id="add-new-ip-loading" style="display:none;"/>
		</div>
		</form>
		<h3 id="delete-transient-results"></h3>
		<form id="delete-transient-form">
			<label>I want to delete the transient.
  <input type="checkbox" name="del-tr" id="del-tr" value="1" >
			<input type="hidden" name="delete-transient" id="delete-transient" value="deleted"></label>
			<button id="delete-transient-submit" style="display: none;" class="button-primary">Delete Transient</button>
		</form>
	</div>
	<?php
	}

	public static function add_new_ip_scripts( $hook ) {
		global $add_new_ip_settings;
		if ( $hook !== $add_new_ip_settings ) {
			return;
		}
		wp_enqueue_script( 'add-new-ip', plugin_dir_url( dirname( __FILE__ ) ) . 'js/add-new-ip.js', array( 'jquery' ) );
		wp_localize_script(
			'add-new-ip', 'add_new_ip_object', array(
				'add_new_ip_nonce' => wp_create_nonce( 'add-new-ip-nonce' ),
				'add_new_ip_ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}
	public static function get_and_validate_ip_address() {
		$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' );
		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
					// trim for safety measures
					$ip = trim( $ip );
					// attempt to validate IP
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}
		return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : false;
	}

}
Sidetrack_IP_AJAX::init();
