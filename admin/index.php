<?php
require_once __DIR__ . '/classes/class-sts-tickets-table.php';

$action = ( isset( $_GET['action'] ) ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // Input var okay.
?>
<div id="sts-wrap" class="wrap <?php echo esc_attr( 'ticket-' . $action ); ?>">
	<?php
	$sts_actions = array( 'single' );
	if ( in_array( $action, $sts_actions, true ) ) :
		if ( 'single' === $action ) :
			require_once 'ticket-single.php';
		endif;
	else :
		require_once dirname( __FILE__ ) . '/inc/ticket-table.php';
		?>

		<h2>
			<img src="<?php echo esc_url( STS_URL ); ?>assets/img/logo-small.svg" height="25px" />
			<?php esc_html_e( 'Tickets', 'support-ticket' ); ?>
		</h2>
		<?php if ( isset( $_GET['updated'] ) ) : // Input var okay. ?>
			<div id="message" class="updated notice is-dismissible"><p><?php esc_html_e( 'Updated.', 'support-ticket' ); ?></p></div>
		<?php endif; ?>
		<?php

		add_filter( 'list_table_primary_column', 'sts_standard_table_column', 10, 2 );
		$table = new STS_Tickets_Table();
		$table->prepare_items();
		?>
		<?php $table->display(); ?>
		</form>
	<?php endif; ?>
</div>
