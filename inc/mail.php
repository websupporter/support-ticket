<?php

/**
 * Creates the Ticket Email and sends it
 *
 * @since 1.0.0
 *
 * @param string $to Receiver Email address.
 * @param string $subject Email subject.
 * @param string $body Email body.
 * @param array  $headers Email headers.
 * @param array  $attachments Email attachments.
 *
 * @return bool
 */
function sts_mail( $to, $subject, $body, $headers = array(), $attachments = array() ) {

	//Adding the From Header
	$headers_default = array(
		'From: "' . get_bloginfo( 'name' ) . '" <' . get_bloginfo( 'admin_email' ) . '>',
	);
	$settings        = get_option( 'sts-core-settings' );
	$from_string     = 'From:';
	if ( isset( $settings['email']['from_name'] ) ) {
		$from_string .= ' ' . $settings['email']['from_name'];
	}
	if ( isset( $settings['email']['from_email'] ) ) {
		$from_string .= ' <' . $settings['email']['from_email'] . '>';
	}
	if ( 'From:' !== $from_string ) {
		$headers_default = array( $from_string );
	}
	$headers = wp_parse_args( $headers, $headers_default );

	$body = apply_filters( 'the_content', $body );
	if ( isset( $settings['email']['wrapper'] ) ) {

		/**
		 * Filter the email wrapper before the content is inserted
		 *
		 * @since 1.0.0
		 *
		 * @param array $wrapper The current HTML template.
		 *
		 * @return array $wrapper The HTML template to apply.
		 */
		$wrapper = apply_filters( 'sts-email-wrapper', $settings['email']['wrapper'] );
		$body    = preg_replace( '^#content#^', $body, $wrapper );
	}

	/**
	 * This filter enables you to hook into the mail sending process and
	 * create your own method. You get all the information necessary.
	 * If you return something else than null the mail will not be send
	 * by the usual process.
	 *
	 * @since 1.0.0
	 *
	 * @param null   $continue With the value null the mail will be send afterwards.
	 * @param string $to Receiver Email.
	 * @param string $subject Mail subject.
	 * @param string $body HTML body.
	 * @param array  $headers Email Headers.
	 * @param array  $attachments Email Attachments.
	 *
	 * @return mixed $continue With the value null the mail will be send afterwards.
	 */
	$continue = apply_filters( 'sts-before-send-email', null, $to, $subject, $body, $headers, $attachments );
	if ( null !== $continue ) {
		return (bool) $continue;
	}
	add_filter( 'wp_mail_content_type', 'sts_set_html_content_type' );
	$success = wp_mail( $to, $subject, $body, $headers, $attachments );
	remove_filter( 'wp_mail_content_type', 'sts_set_html_content_type' );
	return $success;
}

/**
 * Set the mail content type to html for ticket emails.
 *
 * @param string $type The current type. (unused)
 *
 * @return string 'text/html'
 */
function sts_set_html_content_type( $type ) {

	return 'text/html';
}

/**
 * Notifiy the ticket owner in case of an answer
 *
 * @since 1.0.0
 *
 * @param (int)     $ticket_id  the ticket ID
 * @param (int)     $answer_id  the answer ID
 * @param (array)   $post_data  the post data
 * @return (void)
 **/
add_action( 'sts-after-ticket-answer-save', 'sts_send_notification_email_to_ticket_owner', 10, 3 );
function sts_send_notification_email_to_ticket_owner( $ticket_id, $answer_id, $post_data ) {
	global $post;

	//Check if we want to notify the ticket owner
	$settings = get_option( 'sts-core-settings' );
	if ( ! isset( $settings['email']['notifiy-ticketowner'] ) || 1 != $settings['email']['notifiy-ticketowner'] ) {
		return;
	}

	//Get the ticket
	$args = array(
		'post_type'   => 'ticket',
		'post_status' => array( 'draft' ),
		'p'           => $ticket_id,
	);

	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		return;
	}

	$query->the_post();
	$ticket = $post;

	//Get the answer
	$args = array(
		'post_type'   => 'ticket',
		'post_status' => array( 'draft' ),
		'p'           => $answer_id,
	);

	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		return;
	}

	$query->the_post();
	$answer = $post;
	if ( $answer->post_author == $ticket->post_author ) {
		return;
	}

	$ticket_owner = get_user_by( 'id', $ticket->post_author );
	$subject      = $answer->post_title;
	$text         = $answer->post_content . PHP_EOL . PHP_EOL;
	$text        .= sprintf( __( 'Please click the link to reply:', 'support-ticket' ) ) . ' ';
	$view_ticket  = admin_url( 'admin.php?page=sts&action=single&ID=' . $ticket->ID . '#answer-' . $answer->ID );

	/**
	 * Filters the URL where the ticket can be read
	 *
	 * @since 1.0.0
	 *
	 * @param    (string)    $view_ticket    the URL
	 * @return   (string)    $view_ticket    the URL
	 */
	$view_ticket = apply_filters( 'sts-view-ticket-url', $view_ticket, $ticket->ID );

	$text .= '<a href="' . $view_ticket . '">' . $view_ticket . '</a>';

	$headers     = array();
	$attachments = array();

	if ( ! sts_mail( $ticket_owner->data->user_email, $subject, $text, $headers, $attachments ) ) {
		die( 'Nicht verschickt :/' );
	}
	wp_reset_query();
}

/**
 * Notifiy the agent in case the ticket owner writes an answer
 *
 * @since 1.0.0
 *
 * @param (int)     $ticket_id  the ticket ID
 * @param (int)     $answer_id  the answer ID
 * @param (array)   $post_data  the post data
 * @return (void)
 **/
add_action( 'sts-after-ticket-answer-save', 'sts_send_notification_email_to_agent', 10, 3 );
function sts_send_notification_email_to_agent( $ticket_id, $answer_id, $post_data ) {
	global $post;

	//Check if we want to notify the ticket owner
	$settings = get_option( 'sts-core-settings' );
	if ( ! isset( $settings['email']['notifiy-agent'] ) || 1 != $settings['email']['notifiy-agent'] ) {
		return;
	}

	//Get the answer
	$args = array(
		'post_type'   => 'ticket',
		'post_status' => array( 'draft' ),
		'p'           => $answer_id,
	);

	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		return;
	}

	$query->the_post();
	$answer = $post;

	$agent = get_user_by( 'id', get_post_meta( $ticket_id, 'ticket-agent', true ) );

	if ( (int) get_current_user_id() === (int) $answer->post_author ) {
		return;
	}

	$subject     = $answer->post_title;
	$text        = $answer->post_content . PHP_EOL . PHP_EOL;
	$text       .= sprintf( __( 'Please click the link to reply:', 'support-ticket' ) ) . ' ';
	$view_ticket = admin_url( 'admin.php?page=sts&action=single&ID=' . $ticket_id . '#answer-' . $answer->ID );
	/**
	 * Filters the URL where the ticket can be read
	 *
	 * @since 1.0.0
	 *
	 * @param    (string)    $view_ticket    the URL
	 * @return   (string)    $view_ticket    the URL
	 */
	$view_ticket = apply_filters( 'sts-view-ticket-url', $view_ticket, $ticket_id );

	$text .= '<a href="' . $view_ticket . '">' . $view_ticket . '</a>';

	$headers     = array();
	$attachments = array();

	if ( ! sts_mail( $agent->data->user_email, $subject, $text, $headers, $attachments ) ) {
		die( 'Nicht verschickt :/' );
	}
	wp_reset_query();
}
