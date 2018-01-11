<?php

require_once( __DIR__ . '/mail.php' );

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
* @return array     Array of users
*/
function sts_get_possible_agents() {

	$possible_roles = array();
	$roles          = get_editable_roles();
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
		$args       = array( 'role' => $role );
		$user_query = new WP_User_Query( $args );
		$agents     = array_merge( $user_query->results, $agents );
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
		$status = sts_get_status_class_arr();
	} else {
		$status = sts_get_status_arr();
	}

	if ( isset( $status[ (int) $status_index ] ) ) {
		return $status[ (int) $status_index ];
	}

	return esc_html__( 'Unknown', 'support-ticket' );
}

/**
 * Renders the Form field
 *
 * @since   1.0.0
 *
 * @param   array   $field The field array.
 * @param   string  $location The location where it will be displayed.
 * @param   boolean $echo If it will be echoed immediately.
 * @param   array   $args The arguments
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
			'label'    => __( 'Name', 'support-ticket' ),
			'id'       => 'ticket-user',
			'tag'      => 'input',
			'type'     => 'text',
			'name'     => 'user',
			'value'    => '',
			'error'    => false,
			'required' => true,
		);
		$fields[] = array(
			'label'    => __( 'Email', 'support-ticket' ),
			'id'       => 'ticket-email',
			'tag'      => 'input',
			'type'     => 'email',
			'name'     => 'email',
			'value'    => '',
			'error'    => false,
			'required' => true,
		);
	}
	$fields[] = array(
		'label'    => __( 'Subject', 'support-ticket' ),
		'id'       => 'ticket-subject',
		'tag'      => 'input',
		'type'     => 'text',
		'name'     => 'subject',
		'value'    => '',
		'error'    => false,
		'required' => false,
	);
	$fields[] = array(
		'label'    => __( 'Message', 'support-ticket' ),
		'id'       => 'ticket-message',
		'tag'      => 'textarea',
		'name'     => 'message',
		'value'    => '',
		'error'    => false,
		'required' => false,
	);

	/**
	* Filter the form fields
	*
	* @since 1.0.0
	*
	* @param (array)    the fields
	* @param (string)   location
	* @return (array)   the fields
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

	$query    = new WP_Query( $args );
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
function sts_get_status_arr() {

	/**
	 * Filter the available ticket status options
	 *
	 * @since 1.0.0
	 *
	 * @param array The status array.
	 *
	 * @return array The status array.
	 */
	return apply_filters(
		'sts-status-array', array(
			esc_html__( 'Open', 'support-ticket' ),
			esc_html__( 'Pending', 'support-ticket' ),
			esc_html__( 'Close', 'support-ticket' ),
		)
	);
}

/**
* Get The Status Array untranslated
* We need these for the table classes in the ticket overview
*
* @since 1.1.0
*
* @return array Status
*/
function sts_get_status_class_arr() {

	/**
	 * Filter the available ticket status options
	 *
	 * @since 1.0.0
	 *
	 * @param array The status array.
	 *
	 * @return array The status array.
	 */
	return apply_filters(
		'sts-status-array', array(
			'open',
			'pending',
			'close',
		)
	);
}

/**
 * Return the number of unread tickets for a user.
 *
 * @since master
 * @param $user_id
 *
 * @return int
 */
function get_unread_tickets_for_user( $user_id ) {

	global $wpdb;
	$all = $wpdb->get_results(
		$wpdb->prepare(
			'select count( a.post_id ) as alltickets
			from ' . $wpdb->postmeta . ' as a
			where ( a.meta_key = "ticket-agent" && a.meta_value = %d )',
			$user_id
		)
	);

	$res = $wpdb->get_results(
		$wpdb->prepare(
			'select  count( r.post_id )  as readtickets
			from ' . $wpdb->postmeta . ' as r, ' . $wpdb->postmeta . ' as a
			where ( a.meta_key = "ticket-agent" && a.meta_value = %d && a.post_id = r.post_id && r.meta_key = "ticket-read" && r.meta_value = 1 )',
			$user_id
		)
	);
	return (int) $all[0]->alltickets - (int) $res[0]->readtickets;
}


/**
 * Prints a select field.
 *
 * @param string     $id
 * @param string     $name
 * @param string     $label
 * @param string     $value
 * @param stdClass[] $options
 */
function sts_print_select( $id, $name, $label, $value, $options ) {
	?>
	<p>
		<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
		<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>">
			<?php foreach ( $options as $option ) : ?>
				<option <?php selected( $value, $option->value ); ?>
					value="<?php echo esc_attr( $option->value ); ?>">
					<?php echo $option->label; ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
}
