<?php

/**
 * Update the User agent settings
 *
 * Explanations on this filter can be found in admin/inc/functions-settings.php
 *
 *
 * @since 1.0.0
 *
 * @param (mixed)       $return
 *
 * @return (mixed)      $return
 **/
function sts_settings_user_standard_agent( $return ) {
	$settings       = get_option( 'sts-core-settings' );
	$standard_agent = 1;
	if ( isset( $_POST['user']['standard-agent'] ) ) {
		$standard_agent = (int) $_POST['user']['standard-agent'];
	}
	$settings['user']['standard-agent'] = $standard_agent;

	update_option( 'sts-core-settings', $settings );

	return $return;
}
add_filter( 'sts-settings-update-user', 'sts_settings_user_standard_agent' );

/**
 * Renders the standard agent metabox
 *
 * @since 1.0.0
 *
 * @param (stdClass)    $args   empty
 *
 * @return (void)
 **/
function sts_settings_metabox_user_agent_render( $args ) {
	$standard_agent = 1;
	$settings       = get_option( 'sts-core-settings' );
	if ( isset( $settings['user']['standard-agent'] ) ) {
		$standard_agent = $settings['user']['standard-agent'];
	}

	$agents = sts_get_possible_agents();

	?>
	<section>
		<label for="standard-ticket-agent"><?php _e( 'New tickets are assigned to', 'support-ticket' ); ?></label>
		<select id="standard-ticket-agent" type="checkbox" name="user[standard-agent]">
			<?php
			foreach ( $agents as $s ) :
				if ( is_string( $s ) ) :
					?>
					<option disabled>---------------------</option>
				<?php else : ?>
					<option
						value="<?php echo $s->ID; ?>" <?php selected( $s->ID, $standard_agent ); ?>><?php echo $s->data->display_name; ?></option>
					<?php
				endif;
			endforeach;
			?>
		</select>
	</section>
	<?php
}
