<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


	/**
	 * Define the standard column for the table
	*
	 * @since    1.0.6
	 *
	 * @param (array)    $columns
	 * @return (array)   $columns
	 **/
function sts_standard_table_column( $column, $screen ) {
	$column = 'subject';
	/**
		 * Filters the standard table column
		 * which was introduced in WP 4.3
		 * see https://make.wordpress.org/core/2015/08/08/list-table-changes-in-4-3/
		 *
		 * @since 1.0.5
		 *
		 * @param    (string)    $column     the columns ID
		 * @return   (string)    $column     the columns ID
		 */
	$column = apply_filters( 'sts-standard-table-column', $column );
	return $column;
}

	/**
	 * Add the standard columns
	*
	 * @since    1.0.0
	 *
	 * @param (array)    $columns
	 * @return (array)   $columns
	 **/
	add_filter( 'sts-tickets-table-columns', 'sts_ticket_table_columns', 1, 1 );
function sts_ticket_table_columns( $columns ) {
	if ( current_user_can( 'read_assigned_tickets' ) ) {
		$columns = array_merge( $columns, array( 'unread' => '' ) );
	}

		return array_merge(
			$columns, array(
				'cb'      => '<input type="checkbox" />',
				'subject' => __( 'Subject', 'support-ticket' ),
				'ID'      => __( 'ID', 'support-ticket' ),
				'date'    => __( 'Date', 'support-ticket' ),
				'from'    => __( 'From', 'support-ticket' ),
				'status'  => __( 'Status', 'support-ticket' ),
			)
		);
}

	/**
	 * Render the standard columns
	*
	 * @since    1.0.0
	 *
	 * @param (string)   $current        current output
	 * @param (stdClass) $item           current post item
	 * @param (string)   $column_name    the column ID
	 * @return (string)                  updated output
	 **/
	add_filter( 'sts-tickets-table-column', 'sts_ticket_table_column_render', 1, 3 );
function sts_ticket_table_column_render( $current, $item, $column_name ) {
	switch ( $column_name ) {
		case 'unread':
			if ( 'unread' == sts_get_the_ticket_read( $item->ID ) && current_user_can( 'read_assigned_tickets' ) ) {
				return '<a href="admin.php?page=sts&action=single&ID=' . $item->ID . '"><span title="' . __( 'Unread', 'support-ticket' ) . '" class="ticket-unread">' . __( 'Unread', 'support-ticket' ) . '</span></a>';
			}
			return '';
		case 'ID':
			return '<a href="admin.php?page=sts&action=single&ID=' . $item->ID . '">' . $item->ID . '</a>';
		case 'subject':
			return '<a href="admin.php?page=sts&action=single&ID=' . $item->ID . '">' . get_the_title( $item->ID ) . '</a>';
		;
		case 'from':
			if ( 0 === (int) $item->post_author ) {
				return 0;
			}

			$user = get_userdata( $item->post_author );
			if ( ! $user ) {
				return __( 'User not found :/', 'support-ticket' );
			}
			return $user->data->display_name . ' &lt;' . $user->data->user_email . '&gt;';
		case 'date':
			return get_the_time( get_option( 'date_format' ), $item->ID ) . ', ' . get_the_time( get_option( 'time_format' ), $item->ID );
		case 'status':
			return sts_get_the_status( $item->ID );
	}
}

	/**
	 * Select only specific ticket status
	*
	 * @since    1.0.0
	 *
	 * @param (array)    $columns
	 * @return (array)   $columns
	 **/
	add_filter( 'sts-tickets-table-postargs', 'sts_tickets_table_postargs' );
function sts_tickets_table_postargs( $args ) {
	if ( ! isset( $_GET['status'] ) || -1 === (int) wp_unslash( $_GET['status'] ) ) {
		return $args;
	}

	$status = (int) $_GET['status'];

	if ( ! isset( $args['meta_query'] ) ) {
		$args['meta_query'] = array();
	}

	$args['meta_query'][] = array(
		'key'   => 'ticket-status',
		'value' => $status,
	);

	return $args;
}

	/**
	 * Add status filter
	*
	 * @since    1.0.0
	 *
	 * @param (string)   $which      'top' or 'bottom'
	 * @return (void)
	 **/
	add_action( 'sts-extra-tablenav', 'sts_table_add_status_filter' );
function sts_table_add_status_filter( $which ) {

		echo '<label class="screen-reader-text" for="status-filter">' . __( 'Filter by ticket status', 'support-ticket' ) . '</label>';
		echo '<select id="status-filter" name="status">';
		$status_array         = sts_get_status_arr();
		$current_status_index = -1;
	if ( isset( $_GET['status'] ) ) {
		$current_status_index = (int) $_GET['status'];
	}

		echo '<option value="-1" ' . selected( -1, $current_status_index, false ) . '>' . __( 'Every status', 'support-ticket' ) . '</option>';
	foreach ( $status_array as $status_index => $status ) {
		echo '<option ' . selected( $status_index, $current_status_index, false ) . ' value="' . $status_index . '">' . $status . '</option>';

	}
		echo '</select>';
		submit_button( __( 'Filter' ), 'button', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
		echo '';
}

