<?php

$user = wp_get_current_user();
$ticket_id = ( isset( $_GET['ticket-id'] ) ) ? (int) sanitize_text_field( wp_unslash( $_GET['ticket-id'] ) ) : 0; // Input var okay.
$view_ticket = admin_url( 'admin.php?page=sts&action=single&ID=' . $ticket_id );

/**
 * Filters the URL where the ticket can be read
 *
 * @since 1.0.0
 *
 * @param 	(string) 	$view_ticket 	the URL
 * @return 	(string) 	$view_ticket 	the URL
 */
$view_ticket = apply_filters( 'sts-view-ticket-url', $view_ticket, $ticket_id );
?>
<p><?php echo esc_html( sprintf( __( 'Hello %s', 'sts' ), $user->data->display_name ) ); ?>,</p>
<p>
	<?php esc_html_e( 'We have received your ticket and will contact you as soon as possible.', 'sts' ); ?>
	<?php echo esc_html( sprintf( __( 'Your ticket is filed as #%d.', 'sts' ), $ticket_id ) ); ?>
	<a href="<?php echo esc_url( $view_ticket ); ?>">
		<?php esc_html_e( 'Click here to see your ticket.', 'sts' ); ?>
	</a>
</p>
<p><?php esc_html_e( 'Thank you.', 'sts' ); ?></p>
