<?php
require_once dirname( __FILE__ ) . '/classes/class-sts-settings.php';
require_once dirname( __FILE__ ) . '/inc/functions-settings.php';
add_thickbox();
do_action( 'add_meta_boxes' );
$sts_settings = new Sts_Settings();

//ToDo: Validate nonce here too.
if ( isset( $_POST['t-action'] ) && 'settings' === sanitize_text_field( wp_unslash( $_POST['t-action'] ) ) ) { // Input var okay.
	$sts_settings->update();
}

wp_enqueue_script( 'common' );
wp_enqueue_script( 'wp-lists' );
wp_enqueue_script( 'postbox' );

?><div id="sts-wrap" class="wrap">
	<h2><?php esc_html_e( 'Settings', 'support-ticket' ); ?></h2>
	<?php
	$sts_settings->render_error();
	if ( isset( $_GET['updated'] ) ) : // Input var okay.
	?>
	<div id="message" class="updated notice is-dismissible"><p><?php esc_html_e( 'Settings updated.', 'support-ticket' ); ?></p></div>
	<?php
	endif;
	?>

	<div id="poststuff">
		<div id="sts-tabs">
			<ul>
				<?php $sts_settings->render_tabs(); ?>
			</ul>
				<?php $sts_settings->render_content(); ?>
		</div>
	</div>
</div>

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
