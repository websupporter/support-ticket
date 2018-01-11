<?php

/**
 * Update the ticket status
 *
 * @since 1.0.0
 *
 * @param (array)   $post_data  the POST data
 * @param (int)     $post_id    the post ID
 *
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
 * Renders the status metabox.
 *
 * @since 1.0.0
 **/
function sts_metabox_status_render() {
	$current_status_index = (int) get_post_meta( get_the_ID(), 'ticket-status', true );
	$current_status       = sts_translate_status( $current_status_index );
	$status_array         = sts_get_status_arr();
	$settings             = get_option( 'sts-core-settings' );
	$current_agent_id     = (int) get_post_meta( get_the_ID(), 'ticket-agent', true );
	if ( ! $current_agent_id ) {
		$current_agent_id = ( isset( $settings['user']['standard-agent'] ) ) ? $settings['user']['standard-agent'] : 1;
	}
	$current_agent = get_user_by( 'ID', $current_agent_id );
	$agents        = sts_get_possible_agents();

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
		if ( current_user_can( 'update_tickets' ) ) :
			sts_print_select(
				'ticket-status',
				't[ticket-status]',
				__( 'Update status', 'support-ticket' ),
				$current_status_index,
				array_map( function( $label, $value ) {
					return (object) array(
						'value' => $value,
						'label' => $label,
					);
				}, $status_array, array_keys( $status_array ) )
			);
		endif;
		if ( current_user_can( 'assign_agent_to_ticket' ) ) :
			sts_print_select(
				'ticket-agent',
				't[ticket-agent]',
				__( 'Current agent', 'support-ticket' ),
				$current_agent->ID,
				array_map( function( $agent ) {
					return (object) array(
						'value' => $agent->ID,
						'label' => $agent->display_name,
					);
				}, $agents )
			);
		endif;
		?>
		<button class="button button-primary button-large"><?php _e( 'Update', 'support-ticket' ); ?></button>
	<?php
	endif;
}
