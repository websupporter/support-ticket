<?php

class STS_Tickets_Table extends WP_List_Table {

	function get_bulk_actions() {
		$actions = array();
		if ( current_user_can( 'delete_other_tickets' ) ) {
			$actions['delete'] = __( 'Delete' );
		}
		/**
		 * Filters the bulk actions for the ticket overview table
		 *
		 * @since 1.0.5
		 *
		 * @param    (array)     $actions    the actions
		 * @return   (array)     $actions    the actions
		 */
		return apply_filters( 'sts-tickets-table-bulk-actions', $actions );
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="ticket[]" value="%s" />', $item->ID
		);
	}

	function get_columns() {
		$columns = array();
		/**
		 * Filter the columns of the table
		 *
		 * @since 1.0.0
		 *
		 * @param (array)    $columns    the status array
		 * @return (array)   $columns    the status array
		 */
		return apply_filters( 'sts-tickets-table-columns', $columns );
	}

	function get_data() {
		global $post;
		$data = array();

		$paged = 1;
		if ( isset( $_GET['paged'] ) ) {
			$paged = (int) $_GET['paged'];
		}

		$tickets_per_page = (int) get_option( 'posts_per_page', 10 );
		/**
		 * Filter how many tickets should be shown per page.
		 *
		 * @since 1.1.0
		 *
		 * @param (int)    $tickets_per_page
		 * @return (int)   $tickets_per_page
		 */
		$tickets_per_page = (int) apply_filters( 'sts-tickets-per-page', $tickets_per_page );

		$args = array(
			'post_type'      => 'ticket',
			'post_status'    => 'any',
			'post_parent'    => 0,
			'posts_per_page' => $tickets_per_page,
			'paged'          => $paged,
		);

		/**
		 * Filter the Query args for the table
		 *
		 * @since 1.0.0
		 *
		 * @param (array)    $args   the query args
		 * @return (array)   $args   the query args
		 */
		$args = apply_filters( 'sts-tickets-table-postargs', $args );

		if ( ! current_user_can( 'read_other_tickets' ) && ! current_user_can( 'read_assigned_tickets' ) ) {
			$args['author'] = get_current_user_id();
		} elseif ( ! current_user_can( 'read_other_tickets' ) ) {
			$args['meta_query'] = array(
				array(
					'key'   => 'ticket-agent',
					'value' => get_current_user_id(),
				),
			);
		}
		$query = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			$data[] = $post;
		}

		$this->set_pagination_args(
			array(
				'total_items' => $query->found_posts,
				'per_page'    => $tickets_per_page,
			)
		);

		return $data;
	}

	function no_items() {
		_e( 'No tickets found.', 'support-ticket' );
	}

	function extra_tablenav( $which ) {
		echo '<div class="alignleft actions">';
		/**
		 * Action, to add tablenav elements
		 *
		 * @since 1.0.0
		 *
		 * @param (string)  $which  'top' or 'bottom'
		 **/
		do_action( 'sts-extra-tablenav', $which );
		echo '</div>';
	}

	function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_data();
	}

	function single_row( $item ) {
		$tr_classes = array(
			'status-' . sanitize_key( sts_get_the_status( $item->ID, 'class' ) ),
		);
		if ( 'unread' == sts_get_the_ticket_read( $item->ID ) && current_user_can( 'read_other_tickets' ) ) {
			$tr_classes[] = 'ticket-unread';
		}

		echo '<tr class="' . implode( ' ', $tr_classes ) . '">';
		echo $this->single_row_columns( $item );
		echo '</tr>';

	}

	function display_tablenav( $which ) {
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php if ( 'top' == $which ) : ?>
			<form method="get">
				<input type="hidden" name="page" value="sts" />
				<?php $this->extra_tablenav( $which ); ?>
			</form>

			<form method="post">
				<?php wp_nonce_field( 'sts-bulk-action', 't-nonce' ); ?>
				<input type="hidden" name="sts-action" value="bulk-action" />
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
				<?php endif; ?>
				<?php $this->pagination( $which ); ?>
				<br class="clear" />
		</div>
		<?php
	}

	function column_default( $item, $column_name ) {
		$rendered = '';

		/**
		 * Filter the column rendering output
		 * @since 1.0.0
		 *
		 * @param (string)   $rendered       the rendered output
		 * @param (stdClass) $item           the post item
		 * @param (string)   $column_name    the column name
		 * @return (string)  $rendered       the rendered output
		 */
		$rendered = apply_filters( 'sts-tickets-table-column', $rendered, $item, $column_name );

		/**
		 * Filter the column rendering output for a specific column
		 * @since 1.0.0
		 *
		 * @param (string)   $rendered       the rendered output
		 * @param (stdClass) $item           the post item
		 * @return (string)  $rendered       the rendered output
		 */
		return apply_filters( 'sts-tickets-table-column-' . $column_name, $rendered, $item );
	}
}
