<?php

/**
 * Update the ticket status
 *
 * @since 1.0.0
 *
 * @param (array)   $post_data  the POST data
 * @param (int)     $post_id    the post ID
 * @return (void)
 **/
function sts_admin_update_status( $post_data, $post_id ) {
	if ( ! current_user_can( 'update_tickets' ) && ! current_user_can( 'assign_agent_to_ticket' ) ) {
		return;
	}

	if ( current_user_can( 'update_tickets' )
		&& isset( $post_data['ticket-status'] )
		&& is_numeric( $post_data['ticket-status'] )
	) {
		$status_update = update_post_meta( $post_id, 'ticket-status', $post_data['ticket-status'] );
	}

	if ( current_user_can( 'assign_agent_to_ticket' )
		&& isset( $post_data['ticket-agent'] )
		&& is_numeric( $post_data['ticket-agent'] )
	) {
		$agent_update = update_post_meta( $post_id, 'ticket-agent', $post_data['ticket-agent'] );
	}

	/**
	 * Status has been updated
	 *
	 * @since 1.0.0
	 *
	 * @param (int)      Ticket ID
	 * @param (array)    POST data
	 */
	if ( isset( $status_update ) && false !== $status_update ) {
		do_action( 'sts-ticket-status-updated', $post_id, $post_data );
	}

	/**
	 * Assigned agent changed
	 *
	 * @since 1.0.0
	 *
	 * @param (int)      Ticket ID
	 * @param (array)    POST data
	 */
	if ( isset( $agent_update ) && false !== $agent_update ) {
		do_action( 'sts-ticket-agent-updated', $post_id, $post_data );
	}
}
add_action( 'ticket-admin-update', 'sts_admin_update_status', 10, 2 );

/**
 * Renders the status metabox
 *
 * @since 1.0.0
 *
 * @param (object)  $post
 * @return (void)
 **/
function sts_metabox_status_render( $post ) {
	$current_status_index = (int) get_post_meta( get_the_ID(), 'ticket-status', true );
	$current_status       = sts_translate_status( $current_status_index );
	$status_array         = sts_get_status_arr();

	$standard_agent = 1;
	$settings       = get_option( 'sts-core-settings' );
	if ( isset( $settings['user']['standard-agent'] ) ) {
		$standard_agent = $settings['user']['standard-agent'];
	}
	$current_agent = (int) get_post_meta( get_the_ID(), 'ticket-agent', true );

	if ( ! $current_agent ) {
		$current_agent = $standard_agent;
	}
	$agents = sts_get_possible_agents();
	foreach ( $agents as $agent ) {
		if ( ! is_string( $agent ) && $agent->ID == $current_agent ) {
			$current_agent = $agent;
			break;
		}
	}

	if ( ! current_user_can( 'update_tickets' ) && ! current_user_can( 'assign_agent_to_ticket' ) ) :
		?>
		<p>
			<?php
			// translators: %s is the current status.
			printf( __( 'Current status: %s', 'support-ticket' ), $current_status );
			?>
		</p>
		<p>
			<?php
			// translators: %s is the name of the current agent.
			printf( __( 'Current agent: %s', 'support-ticket' ), $current_agent->display_name );
			?>
		</p>
		<?php
	else :
		?>
		<?php if ( current_user_can( 'update_tickets' ) ) : ?>
		<p>
			<label for="ticket-status"><?php _e( 'Update status', 'support-ticket' ); ?>:</label>
			<select id="ticket-status" name="t[ticket-status]">
				<?php foreach ( $status_array as $status_index => $status ) : ?>
					<option <?php selected( $status_index, $current_status_index ); ?> value="<?php echo $status_index; ?>">
						<?php echo $status; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	endif;
if ( current_user_can( 'assign_agent_to_ticket' ) ) :
	?>
	<p>
		<label for="ticket-agent"><?php _e( 'Current agent', 'support-ticket' ); ?>:</label>
		<select id="ticket-agent" name="t[ticket-agent]">
			<?php foreach ( $agents as $agent ) : ?>
						<?php if ( is_string( $agent ) ) : ?>
							<option disabled>----</option>
						<?php else : ?>
							<option <?php selected( $current_agent->ID, $agent->ID ); ?> value="<?php echo $agent->ID; ?>">
								<?php echo $agent->display_name; ?>
							</option>
						<?php endif; ?>
					<?php endforeach; ?>
		</select>
			</p>
		<?php endif; ?>
		<button class="button button-primary button-large"><?php _e( 'Update', 'support-ticket' ); ?></button>
		<?php
	endif;
}
