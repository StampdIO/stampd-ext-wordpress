<div class="wrap">
    <h1><?php echo __( 'Stampd.io Options', 'stampd' ); ?></h1>
    <form method="post" action="options.php" autocomplete="off">
		<?php

		settings_fields( 'stampd_ext_wp_api_credentials' );
		do_settings_sections( 'stampd_ext_wp_plugin_options' );
		submit_button();

		var_dump( $_REQUEST );

		?>
    </form>
</div>