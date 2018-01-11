<?php

/**
 * Renders the answer metabox
 *
 * @since 1.0.0
 *
 * @param (object)  $post
 * @return (void)
 **/
function sts_metabox_answer_render( $post ) {

	?>
	<div class="ticket-answer">
		<section>
			<label for="answer-subject"><?php _e( 'Subject', 'support-ticket' ); ?></label>
			<div class="input-pre">
				<span>Re: [Ticket #<?php echo $_GET['ID']; ?>]</span>
				<input id="answer-subject" name="t[subject]" type="text" />
			</div>
		</section>
		<section>
			<label for="answer-answer"><?php _e( 'Message', 'support-ticket' ); ?></label>
			<div>
				<textarea id="answer-answer" name="t[answer]"></textarea>
			</div>
		</section>
		<section>
			<button class="button button-primary button-large"><?php _e( 'Answer', 'support-ticket' ); ?></button>
		</section>
	</div>
	<?php
}
