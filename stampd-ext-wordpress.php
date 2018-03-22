<?php

/*
Plugin Name: Stampd.io Blockchain Stamping
Plugin URI: https://stampd.io
Description: A blockchain stamping plugin that helps protect your rights in your digital creations by posting a unique imprint of your posts on the blockchain.
Author: stampd.io
Version: 1.0
Author URI: https://stampd.io
*/

class StampdExtWordpress {

	// Statics
	private static $pluginVersion = '1.0';
	private static $pluginPrefix = 'stampd_ext_wp_';
	private static $APIBaseURL = 'http://dev.stampd.io/api/v2';

	function __construct() {
		$this->hookOptions();
		$this->_loadAssets();
		$this->_hookAdminSettingsPage();
		$this->_hookMetaboxes();
		$this->_addPluginSettingsLink();
	}

	/*
	 * Add plugin settings link
	 */
	private function _addPluginSettingsLink() {
		$plugin = plugin_basename( __FILE__ );
		add_filter( 'plugin_action_links_' . $plugin, array( $this, 'pluginSettingsLinkFilter' ) );
	}

	/*
	 * Display plugin settings link
	 */
	function pluginSettingsLinkFilter( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this::$pluginPrefix . 'plugin_options">' . __( 'Settings' ) . '</a>';
		array_push( $links, $settings_link );

		return $links;
	}

	/*
	 * Hook options
	 */
	private function hookOptions() {
		add_action( 'admin_init', array( $this, 'loadOptions' ) );
	}

	/*
	 * Load options
	 */
	function loadOptions() {
		$admin_page_slug = $this::$pluginPrefix . 'plugin_options';

		// add sections
		$api_creds_section_slug = $this::$pluginPrefix . 'api_credentials';
		add_settings_section( $api_creds_section_slug, __( 'API Credentials', 'stampd' ), array(
			$this,
			'displayAPICredentialsOptionHeader'
		), $admin_page_slug );

		// add inputs
		$input_slug = $this::$pluginPrefix . 'client_id';
		add_settings_field( $input_slug, __( 'Client ID', 'stampd' ), array(
			$this,
			'displayClientIDInput'
		), $admin_page_slug, $api_creds_section_slug );
		register_setting( $api_creds_section_slug, $input_slug );

		$input_slug = $this::$pluginPrefix . 'secret_key';
		add_settings_field( $input_slug, __( 'Secret Key', 'stampd' ), array(
			$this,
			'displaySecretKeyInput'
		), $admin_page_slug, $api_creds_section_slug );
		register_setting( $api_creds_section_slug, $input_slug, array( $this, 'sanitizeSecretKey' ) );
	}

	/*
	 * Sanitize secret key
	 */
	function sanitizeSecretKey( $value ) {
		// TODO: call init
		$client_id  = get_option( $this::$pluginPrefix . 'client_id' );
		$secret_key = $value;
		$init       = $this->_APIInitCall( $client_id, $secret_key );
		$valid_init = $this->_isValidLogin( $init );

		if ( ! $valid_init ) {
			$input_slug = $this::$pluginPrefix . 'client_id';
			update_option( $input_slug, '' );

			$input_slug = $this::$pluginPrefix . 'secret_key';
			add_settings_error(
				$input_slug,
				esc_attr( 'settings_updated' ),
				__( 'Invalid credentials', 'stampd' ),
				'error'
			);
		}
	}

	/*
	 * Client ID input
	 */
	function displayClientIDInput() {
		$client_id_input_slug = $this::$pluginPrefix . 'client_id';
		?>
        <input type="text" name="<?php echo $client_id_input_slug; ?>" id="<?php echo $client_id_input_slug; ?>"
               value="<?php echo get_option( $client_id_input_slug ); ?>"/>
		<?php
	}

	/*
     * Secret key input
     */
	function displaySecretKeyInput() {
		$client_id_input_slug = $this::$pluginPrefix . 'secret_key';
		?>
        <input type="password" name="<?php echo $client_id_input_slug; ?>" id="<?php echo $client_id_input_slug; ?>"
               value="<?php echo get_option( $client_id_input_slug ); ?>"/>
		<?php
	}

	/*
	 * API creds opts header
	 */
	function displayAPICredentialsOptionHeader() {
		echo __( 'Get your client ID and secret key on <a href="https://stampd.io" target="_blank">stampd.io</a>.', 'stampd' );
	}

	/*
	 * Hook admin settings page
	 */
	private function _hookAdminSettingsPage() {
		add_action( 'admin_menu', array( $this, 'addAdminMenuItems' ) );
	}

