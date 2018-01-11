<?php

/**
 * Renders the privatenote metabox
 *
 * @since 1.0.0
 *
 * @param (object)  $post
 * @return (void)
 **/
function sts_metabox_privatenote_render( $post ) {
	?>
	<div class="ticket-privatenote">
		<textarea name="t[privatenote]"><?php echo get_post_meta( $post->ID, 'ticket-privatenote', true ); ?></textarea>
		<small><?php _e( 'Private notes can only be seen by other agents and admins. Not by the ticket owner himself.', 'support-ticket' ); ?></small>
		<button class="button button-primary button-large"><?php _e( 'Update', 'support-ticket' ); ?></button>
	</div>
	<?php
}

/**
 * Update the private note
 *
 * @since 1.0.0
 *
 * @param (array)   $post_data  the POST data
 * @param (int)     $post_id    the post ID
 * @return (void)
 **/
add_action( 'ticket-admin-update', 'sts_admin_update_privatenote', 10, 2 );
function sts_admin_update_privatenote( $post_data, $post_id ) {
	if ( isset( $post_data['privatenote'] ) ) {
		update_post_meta( $post_id, 'ticket-privatenote', $post_data['privatenote'] );
	}
}
