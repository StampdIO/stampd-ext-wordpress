<?php

/**
 * Admin settings page
 *
 * @version 1.0
 * @package stampd-ext-wordpress
 * @author Hypermetron (Minas Antonios)
 * @copyright Copyright (c) 2018, Minas Antonios
 * @license http://opensource.org/licenses/gpl-2.0.php GPL v2 or later
 */

?>
<div class="wrap">
    <h1><?php echo __( 'Stampd.io Options', 'stampd' ); ?></h1>
    <form method="post" action="options.php">
		<?php

		settings_fields( 'stampd_ext_wp_api_credentials' );
		settings_fields( 'stampd_ext_wp_general_settings' );
		do_settings_sections( 'stampd_ext_wp_plugin_options' );
		submit_button();

		?>
    </form>
</div>