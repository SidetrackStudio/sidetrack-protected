<?php

class Sidetrack_Customizer {
	public static function init() {
		add_action( 'login_enqueue_scripts', array( __CLASS__, 'enqueue_login_scripts_styles' ) );
		add_action( 'login_enqueue_scripts', array( __CLASS__, 'sidetrack_login_logo' ) );
	}


	public static function sidetrack_login_logo() {
		?>
		<style type="text/css">
		#login {
			margin-top: 3rem;
		}
		#login h1 a, .login h1 a {
			background-image: url(<?php echo plugins_url( 'assets/sidetrack-logo.png', dirname( __FILE__ ) ); ?>);
			height: auto;
			width: 320px;
			background-size: 320px auto;
			background-repeat: no-repeat;
			padding: 2rem 0;
			/*border: 1px solid purple;*/
			background-position-y: 1rem;
		}
	</style>
	<?php
	}
	public static function enqueue_login_scripts_styles() {
		wp_register_style( 'login-page', plugins_url( '/css/sidetrack-login.css', dirname( __FILE__ ) ) );
		wp_enqueue_style( 'login-page' );
	}
}
Sidetrack_Customizer::init();
