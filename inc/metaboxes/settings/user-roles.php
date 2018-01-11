<?php

/**
 * Renders the user roles & capabilities metabox
 *
 * @since 1.0.5
 *
 * @param (stdClass)    $args   empty
 * @return (void)
 **/
function sts_settings_metabox_user_roles_render( $args ) {
	$roles = get_editable_roles();
	?>
	<table class="wp-list-table widefat fixed striped">
		<thead>
		<tr>
			<th><?php _e( 'Role', 'support-ticket' ); ?></th>
			<th><?php _e( 'Create Ticket', 'support-ticket' ); ?></th>
			<th><?php _e( 'Read assigned tickets', 'support-ticket' ); ?></th>
			<th><?php _e( 'Read others tickets', 'support-ticket' ); ?></th>
			<th><?php _e( 'Assign tickets to agents', 'support-ticket' ); ?></th>
			<th><?php _e( 'Delete tickets', 'support-ticket' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $roles as $role_key => $role ) : ?>
			<tr>
				<th><?php echo $role['name']; ?></th>
				<th>
					<?php
					$checked = '';
					if ( isset( $role['capabilities']['read_own_tickets'] ) && 1 === (int) $role['capabilities']['read_own_tickets'] ) {
						$checked = 'checked="checked"';
					}
					?>
					<input type="checkbox" name="user[roles][read_own_tickets][<?php echo $role_key; ?>]" value="1" <?php echo $checked; ?> />
				</th>
				<th>
					<?php
					$checked = '';
					if ( isset( $role['capabilities']['read_assigned_tickets'] ) && 1 === (int) $role['capabilities']['read_assigned_tickets'] ) {
						$checked = 'checked="checked"';
					}
					?>
					<input type="checkbox" name="user[roles][read_assigned_tickets][<?php echo $role_key; ?>]" value="1" <?php echo $checked; ?> />
				</th>
				<th>
					<?php
					$checked = '';
					if ( isset( $role['capabilities']['read_other_tickets'] ) && 1 === (int) $role['capabilities']['read_other_tickets'] ) {
						$checked = 'checked="checked"';
					}
					?>
					<input type="checkbox" name="user[roles][read_other_tickets][<?php echo $role_key; ?>]" value="1" <?php echo $checked; ?> />
				</th>
				<th>
					<?php
					$checked = '';
					if ( isset( $role['capabilities']['assign_agent_to_ticket'] ) && 1 === (int) $role['capabilities']['assign_agent_to_ticket'] ) {
						$checked = 'checked="checked"';
					}
					?>
					<input type="checkbox" name="user[roles][assign_agent_to_ticket][<?php echo $role_key; ?>]" value="1" <?php echo $checked; ?> />
				</th>
				<th>
					<?php
					$checked = '';
					if ( isset( $role['capabilities']['delete_other_tickets'] ) && 1 === (int) $role['capabilities']['delete_other_tickets'] ) {
						$checked = 'checked="checked"';
					}
					?>
					<input type="checkbox" name="user[roles][delete_other_tickets][<?php echo $role_key; ?>]" value="1" <?php echo $checked; ?> />
				</th>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Update the Role & Capabilites settings
 *
 * Explanations on this filter can be found in admin/inc/functions-settings.php
 *
 *
 * @since 1.0.5
 *
 * @param (mixed)       $return
 * @return (mixed)      $return
 **/
add_filter( 'sts-settings-update-user', 'sts_settings_user_update_roles' );
function sts_settings_user_update_roles( $return ) {
	$roles    = get_editable_roles();
	$role_arr = array();
	foreach ( $roles as $role_key => $role ) {
		$role_arr[ $role_key ] = array(
			'read_own_tickets'       => false,
			'read_assigned_tickets'  => false,
			'read_other_tickets'     => false,
			'delete_other_tickets'   => false,
			'update_tickets'         => false,
			'assign_agent_to_ticket' => false,
		);
	}

	foreach ( $_POST['user']['roles'] as $cap => $roles_with_cap ) {
		foreach ( $roles_with_cap as $role_name => $val ) {
			if ( 1 === (int) $val ) {
				$role_arr[ $role_name ][ $cap ] = true;
				if ( 'read_assigned_tickets' === $cap ) {
					$role_arr[ $role_name ]['update_tickets'] = true;
				}
				if ( 'read_other_tickets' === $cap ) {
					$role_arr[ $role_name ]['read_assigned_tickets'] = true;
					$role_arr[ $role_name ]['update_tickets']        = true;
				}
			}
		}
	}

	foreach ( $role_arr as $role => $caps ) {
		$role = get_role( strtolower( $role ) );
		foreach ( $caps as $cap => $has_cap ) {
			$role->add_cap( $cap, $has_cap );
		}
	}

	/**
	 * After the roles have been updated.
	 *
	 * @since 1.0.5
	 *
	 * @param   (array) $role_arr    The roles Array
	 **/
	do_action( 'sts-after-roles-updated', $role_arr );
	return $return;
}
