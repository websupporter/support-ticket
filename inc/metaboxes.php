<?php
require_once __DIR__ . '/metaboxes/settings/email-sender.php';
require_once __DIR__ . '/metaboxes/settings/user-agent.php';
require_once __DIR__ . '/metaboxes/settings/user-roles.php';
require_once __DIR__ . '/metaboxes/settings/email.php';
require_once __DIR__ . '/metaboxes/settings/metafields.php';

require_once __DIR__ . '/metaboxes/ticket/metafields.php';
require_once __DIR__ . '/metaboxes/ticket/answer.php';
require_once __DIR__ . '/metaboxes/ticket/private-note.php';
require_once __DIR__ . '/metaboxes/ticket/message.php';
require_once __DIR__ . '/metaboxes/ticket/status.php';

add_action(
	'add_meta_boxes', function() {

		$repository = new Sts_Metabox_Repository();
		$repository->wp_register();
	}
);

/**
 * Register the metaboxes to the Sts Metabox Repository.
 */
function sts_add_meta_boxes() {

	$repository = new Sts_Metabox_Repository();

	$id = ( isset( $_GET['ID'] ) ) ? (int) wp_unslash( $_GET['ID'] ) : 0;
	$repository->add(
		'ticket-message',
		// translators: %d is the ID of the ticket.
		sprintf( __( 'Ticket #%d', 'support-ticket' ), $id ),
		'sts_metabox_message_render',
		'ticket-boxes',
		'normal'
	);

	$metafields = get_option( 'sts-metafields', array() );
	if ( count( $metafields ) > 0 ) {
		$repository->add(
			'ticket-metafields',
			__( 'Metafields', 'support-ticket' ),
			'sts_metabox_metafields_render',
			'ticket-boxes',
			'normal'
		);
	}

	$repository->add(
		'ticket-answer',
		__( 'Answer', 'support-ticket' ),
		'sts_metabox_answer_render',
		'ticket-boxes',
		'normal'
	);

	$repository->add(
		'ticket-status',
		__( 'Status', 'support-ticket' ),
		'sts_metabox_status_render',
		'ticket-boxes',
		'side'
	);

	if ( current_user_can( 'update_tickets' ) ) {
		$repository->add(
			'ticket-privatenote',
			__( 'Private Note', 'support-ticket' ),
			'sts_metabox_privatenote_render',
			'ticket-boxes',
			'side'
		);
	}

	//Settings Metaboxes
	$repository->add(
		'ticket-setting-email-notification',
		__( 'Email notification', 'support-ticket' ),
		'sts_settings_metabox_email_notification_render',
		'ticket-settings-email',
		'normal'
	);

	$repository->add(
		'ticket-setting-user-agent',
		__( 'Ticket agents', 'support-ticket' ),
		'sts_settings_metabox_user_agent_render',
		'ticket-settings-user',
		'normal'
	);

	$repository->add(
		'ticket-setting-user-roles',
		__( 'Roles & Capabilities', 'support-ticket' ),
		'sts_settings_metabox_user_roles_render',
		'ticket-settings-user',
		'normal'
	);

	$repository->add(
		'ticket-setting-email-sender',
		__( 'Email Sender', 'support-ticket' ),
		'sts_settings_metabox_email_sender_render',
		'ticket-settings-email',
		'normal'
	);

	$repository->add(
		'ticket-setting-email-wrapper',
		__( 'Email wrapper', 'support-ticket' ),
		'sts_settings_metabox_email_wrapper_render',
		'ticket-settings-email',
		'normal'
	);

	$repository->add(
		'ticket-setting-ticket-wrapper',
		__( 'Additional ticket fields', 'support-ticket' ),
		'sts_settings_metabox_metafields_render',
		'ticket-settings-ticket',
		'normal'
	);
}
add_action( 'plugins_loaded', 'sts_add_meta_boxes' );

