<?php

/**
 * Renders the ticket fields metabox
 *
 * @since 1.0.0
 *
 * @param (stdClass)    $args   empty
 * @return (void)
 **/
function sts_settings_metabox_metafields_render( $args ) {
	$fields        = sts_get_create_ticket_form_fields( 'edit' );
	$core_fields   = array( 'ticket-user', 'ticket-email', 'ticket-subject', 'ticket-message' );
	$metafields    = get_option( 'sts-metafields', array() );
	$edited_fields = array();
	foreach ( $metafields as $field ) {
		$edited_fields[] = $field['id'];
	}
	?>

	<ul class="ticket-field-list">
		<?php
		foreach ( $fields as $field ) :
			if ( in_array( $field['id'], $edited_fields ) ) :
				?>
				<li class="editable">
					<input name="ticket[fields][id][]" value="<?php echo $field['id']; ?>" type="hidden"/><textarea name="ticket[fields][fields][]" style="display:none;"><?php echo json_encode( $field ); ?></textarea>
					<?php echo $field['label']; ?>
					<a href="#" title="<?php _e( 'Trash', 'support-ticket' ); ?>" class="sts-delete dashicons dashicons-trash"></a>
					<a href="#" title="<?php _e( 'Edit', 'support-ticket' ); ?>" class="sts-edit-field dashicons dashicons-edit"></a>
				</li>
				<?php
			endif;
		endforeach;
		?>
	</ul>
	<hr />
	<a id="btn-sts-create-new-ticket-field" title="<?php _e( 'Add a new form field', 'support-ticket' ); ?>" href="#TB_inline?width=250&height=auto&inlineId=sts-create-new-ticket-field" class="thickbox button button-large"><div class="dashicons dashicons-plus"></div> <?php _e( 'Create new field', 'support-ticket' ); ?></a>
	<a id="btn-sts-edit-ticket-field" href="#TB_inline?width=250&height=auto&inlineId=sts-edit-ticket-field" class="thickbox"></a>
	<div id="sts-edit-ticket-field" style="display:none;">
		<input type="hidden" id="edit_metakey" value="" />
		<input type="hidden" id="edit_tag" value="" />
		<input type="hidden" id="edit_li_index" value="" />
		<p>
			<label for="edit_label"><?php _e( 'Label', 'support-ticket' ); ?>:</label>
			<br />
			<input type="text" id="edit_label" value="" />
		</p>
		<p>
			<label for="edit_metakey_display"><?php _e( 'Meta key', 'support-ticket' ); ?>:</label>
			<br />
			<span id="edit_metakey_display"></span>
		</p>
		<p>
			<label for="edit_tag_display"><?php _e( 'Type', 'support-ticket' ); ?>:</label>
			<br />
			<span id="edit_tag_display"></span>
		</p>
		<div id="sts-formfield-choices-edit-wrapper" style="display:none;">
			<p>
				<label for="edit_choices"><?php _e( 'Choices', 'support-ticket' ); ?>:</label>
				<br />
				<textarea id="edit_choices"></textarea>
				<br />
				<small><?php _e( 'Use a new line for each choice.', 'support-ticket' ); ?></small>
			</p>
		</div>
		<button class="button default" id="do-sts-edit-ticket-field"><?php _e( 'Edit', 'support-ticket' ); ?></button>
	</div>

	<div id="sts-create-new-ticket-field" style="display:none;">
		<p>
			<label for="label"><?php _e( 'Label', 'support-ticket' ); ?>:</label>
			<br />
			<input type="text" id="label" value="" />
		</p>
		<p>
			<label for="metakey"><?php _e( 'Meta key', 'support-ticket' ); ?>:</label>
			<br />
			<input type="text" id="metakey" value="" />
		</p>
		<p>
			<label for="type"><?php _e( 'Type', 'support-ticket' ); ?>:</label>
			<br />
			<select type="text" id="tag">
				<option value="input"><?php _e( 'Input field', 'support-ticket' ); ?></option>
				<option value="select"><?php _e( 'Selectbox', 'support-ticket' ); ?></option>
			</select>
		</p>
		<div id="sts-formfield-choices-wrapper" style="display:none;">
			<p>
				<label for="choices"><?php _e( 'Choices', 'support-ticket' ); ?>:</label>
				<br />
				<textarea id="choices"></textarea>
				<br />
				<small><?php _e( 'Use a new line for each choice.', 'support-ticket' ); ?></small>
			</p>
		</div>
		<button class="button default" id="do-sts-create-new-ticket-field"><?php _e( 'Add', 'support-ticket' ); ?></button>
	</div>
	<?php
}
