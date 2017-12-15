<?php
$fields = sts_get_create_ticket_form_fields( 'admin' );
if ( isset( $_GET['ticket-created'] ) ) : // Input var okay.
	$ticket_id = ( isset( $_GET['ticket-id'] ) ) ? sanitize_text_field( wp_unslash( $_GET['ticket-id'] ) ) : 0; // Input var okay.
	?>
	<script>
		location.href="?page=sts&action=single&ticket-new=1&ID=<?php echo (int) $ticket_id; ?>";
	</script>
	<?php
	exit;
endif;
?>
<div id="sts-wrap" class="wrap">
	<h2>
		<img src="<?php echo esc_url( STS_URL ); ?>assets/img/logo-small.svg" height="25px" />
		<?php esc_html_e( 'Create a new ticket', 'support-ticket' ); ?>
	</h2>
	<form method="post" class="ticket create" enctype="multipart/form-data">
		<?php if ( isset( $_SESSION['tickets']['error'] ) && is_wp_error( $_SESSION['tickets']['error'] ) ) : ?>
		<div class="error"><p><?php echo $_SESSION['tickets']['error']->get_error_message(); ?></p></div>
		<?php endif; ?>

		<input type="hidden" name="t-action" value="ticket-create" />
		<?php wp_nonce_field( 'ticket-create', 't-nonce' ); ?>

		<?php
		foreach ( $fields as $field ) {
			sts_render_form_field( $field, 'admin' );
		}
		?>
		<section>
			<label>&ensp;</label>
			<button class="button button-primary button-large"><?php esc_html_e( 'Send', 'support-ticket' ); ?></button>
		</section>
	</form>
</div>
