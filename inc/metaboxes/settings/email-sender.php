<?php


/**
 * Update the Email sender settings
 *
 * Explanations on this filter can be found in admin/inc/functions-settings.php
 *
 *
 * @since 1.0.5
 *
 * @param (mixed)       $return
 * @return (mixed)      $return
 **/
function sts_settings_update_email_sender( $return ) {
	$settings = get_option( 'sts-core-settings' );
	if ( isset( $_POST['email']['from_name'] ) ) {
		$settings['email']['from_name'] = sanitize_text_field( $_POST['email']['from_name'] );
	}
	if ( isset( $_POST['email']['from_email'] ) ) {
		$settings['email']['from_email'] = sanitize_text_field( $_POST['email']['from_email'] );
	}

	update_option( 'sts-core-settings', $settings );
	return $return;
}
add_filter( 'sts-settings-update-email', 'sts_settings_update_email_sender' );


/**
 * Renders the email sender metabox in the settings
 *
 * @since 1.0.5
 *
 * @param (stdClass)    $args   empty
 * @return (void)
 **/
function sts_settings_metabox_email_sender_render( $args ) {
	$settings = get_option( 'sts-core-settings' );

	$from_name  = get_bloginfo( 'name' );
	$from_email = get_bloginfo( 'admin_email' );
	if ( isset( $settings['email']['from_name'] ) ) {
		$from_name = $settings['email']['from_name'];
	}
	if ( isset( $settings['email']['from_email'] ) ) {
		$from_email = $settings['email']['from_email'];
	}

	?>
	<section>
		<label for="standard-email-from-name"><?php _e( 'Sender Name', 'support-ticket' ); ?></label>
		<input type="text" id="standard-email-from-name" name="email[from_name]" value="<?php echo sanitize_text_field( $from_name ); ?>" />
	</section>
	<section>
		<label for="standard-email-from-email"><?php _e( 'Sender Email', 'support-ticket' ); ?></label>
		<input type="email" id="standard-email-from-email" name="email[from_email]" value="<?php echo sanitize_text_field( $from_email ); ?>" />
	</section>
	<?php
}
