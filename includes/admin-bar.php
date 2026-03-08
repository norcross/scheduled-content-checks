<?php
/**
 * Handle our specific admin bar stuff.
 *
 * @package ScheduledContentChecks
 */

// Declare our namespace.
namespace ScheduledContentChecks\AdminBar;

/**
 * Start our engines.
 */
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_admin_bar_css' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\load_admin_bar_css' );
add_action( 'admin_bar_menu', __NAMESPACE__ . '\add_missed_schedule_link', 9999 );

/**
 * Load some basic CSS for the admin bar.
 *
 * @return void
 */
function load_admin_bar_css() {

	// Bail if current user doesn't have cap.
	if ( ! current_user_can( \ScheduledContentChecks\AdminSetup\get_user_cap_for_run() ) ) {
		return;
	}

	// Set my CSS up.
	$admin_bar_css  = '
		#wpadminbar #wp-admin-bar-missed-schedule-link .ab-icon::before {
			content: "\f469";
			top: 3px;
		}';

	// And add the CSS.
	wp_add_inline_style( 'admin-bar', $admin_bar_css );
}

/**
 * Add our quick link to the admin bar to run the missed schedule check.
 *
 * @param  WP_Admin_Bar $wp_admin_bar The admin bar object.
 *
 * @return void                       If current user can't manage and we bail early.
 */
function add_missed_schedule_link( \WP_Admin_Bar $wp_admin_bar ) {

	// Bail if current user doesn't have cap.
	if ( ! current_user_can( \ScheduledContentChecks\AdminSetup\get_user_cap_for_run() ) ) {
		return;
	}

	// Set the query args.
	$setup_args = [
		'scc-run-check' => 'yes',
		'scc-run-nonce' => wp_create_nonce( \ScheduledContentChecks\NONCE_PREFIX . 'manual_run' ),
	];

	// Get our link with the query parameter.
	$setup_link = add_query_arg( $setup_args, admin_url( '/' ) );

	// Now add the admin bar link.
	$wp_admin_bar->add_node(
		[
			'id'        => 'missed-schedule-link',
			'title'     => '<span class="ab-icon" aria-hidden="true"></span>',
			'href'      => esc_url( $setup_link ),
			'position'  => 0,
			'meta'      => [
				'title'  => __( 'Check for missed scheduled content.', 'scheduled-content-checks' ),
				'target' => '_blank',
			],
		]
	);
}
