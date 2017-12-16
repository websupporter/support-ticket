<?php
/**
 * Plugin Activation Hook
 * Register new User role
 *
 * @since 1.0.0
 */
function sts_on_plugin_activation() {

	$role = add_role(
		'ticket-user',
		__( 'Ticket User', 'support-ticket' ),
		array(
			'read'             => true,
			'read_own_tickets' => true,
		)
	);

	remove_role( 'ticket-agent' );

	$role = add_role(
		'ticket-agent',
		__( 'Ticket Agent', 'support-ticket' ),
		array(
			'read'                  => true,
			'read_own_tickets'      => true,
			'read_assigned_tickets' => true,
			'read_other_tickets'    => false,
			'update_tickets'        => true,
		)
	);

	$admin_role = get_role( 'administrator' );
	$admin_role->add_cap( 'read_own_tickets', true );
	$admin_role->add_cap( 'read_assigned_tickets', true );
	$admin_role->add_cap( 'read_other_tickets', true );
	$admin_role->add_cap( 'delete_other_tickets', true );
	$admin_role->add_cap( 'update_tickets', true );
	$admin_role->add_cap( 'assign_agent_to_ticket', true );
}
register_activation_hook( STS_FILE, 'sts_on_plugin_activation' );

/**
 * Add the metafields to the "create ticket"-form
 *
 * @since 1.0.0
 * @wp-hook sts-create-ticket-formfields
 *
 * @param array $fields the existing fields.
 *
 * @return array $fields the extended fields.
 **/
function sts_metafields( $fields ) {

	$add = get_option( 'sts-metafields', array() );

	foreach ( $add as $field ) {
		$type = '';
		if ( 'input' === $field['tag'] ) {
			$type = 'text';
		}

		$single = array(
			'label'    => $field['label'],
			'id'       => $field['id'],
			'tag'      => $field['tag'],
			'name'     => $field['metakey'],
			'metakey'  => $field['metakey'],
			'type'     => $type,
			'value'    => '',
			'error'    => false,
			'required' => false,
		);

		if ( 'select' === $field['tag'] ) {
			$choices_arr = $field['choices'];
			$choices     = array();
			foreach ( $choices_arr as $key => $val ) {
				$choices[ $val ] = $val;
			}
			$single['choices'] = $choices;
		}
		$fields[] = $single;
	}

	return $fields;
}
add_filter( 'sts-create-ticket-formfields', 'sts_metafields' );

/**
 * Saves the metadata on ticket create.
 *
 * @wp-hook sts-createticket-after
 * @param int $post_id
 * @param array $post_data
 */
function sts_save_metafields( $post_id, $post_data ) {

	$metafields = get_option( 'sts-metafields', array() );

	foreach ( $metafields as $field ) {
		if ( isset( $post_data[ $field['metakey'] ] ) ) {
			update_post_meta( $post_id, $field['metakey'], sanitize_text_field( $post_data[ $field['metakey'] ] ) );
		}
	}
}
add_action( 'sts-createticket-after', 'sts_save_metafields', 10, 2 );

/**
 * Sends an email to the ticket owner in case of a status update, e.g. when the ticket is closed.
 *
 * @since 1.0.7
 *
 * @wp-hook sts-ticket-status-updated
 * @param int   $post_id The post id.
 * @param array $post_data The post data.
 **/
