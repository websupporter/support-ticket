<?php
if ( ! isset( $_GET['ID'] ) || ! is_numeric( wp_unslash( $_GET['ID'] ) ) ) { // Input var okay.
	wp_die( esc_html__( 'Something went wrong :/', 'support-ticket' ) );
}

$ticket_id = (int) wp_unslash( $_GET['ID'] ); // Input var okay.

if ( ! sts_current_user_can_read_ticket( $ticket_id ) ) {
	wp_die( esc_html__( 'Something went wrong :/', 'support-ticket' ) );
}



do_action( 'add_meta_boxes' );
wp_enqueue_script( 'common' );
wp_enqueue_script( 'wp-lists' );
wp_enqueue_script( 'postbox' );

if (
	isset( $_POST['t-action'] ) // Input var okay.
	&& 'ticket-admin-update' === sanitize_text_field( wp_unslash( $_POST['t-action'] ) ) ) { // Input var okay.
	if (
	isset( $_POST['t'] ) // Input var okay.
	&& isset( $_POST['t-nonce'] ) // Input var okay.
	&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['t-nonce'] ) ), 'ticket-admin-update-' . get_current_user_id() ) // Input var okay.
	) {

		/**
		 * Filter the post data before the update
		 *
		 * @since 1.0.0
		 *
		 * @param array $_POST['t'] The post data.
		 * @param int   $ticket_id The ticket ID.
		 *
		 * @return array $post_data The post data.
		 */

		/* ToDo: proper sanitization should be done on the global scope, not by single actions */
		$post_data = apply_filters( 'sts-ticket-admin-update-postdata', wp_unslash( $_POST['t'] ), $ticket_id );
		do_action( 'ticket-admin-update', $post_data, $ticket_id );
		?><script>location.href='?page=sts&action=single&ID=<?php echo (int) $ticket_id; ?>&updated=1'</script>'
		<?php
	}
	exit;
}

?>
<?php if ( isset( $_GET['updated'] ) ) : // Input var okay ?>
<div id="message" class="updated notice is-dismissible"><p><?php esc_html_e( 'Ticket updated.', 'support-ticket' ); ?></p></div>
<?php endif; ?>
<?php if ( isset( $_GET['ticket-new'] ) ) : // Input var okay ?>
<div id="message" class="updated notice is-dismissible"><p><?php esc_html_e( 'Ticket created.', 'support-ticket' ); ?></p></div>
<?php endif; ?>
<form method="post">
	<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
	<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
	<input type="hidden" name="t-action" value="ticket-admin-update" />
	<?php
	wp_nonce_field( 'ticket-admin-update-' . get_current_user_id(), 't-nonce' );

	$args = array(
		'post_type'   => 'ticket',
		'post_status' => array( 'draft' ),
		'p'           => $ticket_id,
	);

	if ( ! current_user_can( 'read_other_tickets' ) && ! current_user_can( 'read_assigned_tickets' ) ) {
		$args['author'] = get_current_user_id();
	} elseif ( ! current_user_can( 'read_other_tickets' ) ) {
		$args['meta_query'] = array(
			'key'   => 'ticket-agent',
			'value' => get_current_user_id(),
		);
	}

	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) :
	?>
	<h1>
		<img src="<?php echo esc_url( STS_URL ); ?>assets/img/logo-small.svg" height="25px" />
		<?php esc_html_e( 'Ticket not found :/', 'support-ticket' ); ?>
	</h1>
	<?php
	else :
		$query->the_post();

		//If the assigned ticket agent reads this ticket,
		//The postmeta information the ticket has been read will be set.
		if ( get_current_user_id() === (int) get_post_meta( get_the_ID(), 'ticket-agent', true ) ) {
			update_post_meta( get_the_ID(), 'ticket-read', 1 );
		}
	?>
	<h2>
		<img src="<?php echo esc_url( STS_URL ); ?>assets/img/logo-small.svg" height="25px" />
		<?php the_title(); ?>
	</h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content" style="position: relative;">

			<?php
				global $post;
				do_meta_boxes( 'ticket-boxes', 'normal', $post );
			?>
			</div>
			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( 'ticket-boxes', 'side', $post ); ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</form>
<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready( function($) {
		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles('ticket-boxes');
	});
	//]]>
</script>
