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
	private static $blockchains = array(
		'BTC'  => 'Bitcoin',
		'ETH'  => 'Ethereum',
		'BCH'  => 'Bitcoin Cash',
		'DASH' => 'Dash',
	);
	private static $blockchainLinks = array(
		'BTC'  => 'https://blockchain.info/tx/[txid]',
		'ETH'  => 'https://etherscan.io/tx/[txid]',
		'BCH'  => 'https://blockdozer.com/insight/tx/[txid]',
		'DASH' => 'https://live.blockcypher.com/dash/tx/[txid]',
	);
	private static $defaultPostSignature = 'This post has been stamped on the [blockchain] blockchain via <a target="_blank" href="https://stampd.io">stampd.io</a> on [date]. Using the SHA256 hashing algorithm on the content of the post produced the following hash [hash]. The ID of the pertinent transaction is [txid]. <a target="_blank" href="[txlink]">View the transaction on a blockchain explorer.</a>';

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
			'renderAPICredentialsOptionHeader'
		), $admin_page_slug );

		// add inputs
		$input_slug = $this::$pluginPrefix . 'client_id';
		add_settings_field( $input_slug, __( 'Client ID', 'stampd' ), array(
			$this,
			'renderClientIDInput'
		), $admin_page_slug, $api_creds_section_slug );
		register_setting( $api_creds_section_slug, $input_slug );

		$input_slug = $this::$pluginPrefix . 'secret_key';
		add_settings_field( $input_slug, __( 'Secret Key', 'stampd' ), array(
			$this,
			'renderSecretKeyInput'
		), $admin_page_slug, $api_creds_section_slug );
		register_setting( $api_creds_section_slug, $input_slug, array( $this, 'sanitizeSecretKey' ) );

		$general_settings_section_slug = $this::$pluginPrefix . 'general_settings';
		add_settings_section( $general_settings_section_slug, __( 'General Settings', 'stampd' ), array(
			$this,
			'renderGeneralSettingsOptionHeader'
		), $admin_page_slug );

		$input_slug = $this::$pluginPrefix . 'blockchain';
		add_settings_field( $input_slug, __( 'Blockchain', 'stampd' ), array(
			$this,
			'renderBlockchainSelect'
		), $admin_page_slug, $general_settings_section_slug );
		register_setting( $general_settings_section_slug, $input_slug );

		$input_slug = $this::$pluginPrefix . 'enable_post_signature';
		add_settings_field( $input_slug, __( 'Post Signature', 'stampd' ), array(
			$this,
			'renderPostSignatureCheckbox'
		), $admin_page_slug, $general_settings_section_slug );
		register_setting( $general_settings_section_slug, $input_slug );

		$input_slug = $this::$pluginPrefix . 'signature_text';
		add_settings_field( $input_slug, __( 'Signature Text', 'stampd' ), array(
			$this,
			'renderSignatureTextTextarea'
		), $admin_page_slug, $general_settings_section_slug );
		register_setting( $general_settings_section_slug, $input_slug );
	}

	/*
	 * Sanitize secret key
	 *
	 * @param $value string
	 */
	function sanitizeSecretKey( $value ) {

		$client_id  = get_option( $this::$pluginPrefix . 'client_id' );
		$secret_key = $value;

		$init       = $this->_APIInitCall( $client_id, $secret_key );
		$valid_init = $this->_isValidLogin( $init );

		if ( ! $valid_init ) {
			$input_slug = $this::$pluginPrefix . 'secret_key';
			add_settings_error(
				$input_slug,
				esc_attr( 'settings_updated' ),
				__( 'Invalid credentials', 'stampd' ),
				'error'
			);
		}

		return $value;
	}

	/*
	 * Post sig textbox
	 */
	function renderSignatureTextTextarea() {
		$slug = $this::$pluginPrefix . 'signature_text';
		?>
        <textarea name="<?php echo $slug; ?>" id="<?php echo $slug; ?>" class="large-text code"
                  rows="5"><?php echo get_option( $slug, $this::$defaultPostSignature ); ?></textarea>
        <p class="description">
			<?php _e( 'These shortcodes will be replaced with actual values when used within the signature text field:', 'stampd' ); ?>
        </p>
        <ul>
            <li><strong>[hash]</strong> <?php _e( 'hash result from applying SHA256 to the content', 'stampd' ); ?></li>
            <li><strong>[date]</strong> <?php _e( 'stamping date', 'stampd' ); ?></li>
            <li><strong>[blockchain]</strong> <?php _e( 'selected blockchain', 'stampd' ); ?></li>
            <li><strong>[txid]</strong> <?php _e( 'transaction ID', 'stampd' ); ?></li>
            <li><strong>[txlink]</strong> <?php _e( 'link to the transaction on a blockchain explorer', 'stampd' ); ?>
            </li>
        </ul>
		<?php
	}

	/*
	 * Post sig checkbox
	 */
	function renderPostSignatureCheckbox() {
		$slug = $this::$pluginPrefix . 'enable_post_signature';
		?>
        <label for="<?php echo $slug; ?>">
            <input name="<?php echo $slug; ?>" type="checkbox" id="<?php echo $slug; ?>"
                   value="enable" <?php echo get_option( $slug ) !== '' ? 'checked' : ''; ?>>
			<?php _e( 'The following signature will be appended to every stamped post', 'stampd' ); ?>
        </label>
		<?php
	}

	/*
	 * Client ID input
	 */
	function renderClientIDInput() {
		$slug = $this::$pluginPrefix . 'client_id';
		?>
        <input autocomplete="false" type="text" name="<?php echo $slug; ?>"
               id="<?php echo $slug; ?>"
               value="<?php echo get_option( $slug ); ?>"/>
		<?php
	}

	/*
     * Secret key input
     */
	function renderSecretKeyInput() {
		$slug = $this::$pluginPrefix . 'secret_key';
		?>
        <input autocomplete="new-password" type="password" name="<?php echo $slug; ?>"
               id="<?php echo $slug; ?>"
               value="<?php echo get_option( $slug ); ?>"/>
		<?php
	}

	/*
     * Blockchain select
     */
	function renderBlockchainSelect() {
		$client_id_input_slug = $this::$pluginPrefix . 'blockchain';
		?>
        <select name="<?php echo $client_id_input_slug; ?>" id="<?php echo $client_id_input_slug; ?>">
			<?php
			foreach ( $this::$blockchains as $blockchain_id => $blockchain_name ) {
				?>
                <option value="<?php echo $blockchain_id; ?>" <?php selected( get_option( $client_id_input_slug ), $blockchain_id ); ?>><?php echo $blockchain_name; ?></option>
				<?php
			}
			?>
        </select>
		<?php
	}

	/*
	 * API creds opts header
	 */
	function renderAPICredentialsOptionHeader() {
		echo __( 'Get your client ID and secret key on <a href="https://stampd.io" target="_blank">stampd.io</a>.', 'stampd' );
	}

	/*
	 * General settings opts header
	 */
	function renderGeneralSettingsOptionHeader() {
		echo __( 'General plugin settings', 'stampd' );
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
		add_action( 'save_post', array( $this, 'savePostMetabox' ), 9999, 2 );
	}

	/*
	 * Save post //  $this::$pluginPrefix . 'post_metabox'
	 */
	function savePostMetabox( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( ! isset( $_POST[ $this::$pluginPrefix . 'nonce' ] ) || ! isset( $_POST[ $this::$pluginPrefix . 'stamp_btn' ] ) ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( $_POST[ $this::$pluginPrefix . 'nonce' ], $this::$pluginPrefix . 'post_metabox' ) ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_page', $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

//		add_settings_error(
//			$input_slug,
//			esc_attr( 'settings_updated' ),
//			__( 'Invalid credentials', 'stampd' ),
//			'error'
//		);

		global $post;
		echo 'metabox save success';
		echo $post->post_content;
		die();

		return $post_id;

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
		require_once 'templates/post-metabox.php';
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