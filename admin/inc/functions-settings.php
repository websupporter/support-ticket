<?php
/**
 * Add the core settings for the plugin.
 *
 * @since   1.0.0
 *
 * @param (array)   $sts_setting_content    The setting sections
 * @return (array)  $sts_setting_content    The setting sections
 **/
add_filter( 'sts-settings-content', 'sts_setting_core_content' );
function sts_setting_core_content( $sts_setting_content ) {
	$sts_setting_content[] = array(
		'id'          => 'welcome',
		'title'       => __( 'Welcome', 'sts' ),
		'before_form' => '<p style="text-align:center"><img width="50%" src="' . STS_URL . 'assetts/logo-large.svg" alt="" /></p><hr /><p style="text-align:center"><strong>' . __( 'Welcome to WP Support Ticket!', 'sts' ) . '</strong><br />' . __( 'On this page, you can configure the plugin, so it fits your needs.', 'sts' ) . '</p>',
		'after_form'  => '',
	);
	$sts_setting_content[] = array(
		'id'          => 'email',
		'title'       => __( 'Email settings', 'sts' ),
		'before_form' => '',
		'after_form'  => '',
	);
	$sts_setting_content[] = array(
		'id'          => 'user',
		'title'       => __( 'User settings', 'sts' ),
		'before_form' => '',
		'after_form'  => '',
	);
	$sts_setting_content[] = array(
		'id'          => 'ticket',
		'title'       => __( 'Ticket settings', 'sts' ),
		'before_form' => '',
		'after_form'  => '',
	);

	return $sts_setting_content;
}
