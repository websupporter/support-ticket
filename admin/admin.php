<?php

/**
 * Register Scripts & Styles
 * Adds the scripts and styles for the backend end
 *
 * @since   1.0.0
 * @wp-hook admin_enqueue_scripts
 */
function sts_adminscripts( $hook ) {

	$sts_page = false;
	if ( preg_match( '^sts^', $hook ) ) {
		$sts_page = true;
	}

	if ( ! $sts_page ) {
		return;
	}

	wp_enqueue_style( 'sts-admin-style', STS_URL . 'assets/css/admin.css' );
	wp_enqueue_script( 'sts-admin-script', STS_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-sortable' ) );

	$sts_localize = array(
		'trash'      => esc_html__( 'Trash', 'support-ticket' ),
		'edit'       => esc_html__( 'Edit', 'support-ticket' ),
		'inputfield' => esc_html__( 'Input field', 'support-ticket' ),
		'selectbox'  => esc_html__( 'Selectbox', 'support-ticket' ),
	);

	wp_localize_script( 'sts-admin-script', 'stsLocalize', $sts_localize );

	add_action( 'admin_footer_text', 'sts_admin_thankyou' );
}
add_action( 'admin_enqueue_scripts', 'sts_adminscripts' );

/**
 * Extend Admin Menu
 *
 * @since .0.0
 * @wp-hook admin_menu
 */
function wp_sf_adminpage() {

	global $wpdb;

	//Find no of unread tickets of user
	$unread = 0;
	if ( current_user_can( 'read_assigned_tickets' ) ) {

		$all = $wpdb->get_results(
			$wpdb->prepare(
				'
			select 
				count( a.post_id ) as alltickets
			from
				' . $wpdb->postmeta . ' as a
			where (
				a.meta_key = "ticket-agent" &&
				a.meta_value = %d
			)',
				get_current_user_id()
			)
		);

		$res    = $wpdb->get_results(
			$wpdb->prepare(
				'
			select 
				count( r.post_id )  as readtickets
			from
				' . $wpdb->postmeta . ' as r,
				' . $wpdb->postmeta . ' as a
			where (
				a.meta_key = "ticket-agent" &&
				a.meta_value = %d &&
				a.post_id = r.post_id &&
				r.meta_key = "ticket-read" &&
				r.meta_value = 1
			)',
				get_current_user_id()
			)
		);
		$unread = $all[0]->alltickets - $res[0]->readtickets;
	}

	$tickets_title = esc_html__( 'Tickets', 'support-ticket' );
	if ( $unread > 0 ) {
		$tickets_title .= ' (' . $unread . ')';
	}
	add_menu_page( $tickets_title, $tickets_title, 'read_own_tickets', 'sts', 'sts_admin_outpout_index' );
	add_submenu_page( 'sts', esc_html__( 'New Ticket', 'support-ticket' ), esc_html__( 'New Ticket', 'support-ticket' ), 'read_own_tickets', 'sts-new', 'sts_admin_outpout_new_ticket' );
	add_submenu_page( 'sts', esc_html__( 'Settings', 'support-ticket' ), esc_html__( 'Settings', 'support-ticket' ), 'manage_options', 'sts-settings', 'sts_admin_outpout_settings' );
}
add_action( 'admin_menu', 'wp_sf_adminpage' );

function sts_admin_outpout_index() {

	require_once dirname( __FILE__ ) . '/index.php';
}

function sts_admin_outpout_new_ticket() {

	require_once dirname( __FILE__ ) . '/ticket-new.php';
}

function sts_admin_outpout_settings() {

	require_once dirname( __FILE__ ) . '/settings.php';
}

function sts_admin_thankyou() {

	// translators: %s is the name of the plugin.
	return '<span id="footer-thankyou">' . wp_kses_post( sprintf( __( 'Thank you for using %s.' ), '<a href="http://wpsupportticket.com">Support Ticket Plugin</a>' ) ) . '</span>';
}



/**
 * Admin init
 *
 * Do the bulk actions here
 *
 * @since 1.0.0
 * @wp-hook admin_init
*/
function sts_admin_init() {
	if (
		! isset( $_POST['sts-action'] ) || // Input var okay.
		! isset( $_POST['action'] ) || // Input var okay.
		! isset( $_POST['ticket'] ) || // Input var okay.
		! is_array( $_POST['ticket'] ) // Input var okay.
	) {
		return;
	}

	if (
		(
			! isset( $_POST['t-nonce'] ) // Input var okay.
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['t-nonce'] ) ), 'sts-bluk-action' ) // Input var okay.
		)
		&& 'bulk-action' === sanitize_text_field( wp_unslash( $_POST['sts-action'] ) ) // Input var okay.
	) {
		wp_die( esc_html__( 'Something went wrong :/', 'support-ticket' ) );
	}

	if ( 'delete' === sanitize_text_field( wp_unslash( $_POST['action'] ) ) && ! current_user_can( 'delete_other_tickets' ) ) { // Input var okay.
		wp_die( esc_html__( 'Something went wrong :/', 'support-ticket' ) );
	}

	foreach ( wp_unslash( $_POST['ticket'] ) as $raw_ticket ) { // Input var okay.
		$ticket = (int) $raw_ticket;

		$args = array(
			'post_type'      => 'ticket',
			'post_parent'    => $ticket,
			'posts_per_page' => -1,
		);

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				wp_delete_post( get_the_ID(), true );
			}
		}
		wp_reset_query();

		wp_delete_post( $ticket, true );
	}

	$url = add_query_arg( array( 'updated' => 1 ) );
	wp_safe_redirect( $url );
}
add_action( 'admin_init', 'sts_admin_init' );