function sts_send_status_update_mail( $post_id, $post_data ) {

	$settings = get_option( 'sts-core-settings' );
	if (
		! isset( $settings['email']['notifiy-on-status-update'] ) ||
		1 !== (int) $settings['email']['notifiy-on-status-update']
	) {
		return;
	}

	global $post;

	//Get the ticket
	$args = array(
		'post_type' => 'ticket',
		'p'         => $post_id,
	);

	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		return;
	}

	$query->the_post();

	$status = sts_get_status_arr();
	if ( ! isset( $status[ $post_data['ticket-status'] ] ) ) {
		return;
	}

	$status = $status[ $post_data['ticket-status'] ];

	$ticket_owner = get_user_by( 'id', $post->post_author );

	/**
	 * Filters the mail subject.
	 *
	 * @since 1.0.7
	 *
	 * @param string $subject the subject
	 * @param int   $post_id the ticket ID
	 * @param array $post_data the post data
	 *
	 * @return string $subject the subject
	 */
	$subject = apply_filters(
		'sts-status-update-mail-subject',
		// translators: %s is the ID of the ticket.
		sprintf( __( 'The status of your ticket #%s has changed', 'support-ticket' ), $post_id ),
		$post_id,
		$post_data
	);

	$text = __( 'Hello', 'support-ticket' ) . PHP_EOL .
				// translators: %s is the status of the ticket.
				sprintf( __( 'your ticket is now set to "%s"', 'support-ticket' ), $status ) .
				PHP_EOL . PHP_EOL;
	$text       .= sprintf( __( 'You can read your ticket here:', 'support-ticket' ) ) . ' ';
	$view_ticket = admin_url( 'admin.php?page=sts&action=single&ID=' . $post->ID );

	/**
	 * Filters the URL where the ticket can be read
	 *
	 * @since 1.0.7
	 *
	 * @param string $view_ticket the URL
	 *
	 * @return string $view_ticket the URL
	 */
	$view_ticket = apply_filters( 'sts-view-ticket-url', $view_ticket, $post->ID );

	$text .= '<a href="' . $view_ticket . '">' . $view_ticket . '</a>';

	/**
	 * Filters the text message
	 *
	 * @since 1.0.7
	 *
	 * @param string $text the message
	 * @param int    $post->ID the ticket ID
	 * @param array  $post_data the post data
	 *
	 * @return string $text the message
	 */
	$text = apply_filters( 'sts-status-update-mail-body', $text, $post->ID, $post_data );

	$headers     = array();
	$attachments = array();
	sts_mail( $ticket_owner->data->user_email, $subject, $text, $headers, $attachments );
}
add_action( 'sts-ticket-status-updated', 'sts_send_status_update_mail', 10, 2 );



/**
 * Notify an newly assigned agent
 *
 * @since 1.0.7
 * @wp-hook sts-ticket-agent-updated
 *
 * @param int   $post_id The post ID.
 * @param array $post_data The post data.
 **/
function sts_notify_new_agent( $post_id, $post_data ) {

	$agent = get_user_by( 'id', $post_data['ticket-agent'] );

	/**
	 * Filters the mail subject
	 *
	 * @since 1.0.7
	 *
	 * @param string $subject The subject.
	 * @param int    $post_id The ticket ID.
	 * @param array  $post_data The post data.
	 *
	 * @return string $subject The subject.
	 */
	$subject = apply_filters(
		'sts-notify-new-agent-subject',
		// translators: The placeholder is the ID of the ticket.
		sprintf( __( 'You have been assigned to the ticket #%s', 'support-ticket' ), $post_id ),
		$post_id,
		$post_data
	);

	$text = __( 'Hello', 'support-ticket' ) . PHP_EOL .
			// translators: %1$s is post ID, %2$s is the title of the ticket.
			sprintf( __( 'you have been assigned to the ticket #%1$s "%2$s"', 'support-ticket' ), $post_id, get_the_title( $post_id ) ) .
			PHP_EOL . PHP_EOL;
	$text .= sprintf( __( 'You can read the ticket here:', 'support-ticket' ) ) . ' ';

	$view_ticket = admin_url( 'admin.php?page=sts&action=single&ID=' . $post_id );

	/**
	 * Filters the URL where the ticket can be read
	 *
	 * @since 1.0.7
	 *
	 * @param string $view_ticket The URL.
	 * @param int    $post_id The post ID.
	 *
	 * @return string $view_ticket The URL.
	 */
	$view_ticket = apply_filters( 'sts-view-ticket-url', $view_ticket, $post_id );

	$text .= '<a href="' . esc_url( $view_ticket ) . '">' . esc_html( $view_ticket ) . '</a>';

	/**
	 * Filters the text message
	 *
	 * @since 1.0.7
	 *
	 * @param string $text The message.
	 * @param int    $post_id The ticket ID.
	 * @param array  $post_data The post data.
	 *
	 * @return string $text The message.
	 */
	$text = apply_filters( 'sts-notify-new-agent-body', $text, $post_id, $post_data );

	$headers     = array();
	$attachments = array();
	sts_mail( $agent->data->user_email, $subject, $text, $headers, $attachments );
}
add_action( 'sts-ticket-agent-updated', 'sts_notify_new_agent', 10, 2 );

/**
 * Remove ticket session on logout.
 *
 * @since master
 */
function sts_unset_session() {

	if ( isset( $_SESSION['ticket'] ) ) {
		unset( $_SESSION['ticket'] );
	}

	if ( isset( $_SESSION['tickets'] ) ) {
		unset( $_SESSION['tickets'] );
	}
}
add_action( 'wp_logout', 'sts_unset_session' );
