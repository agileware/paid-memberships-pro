<?php

defined( 'ABSPATH' ) || die( 'File cannot be accessed directly' );

class PMPro_Deny_Network_Activation {

	public function init() {
		register_activation_hook( PMPRO_BASE_FILE, array( $this, 'pmpro_check_network_activation' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_admin_style' ) );
		add_action( 'network_admin_notices', array( $this, 'display_message_after_network_activation_attempt' ) );

		// On the blog list page, show the plugins and theme active on each blog
		add_filter( 'manage_sites-network_columns', array( $this, 'add_sites_column' ), 10, 1 );
		add_action( 'manage_sites_custom_column', array( $this, 'manage_sites_custom_column' ), 10, 3 );
	}

	public function wp_admin_style() {
		global $current_screen;
		if ( 'sites-network' === $current_screen->id || 'plugins-network' === $current_screen->id ) {
	?>
		<style type="text/css">
			.notice.notice-info {
				background-color: #ffd;
			}
		</style>
	<?php
		}
	}

	public function display_message_after_network_activation_attempt() {
		global $current_screen;
		if ( !empty($_REQUEST['pmpro_deny_network_activation']) && ( 'sites-network' === $current_screen->id || 'plugins-network' === $current_screen->id ) ) {
				//get plugin data
				$plugin = isset($_REQUEST['pmpro_deny_network_activation']) ? $_REQUEST['pmpro_deny_network_activation'] : '';
				$plugin_path = WP_PLUGIN_DIR . '/' . urldecode($plugin);
				$plugin_data = get_plugin_data($plugin_path);

				if(!empty($plugin_data))
					$plugin_name = $plugin_data['Name'];
				else
					$plugin_name = '';

				//show notice
				echo '<div class="notice notice-info is-dismissible"><p>';
				$text = sprintf( __("The %s plugin should not be network activated. Activate on each individual site's plugin page.", 'paid-memberships-pro'), $plugin_name);
				echo $text;
				echo '</p></div>';
		}
	}

	public function pmpro_check_network_activation( $network_wide ) {
		if ( is_multisite() && ! $network_wide ) {
			return;
		}

		$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';

		deactivate_plugins( $plugin_slug, true, true );
		wp_redirect( network_admin_url( 'plugins.php?pmpro_deny_network_activation=' . $plugin ) );
		exit;
	}
}

$deny_network = new PMPro_Deny_Network_Activation();
$deny_network->init();
