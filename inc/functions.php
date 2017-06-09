<?php

/**
 * Checks, if a section has metaboxes registered
 *
 * @since 1.0.0
 *
 * @param mixed  $screen The screen identifier.
 * @param string $context The context identifier.
 *
 * @return boolean Whether metaboxes are registered or not.
 **/
function sts_has_meta_boxes( $screen = null, $context = 'normal' ) {

	global $wp_meta_boxes;

	if ( empty( $screen ) ) {
		$screen = get_current_screen();
	} elseif ( is_string( $screen ) ) {
		$screen = convert_to_screen( $screen );
	}

	$page = $screen->id;
	if ( isset( $wp_meta_boxes[ $page ][ $context ] ) && count( $wp_meta_boxes[ $page ][ $context ] ) > 0 ) {
		return true;
	}
	return false;
}

/**
* Returns the possible ticket agents
*
* @since 1.0.0
*
* @return array 	Array of users
*/
function sts_get_possible_agents() {

	$possible_roles = array();
	$roles = get_editable_roles();
	foreach ( $roles as $role_key => $role ) {
		if (
			isset( $role['capabilities']['read_assigned_tickets'] )
			&& 1 === (int) $role['capabilities']['read_assigned_tickets']
		) {
			$possible_roles[] = $role_key;
		}
	}

	$agents = array();
	foreach ( $possible_roles as $role ) {
		$args = array( 'role' => $role );
		$user_query = new WP_User_Query( $args );
		$agents = array_merge( $user_query->results, $agents );
	}

	//Remove double users
	$double_users = array();
	foreach ( $agents as $key => $val ) {
		if ( in_array( $val->ID, $double_users, true ) ) {
			unset( $agents[ $key ] );
		}

		$double_users[] = $val->ID;
	}

	/**
	 * Filter the possible agents array
	 *
	 * @since 1.0.0
	 *
	 * @param array $agents The possible agents.
	 *
	 * @return array $agents The possible agents.
	 */
	return apply_filters( 'sts-possible-agents', $agents );
}

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
		'From: "' . get_bloginfo( 'name' ) . '" <' . get_bloginfo( 'admin_email' ) . '>'
	);
	$settings = get_option( 'sts-core-settings' );
	$from_string = 'From:';
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
		$body = preg_replace( '^#content#^', $body, $wrapper );
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
 * Translate Status Index To Status
 *
 * @since 1.0.0
 *
 * @param int    $status_index The index of a status, equals the array index.
 * @param string $type 'class' returns the classnames.
 *
 * @return string Status
 */
function sts_translate_status( $status_index, $type = 'normal' ) {

	if ( 'class' === $type ) {
		$status = sts_get_statusClassArr();
	} else {
		$status = sts_get_statusArr();
	}

	if ( isset( $status[ (int) $status_index ] ) ) {
		return $status[ (int) $status_index ];
	}

	return esc_html__( 'Unknown', 'sts' );
}

/**
 * Renders the Form field
 *
 * @since 	1.0.0
 *
 * @param	array   $field The field array.
 * @param 	string  $location The location where it will be displayed.
 * @param 	boolean $echo If it will be echoed immediately.
 * @param	array   $args The arguments
 *                        type => 'section'
 *                                renders <section>
 *                             => 'table'
 *                                renders <tr><td>
 *
 * @return string $element The HTML element.
 */
function sts_render_form_field( $field, $location, $echo = true, $args = array( 'type' => 'section' ) ) {

	if ( 'html' !== $field['tag'] ) {
		if ( 'section' === $args['type'] ) {
			$element = '<section class="';
		} elseif ( 'table' === $args['type'] ) {
			$element = '<tr class="';
		}

		if ( $field['required'] ) {
			$element .= 'required ';
		}
		if ( $field['error'] ) {
			$element .= 'error';
		}

		$element .= '">';

		if ( 'table' === $args['type'] ) {
			$element .= '<td>';
		}

		$element .= '<label for="' . esc_attr( $field['id'] ) . '">';
		$element .= esc_html( $field['label'] );
		$element .= '</label>';

		if ( 'table' === $args['type'] ) {
			$element .= '</td><td>';
		}

		if ( 'input' === $field['tag'] ) {
			$element .= '<input ';
			if ( $field['required'] ) {
				$element .= 'required';
			}
			$element .= ' id="' . esc_attr( $field['id'] ) . '" type="' . esc_attr( $field['type'] ) . '" name="t[' . esc_attr( $field['name'] ) . ']" value="' . esc_attr( $field['value'] ) . '" />';
		} elseif ( 'textarea' === $field['tag'] ) {
			$element .= '<textarea ';
			if ( $field['required'] ) {
				$element .= 'required';
			}
			$element .= ' id="' . esc_attr( $field['id'] ) . '" name="t[' . esc_attr( $field['name'] ) . ']">' . wp_kses_post( $field['value'] ) . '</textarea>';
		} elseif ( 'select' === $field['tag'] ) {
			$element .= '<select ';
			if ( $field['required'] ) {
				$element .= 'required';
			}
			$element .= ' id="' . esc_attr( $field['id'] ) . '" name="t[' . esc_attr( $field['name'] ) . ']">';
			foreach ( $field['choices'] as $value => $label ) {
				$selected = '';
				if ( isset( $field['value'] ) && $field['value'] === $value ) {
					$selected = ' selected="selected" ';
				}
				$element .= '<option ' . $selected . ' value="' . esc_attr( $value ) . '">' . wp_kses_post( $label ) . '</option>';
			}
			$element .= '</select>';
		}

		if ( 'section' === $args['type'] ) {
			$element .= '</section>';
		} elseif ( 'table' === $args['type'] ) {
			$element .= '</td></tr>';
		}
	} else {
		$element = $field['html'];
	}

	/**
	 * Filter the form field output
	 *
	 * @since 1.0.0
	 *
	 * @param string  $element The form element.
	 * @param string  $location Location.
	 * @param array   $field The field array.
	 * @param boolean $echo Whether to output immediatly or not.
	 *
	 * @return string $element The form element.
	 */
	$element = apply_filters( 'sts-render-form-field', $element, $location, $field, $echo );
	if ( $echo ) {
		echo $element;
	} else {
		return $element;
	}
}


