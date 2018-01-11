<?php

/**
 * Renders the metafields metabox
 *
 * @since 1.0.0
 *
 * @param (stdClass)    $args   empty
 * @return (void)
 **/
function sts_metabox_metafields_render( $post ) {
	$metafields = get_option( 'sts-metafields', array() );
	?>
	<table class="wp-list-table widefat fixed striped">
		<thead>
		<tr><th><?php _e( 'Name', 'support-ticket' ); ?></th><th><?php _e( 'Value', 'support-ticket' ); ?></th></tr>
		</thead>
		<tbody>
		<?php foreach ( $metafields as $field ) : ?>
			<tr>
				<td><?php echo $field['label']; ?></td>
				<td>
					<?php echo get_post_meta( $post->ID, $field['metakey'], true ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Update the Ticket field settings
 *
 * Explanations on this filter can be found in admin/inc/functions-settings.php
 *
 *
 * @since 1.0.0
 *
 * @param (mixed)       $return
 * @return (mixed)      $return
 **/
function sts_settings_ticket_fields( $return ) {
	if ( ! isset( $_POST['ticket']['fields']['id'] ) ) {
		update_option( 'sts-metafields', array() );
		return $return;
	}

	$field_ids      = $_POST['ticket']['fields']['id'];
	$field_settings = $_POST['ticket']['fields']['fields'];

	$fields = array();
	if ( is_array( $field_ids ) ) {
		foreach ( $field_ids as $key => $val ) {
			$single     = json_decode( stripslashes( $field_settings[ $key ] ) );
			$single->id = $val;

			if ( 'input' === $single->tag ) {
				$single->type = 'text';
			}

			$single = (array) $single;
			foreach ( $single as $key => $val ) {
				if ( ! is_object( $val ) && ! is_array( $val ) ) {
					$single[ $key ] = sanitize_text_field( $val );
				} else {
					$val = (array) $val;
					foreach ( $val as $sub_key => $sub_val ) {
						$val[ $sub_key ] = sanitize_text_field( $sub_val );
					}
					$single[ $key ] = $val;
				}
			}

			$fields[] = $single;
		}
	}
	update_option( 'sts-metafields', $fields );
	return  $return;
}
add_filter( 'sts-settings-update-ticket', 'sts_settings_ticket_fields' );
