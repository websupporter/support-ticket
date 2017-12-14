<?php

/**
 * Register ticket create shortcode
 * Outputs the shortcode [ticket_create]
 *
 * @since   1.0.0
 */
add_shortcode( 'ticket_create', 'sts_ticket_create' );
function sts_ticket_create( $args ) {

	//The default settings
	$default = array(
		'type' => 'section',
	);
	$args    = wp_parse_args( $args, $default );

	if ( isset( $_SESSION['ticket']['ticket-create'] ) ) {
		$_POST['t'] = $_SESSION['ticket']['ticket-create'];
	}

	$renderer = new Sts_Renderer();
	if ( ! isset( $_GET['ticket-created'] ) ) { // Input var okay.
		$fields = sts_get_create_ticket_form_fields( 'shortcode' );

		$shortcode_file = $renderer->templates_dir() . '/shortcodes/ticket-create.php';

		/**
		* Filter the file path to the shortcode
		*
		* @since 1.0.0
		*
		* @param string $shortcode_file     filename of the shortcode file
		* @return string $shortcode_file    filename of the shortcode file
		*/
		$shortcode_file = apply_filters( 'sts-create-ticket-shortcodefile', $shortcode_file );
	} else {
		$fields         = [];
		$shortcode_file = $renderer->templates_dir() . '/shortcodes/ticket-create-done.php';

		/**
		* Filter the file path to the shortcode for a submitted ticket.
		*
		* @since 1.0.0
		*
		* @param string $shortcode_file filename of the shortcode file
		* @return string $shortcode_file filename of the shortcode file
		*/
		$shortcode_file = apply_filters( 'sts-create-ticket-done-shortcodefile', $shortcode_file );
	}

	return $renderer->render(
		$shortcode_file, [
			'args'   => $args,
			'fields' => $fields,
		]
	);
}
