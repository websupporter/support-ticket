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
