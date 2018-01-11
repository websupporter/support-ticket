<?php

/**
 * Renders the message metabox
 *
 * @since 1.0.0
 *
 * @param (object)  $post
 * @return (void)
 **/
function sts_metabox_message_render( $post ) {
	$ticketauthor_id = $post->post_author;
	?>
	<div class="ticket-content">
		<p class="date">
			<?php
			// translators: %s is the name of the author.
			printf( __( 'by %s', 'support-ticket' ), get_the_author() );
			?>
			,
			<?php the_date(); ?>, <?php the_time(); ?>
		</p>
		<?php

		$pattern = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`\!()\[\]{};:\'".,<>?«»“”‘’]))';
		echo nl2br( preg_replace( "!$pattern!i", "<a href=\"\\0\" rel=\"nofollow\" target=\"_blank\">\\0</a>", get_the_content() ) );
		?>
	</div>
	<ul class="ticket-history">
		<?php
		$args = array(
			'post_type'      => 'ticket',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'post_parent'    => get_the_ID(),
		);

		$subquery = new WP_Query( $args );
		foreach ( $subquery->posts as $post ) :
			$user = get_userdata( $post->post_author );
			?>
			<li class="history-item
					<?php
					if ( $ticketauthor_id == $post->post_author ) {
						echo 'by-ticketowner';}
			?>
" id="answer-<?php echo $post->ID; ?>">
				<h3>
							<span>
								<?php
								// translators: %s is the user name.
								printf( __( 'by %s', 'support-ticket' ), $user->data->display_name );
								?>
								,
								<?php echo get_the_time( get_option( 'date_format' ), $post->ID ) . ', ' . get_the_time( get_option( 'time_format' ), $post->ID ); ?>
							</span>
					<?php echo get_the_title( $post->ID ); ?>
				</h3>
				<div class="entry">
					<?php
					$pattern            = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`\!()\[\]{};:\'".,<>?«»“”‘’]))';
					$post->post_content = preg_replace( "!$pattern!i", "<a href=\"\\0\" rel=\"nofollow\" target=\"_blank\">\\0</a>", $post->post_content );
					echo apply_filters( 'the_content', $post->post_content );
					?>
				</div>
			</li>
			<?php
		endforeach;
		?>
	</ul>
	<?php
}
