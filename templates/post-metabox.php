<?php

global $post;

wp_nonce_field( 'stampd_ext_wp_post_metabox', 'stampd_ext_wp_nonce' );

?>
<div class="inside inside--actual">
    <label for="stampd_ext_wp_hash"><?php _e( 'SHA256 hash derived from last save', 'stampd' ); ?></label>
    <input id="stampd_ext_wp_hash" type="text" autocomplete="off"
           placeholder="<?php _e( 'Hash not calculated yet', 'stampd' ); ?>"
           name="stampd_ext_wp_hash" value="<?php echo hash( 'sha256', $post->post_content ); ?>" readonly>
</div>
<div id="major-publishing-actions">

    <div id="delete-action" class="js-stampd-post-changed" style="display:none;">
	    <?php _e( 'Please <strong>update</strong> the post', 'stampd' ); ?>
    </div>

    <div id="publishing-action">
        <input name="stampd_ext_wp_stamp_btn" type="submit" class="button button-primary button-large"
               id="stampd_ext_wp_stamp_btn" value="<?php _e( 'Stamp', 'stampd' ); ?>">
		<?php /* onclick="return confirm('<?php _e( 'Please ensure that you have saved your post and that it will remain unchanged. Changing the content of this post will invalidate the stamping.', 'stampd' ); ?>');" */ ?>
    </div>
    <div class="clear"></div>
</div>