/**
 * Get the create ticket form fields
 *
 * @since 1.0.0
 *
 * @param string $location The location where the form is displayed.
 *
 * @return array $fields The fields
 */
function sts_get_create_ticket_form_fields( $location ) {

	//Create form fields
	$fields = array();
	if ( ! is_user_logged_in() || 'edit' === $location ) {
		$fields[] = array(
			'label'		=> __( 'Name', 'sts' ),
			'id'		=> 'ticket-user',
			'tag'		=> 'input',
			'type'		=> 'text',
			'name'		=> 'user',
			'value'		=> '',
			'error'		=> false,
			'required'	=> true,
		);
		$fields[] = array(
			'label'		=> __( 'Email', 'sts' ),
			'id'		=> 'ticket-email',
			'tag'		=> 'input',
			'type'		=> 'email',
			'name'		=> 'email',
			'value'		=> '',
			'error'		=> false,
			'required'	=> true,
		);
	}
	$fields[] = array(
		'label'		=> __( 'Subject', 'sts' ),
		'id'		=> 'ticket-subject',
		'tag'		=> 'input',
		'type'		=> 'text',
		'name'		=> 'subject',
		'value'		=> '',
		'error'		=> false,
		'required'	=> false,
	);
	$fields[] = array(
		'label'		=> __( 'Message', 'sts' ),
		'id'		=> 'ticket-message',
		'tag'		=> 'textarea',
		'name'		=> 'message',
		'value'		=> '',
		'error'		=> false,
		'required'	=> false,
	);

	/**
	* Filter the form fields
	*
	* @since 1.0.0
	*
	* @param (array) 	the fields
	* @param (string) 	location
	* @return (array) 	the fields
	*/
	$fields = apply_filters( 'sts-create-ticket-formfields', $fields, $location );

	foreach ( $fields as $key => $field ) {
		if ( isset( $_POST['t'][ $field['name'] ] ) ) {
			$fields[ $key ]['value'] = sanitize_text_field( wp_unslash( $_POST['t'][ $field['name'] ] ) );
		}
		if (
			isset( $_SESSION['tickets']['error'] )
			&& $_SESSION['tickets']['error']->get_error_code() === $field['id']
		) {
			$fields[ $key ]['error'] = true;
		}
	}

	return $fields;
}

/**
* Can a user read a specific ticket
*
* @since 1.0.0
*
* @param int $post_id ID of ticket.
 *
* @return boolean Status
*/
function sts_current_user_can_read_ticket( $post_id = null ) {

	if ( null === $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! $post_id ) {
		return false;
	}

	$args = array(
		'post_type'   => 'ticket',
		'post_status' => 'draft',
		'p'           => $post_id,
	);

	if (
		! current_user_can( 'read_other_tickets' )
		&& ! current_user_can( 'read_assigned_tickets' )
	) {
		$args['author'] = get_current_user_id();
	} elseif ( ! current_user_can( 'read_other_tickets' ) ) {
		$args['meta_query'] = array(
			array(
				'key'   => 'ticket-agent',
				'value' => get_current_user_id(),
			),
		);
	}

	$query = new WP_Query( $args );
	$can_read = $query->have_posts();
	wp_reset_query();

	return $can_read;
}

/**
* Get The Status Array
*
* @since 1.0.0
*
* @return array Status
*/
function sts_get_statusArr() {

	/**
	 * Filter the available ticket status options
	 *
	 * @since 1.0.0
	 *
	 * @param array The status array.
	 *
	 * @return array The status array.
	 */
	return apply_filters( 'sts-status-array', array(
		esc_html__( 'Open', 'sts' ),
		esc_html__( 'Pending', 'sts' ),
		esc_html__( 'Close', 'sts' ),
	) );
}

/**
* Get The Status Array untranslated
* We need these for the table classes in the ticket overview
*
* @since 1.0.0
*
* @return array Status
*/
function sts_get_statusClassArr() {

	/**
	 * Filter the available ticket status options
	 *
	 * @since 1.0.0
	 *
	 * @param array The status array.
	 *
	 * @return array The status array.
	 */
	return apply_filters( 'sts-status-array', array(
		'open',
		'pending',
		'close',
	) );
}
