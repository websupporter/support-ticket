<?php # -*- coding: utf-8 -*-

class Sts_Metabox_Repository {

	/**
	 * @var Sts_Metabox[]
	 */
	private static $metaboxes = [];

	/**
	 * Add a metabox.
	 *
	 * @param $id
	 * @param $title
	 * @param $callback
	 * @param null $screen
	 * @param string $context
	 * @param string $priority
	 * @param null $callback_args
	 */
	public function add( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null ) {

		self::$metaboxes[ $id ] = new Sts_Metabox( $id, $title, $callback, $screen, $context, $priority, $callback_args );
	}

	/**
	 * Remove a metabox.
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function remove( $id ) {

		if ( ! isset( self::$metaboxes[ $id ] ) ) {
			return true;
		}

		unset( self::$metaboxes[ $id ] );
		return true;
	}

	/**
	 * Register our metaboxes.
	 */
	public function wp_register() {

		if ( ! $this->is_sts_page_with_metaboxes() ) {
			return;
		}

		foreach ( self::$metaboxes as $metabox ) {
			add_meta_box(
				$metabox->id(),
				$metabox->title(),
				$metabox->callback(),
				$metabox->screen(),
				$metabox->context(),
				$metabox->priority(),
				$metabox->callback_args()
			);
		}
	}

	/**
	 * Check, if we want to display metaboxes on the current page.
	 *
	 * @return bool
	 */
	private function is_sts_page_with_metaboxes() {

		if ( is_admin() && isset( $_GET['page'] ) && 'sts' === wp_unslash( $_GET['page'] ) && isset( $_GET['ID'] ) ) {
			return true;
		}

		if ( is_admin() && isset( $_GET['page'] ) && 'sts-settings' === wp_unslash( $_GET['page'] ) ) {
			return true;
		}

		return false;
	}
}
