<form method="post" class="ticket create" enctype="multipart/form-data">


	<?php if ( isset( $_SESSION['tickets']['error'] ) && is_wp_error( $_SESSION['tickets']['error'] ) ) : ?>
	<div class="error">
		<?php echo esc_html( $_SESSION['tickets']['error']->get_error_message() ); ?>
	</div>
	<?php endif; ?>


	<input type="hidden" name="t-action" value="ticket-create" />
	<?php wp_nonce_field( 'ticket-create', 't-nonce' ); ?>

	<?php
	if ( is_user_logged_in() ) :
		$user = wp_get_current_user();
	?>
	<p>
		<?php
		// translators: %s is the name of the person, we greet.
				echo esc_html( sprintf( __( 'Hello %s', 'support-ticket' ), $user->data->display_name ) );
		?>
	</p>

	<?php
	elseif (
		isset( $_SESSION['ticket']['action'] )
		&& 'ask-login' === $_SESSION['ticket']['action']
	) :
	?>

	<p>
	<?php
		echo esc_html(
			sprintf(
				// translators: %s is the email address.
				__( 'It seems, you have already an account registered with your mail address %s', 'support-ticket' ),
				$_SESSION['ticket']['ticket-create']['email']
			)
		);
		?>
	</p>
	<p><a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>">
		<?php esc_html_e( 'Please log in before you proceed.', 'support-ticket' ); ?>
		</a>
	</p>

	<?php
	endif;

	if (
		is_user_logged_in()
		|| (
			! isset( $_SESSION['ticket']['action'] ) ||
			'ask-login' !== $_SESSION['ticket']['action']
			)
		) :
		?>
		<?php if ( 'table' === $args['type'] ) : ?>
		<table>
			<tbody>
	<?php endif; ?>
	<?php
	foreach ( $fields as $field ) :
			sts_render_form_field( $field, 'shortcode', true, $args );
	?>
	<?php endforeach; ?>
	<?php if ( 'table' === $args['type'] ) : ?>
			</tbody>
		</table>
	<?php endif; ?>
	<p><button><?php esc_html_e( 'Send', 'support-ticket' ); ?></button></p>
	<?php endif; ?>
</form>
