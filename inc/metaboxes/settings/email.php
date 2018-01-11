<?php
/**
 * Renders the email wrapper metabox
 *
 * @since 1.0.0
 *
 * @param (stdClass)    $args   empty
 * @return (void)
 **/
function sts_settings_metabox_email_wrapper_render( $args ) {
	$settings = get_option( 'sts-core-settings' );

	if ( isset( $settings['email']['wrapper'] ) ) {
		$wrapper = $settings['email']['wrapper'];
	} else {
		$wrapper = preg_replace( '^#logo#^', STS_URL . 'assets/img/logo.png', file_get_contents( STS_ROOT . 'assets/email-wrapper.html' ) );
	}

	?>
	<section>
		<label for="standard-email-wrapper"><?php _e( 'Email Wrapper', 'support-ticket' ); ?></label>
		<textarea id="standard-email-wrapper" name="email[wrapper]"><?php echo $wrapper; ?></textarea>
		<p>
			<small>
				Use some HTML to style your emails. The template tag <code>#content#</code>
				will be replaced by the actual email.
			</small>
		</p>
	</section>
	<?php
}

/**
 * Update the Settings Email section
 *
 * Explanations on this filter can be found in admin/inc/functions-settings.php
 *
 *
 * @since 1.0.0
 *
 * @param (mixed)       $return
 * @return (mixed)      $return
 **/
add_filter( 'sts-settings-update-email', 'sts_settings_email_update' );
function sts_settings_email_update( $return ) {
	$settings     = get_option( 'sts-core-settings' );
	$notification = 0;
	if ( isset( $_POST['email']['notifiy-ticketowner'] ) && 1 === (int) wp_unslash( $_POST['email']['notifiy-ticketowner'] ) ) {
		$notification = 1;
	}
	$settings['email']['notifiy-ticketowner'] = $notification;

	$notification = 0;
	if ( isset( $_POST['email']['notifiy-agent'] ) && 1 === (int) wp_unslash( $_POST['email']['notifiy-agent'] ) ) {
		$notification = 1;
	}
	$settings['email']['notifiy-agent'] = $notification;

	$notification = 0;
	if ( isset( $_POST['email']['notifiy-on-status-update'] ) && 1 === (int) wp_unslash( $_POST['email']['notifiy-on-status-update'] ) ) {
		$notification = 1;
	}
	$settings['email']['notifiy-on-status-update'] = $notification;

	$wrapper = '';
	if ( isset( $_POST['email']['wrapper'] ) ) {
		$wrapper                      = stripslashes( $_POST['email']['wrapper'] );
		$settings['email']['wrapper'] = $wrapper;
	}

	update_option( 'sts-core-settings', $settings );
	return $return;
}

/**
 * Render the Email notification box for settings
 *
 * @since 1.0.0
 *
 * @param (stdClass)    $args   is empty
 * @return (void)
 **/
function sts_settings_metabox_email_notification_render( $args ) {
	$settings      = get_option( 'sts-core-settings' );
	$owner_checked = '';
	if ( isset( $settings['email']['notifiy-ticketowner'] ) && 1 === (int) $settings['email']['notifiy-ticketowner'] ) {
		$owner_checked = 'checked="checked"';
	}
	$agent_checked = '';
	if ( isset( $settings['email']['notifiy-agent'] ) && 1 === (int) $settings['email']['notifiy-agent'] ) {
		$agent_checked = 'checked="checked"';
	}
	$on_status_update_checked = '';
	if ( isset( $settings['email']['notifiy-on-status-update'] ) && 1 === (int) $settings['email']['notifiy-on-status-update'] ) {
		$on_status_update_checked = 'checked="checked"';
	}
	?>
	<section>
		<label for="notify-ticketowner-on-answer"><?php _e( 'Notify ticket owner on answer', 'support-ticket' ); ?></label>
		<input id="notify-ticketowner-on-answer" type="checkbox" name="email[notifiy-ticketowner]" value="1" <?php echo $owner_checked; ?> />
	</section>
	<section>
		<label for="notify-agent-on-answer"><?php _e( 'Notify agent owner on answer', 'support-ticket' ); ?></label>
		<input id="notify-agent-on-answer" type="checkbox" name="email[notifiy-agent]" value="1" <?php echo $agent_checked; ?> />
	</section>
	<section>
		<label for="notifiy-on-status-update"><?php _e( 'Notify ticket owner on status update', 'support-ticket' ); ?></label>
		<input id="notifiy-on-status-update" type="checkbox" name="email[notifiy-on-status-update]" value="1" <?php echo $on_status_update_checked; ?> />
	</section>
	<?php
}
