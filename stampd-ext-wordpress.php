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

	private static $pluginVersion = '1.0';
	private static $pluginPrefix = 'stampd_ext_wp_';

	function __construct() {
		$this->_loadAssets();
	}

	/*
	 * Load all assets CSS, JS
	 */
	private function _loadAssets() {
		add_action( 'wp_enqueue_scripts', array( $this, '_loadFrontCSS' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_loadAdminCSS' ) );
		add_action( 'admin_enqueue_scripts', array( $this, '_loadAdminJS' ) );
	}

	/*
	 * Load front CSS
	 * @note can't be private because it is hooked
	 */
	function _loadFrontCSS() {
		$style_name = $this::$pluginPrefix . 'front_css';
		wp_register_style( $style_name, plugins_url( '/assets/css/front.min.css', __FILE__ ), false, $this::$pluginVersion );
		wp_enqueue_style( $style_name );
	}

	/*
	 * Load admin CSS
	 * @note can't be private because it is hooked
	 */
	function _loadAdminCSS() {
		$style_name = $this::$pluginPrefix . 'admin_css';
		wp_register_style( $style_name, plugins_url( '/assets/css/admin.min.css', __FILE__ ), false, $this::$pluginVersion );
		wp_enqueue_style( $style_name );
	}

	/*
	 * Load admin JS
	 * @note can't be private because it is hooked
	 */
	function _loadAdminJS() {
		$script_name = $this::$pluginPrefix . 'admin_js';
		wp_enqueue_script( $script_name, plugins_url( '/assets/js/admin.min.js', __FILE__ ), array( 'jquery' ), $this::$pluginVersion );
	}
}

// Init the plugin
$_StampdExtWordpress = new StampdExtWordpress();