	/*
	 * Add admin menu items
	 */
	function addAdminMenuItems() {
		add_submenu_page(
			'options-general.php',
			'Stampd.io',
			'Stampd.io',
			'manage_options',
			$this::$pluginPrefix . 'plugin_options',
			array(
				$this,
				'displayAdminSettingsPage'
			),
			"",
			100
		);
	}

	/*
	 * Display admin settings page
	 */
	function displayAdminSettingsPage() {
		require_once 'templates/admin-settings-page.php';
	}

	/*
	 * Hook metaboxes to actions
	 */
	private function _hookMetaboxes() {
		if ( is_admin() ) {
			add_action( 'load-post.php', array( $this, 'hookPostMetabox' ) );
			add_action( 'load-post-new.php', array( $this, 'hookPostMetabox' ) );
		}
	}

	/*
	 * Hook post metabox
	 */
	function hookPostMetabox() {
		add_action( 'add_meta_boxes', array( $this, 'addPostMetabox' ) );
//		add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
	}

	/*
	 * Add post metabox
	 */
	function addPostMetabox() {
		add_meta_box(
			$this::$pluginPrefix . 'post_metabox',
			__( 'Stampd.io Blockchain Stamping', 'stampd' ),
			array( $this, 'renderPostMetabox' ),
			array( 'post', 'page' ),
			'side',
			'default'
		);
	}

	function renderPostMetabox() {
		echo 'testing1';
		// wp_nonce_field( 'custom_nonce_action', 'custom_nonce' );
	}

	/*
	 * Load all assets CSS, JS
	 */
	private function _loadAssets() {
		add_action( 'wp_enqueue_scripts', array( $this, 'loadFrontCSS' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'loadAdminCSS' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'loadAdminJS' ) );
	}

	/*
	 * Load front CSS
	 * @note can't be private because it is hooked
	 */
	function loadFrontCSS() {
		$style_name = $this::$pluginPrefix . 'front_css';
		wp_register_style( $style_name, plugins_url( '/assets/css/front.min.css', __FILE__ ), false, $this::$pluginVersion );
		wp_enqueue_style( $style_name );
	}

	/*
	 * Load admin CSS
	 * @note can't be private because it is hooked
	 */
	function loadAdminCSS() {
		$style_name = $this::$pluginPrefix . 'admin_css';
		wp_register_style( $style_name, plugins_url( '/assets/css/admin.min.css', __FILE__ ), false, $this::$pluginVersion );
		wp_enqueue_style( $style_name );
	}

	/*
	 * Load admin JS
	 * @note can't be private because it is hooked
	 */
	function loadAdminJS() {
		$script_name = $this::$pluginPrefix . 'admin_js';
		wp_enqueue_script( $script_name, plugins_url( '/assets/js/admin.min.js', __FILE__ ), array( 'jquery' ), $this::$pluginVersion );
	}

	/*
	 * Perform post call
	 *
	 * @param $url string
	 * @param $fields array of strings
	 * @return JSON, false
	 */
	private function _performPostCall( $url, $fields = array() ) {
		$fields_string = '';

		foreach ( $fields as $key => $value ) {
			$fields_string .= $key . '=' . $value . '&';
		}
		$fields_string = rtrim( $fields_string, '&' );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POST, count( $fields ) );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields_string );
		$res = curl_exec( $ch );
		curl_close( $ch );

		return json_decode( $res );
	}

	/*
     * Perform get call
     *
     * @param $url string
     * @return JSON, false
     */
	private function _performGetCall( $url ) {
		$res = file_get_contents( $url );

		return json_decode( $res );
	}

	/*
	 * API /init
	 *
	 * @param $client_id string
	 * @param $secret_key string
	 * @return _performGetCall
	 */
	private function _APIInitCall( $client_id, $secret_key ) {
		$url = $this::$APIBaseURL . '/init?client_id=' . $client_id . '&secret_key=' . $secret_key;

		return $this->_performGetCall( $url );
	}

	/*
	 * Check init validity
	 *
	 * @return boolean
	 */
	private function _isValidLogin( $init_res ) {
		if ( $init_res && is_object( $init_res ) && property_exists( $init_res, 'code' ) && ( in_array( $init_res->code, array(
				200, // already logged
				300 // success login
			) ) )
		) {
			return true;
		} else {
			return false;
		}
	}
}

// Init the plugin
$_StampdExtWordpress = new StampdExtWordpress();