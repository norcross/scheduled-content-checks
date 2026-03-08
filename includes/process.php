<?php
/**
 * Handle processing the various post related action requests.
 *
 * @package ScheduledContentChecks
 */

// Declare our namespace.
namespace ScheduledContentChecks\Process;

/**
 * Start our engines.
 */
add_action( 'admin_init', __NAMESPACE__ . '\run_manual_missed_schedule_check' );
add_action( 'admin_init', __NAMESPACE__ . '\maybe_check_missed_schedules', 35 );

/**
 * Manually run our schedule check based on a query string.
 *
 * @return void
 */
function run_manual_missed_schedule_check() {

	// This never runs on front end.
	if ( ! is_admin() ) {
		return;
	}

	// See if we have a trigger.
	$check_trigger  = filter_input( INPUT_GET, 'scc-run-check', FILTER_SANITIZE_SPECIAL_CHARS );

	// Do nothing without it.
	if ( empty( $check_trigger ) || 'yes' !== $check_trigger ) {
		return;
	}

	// Grab the nonce value.
	$confirm_nonce  = filter_input( INPUT_GET, 'scc-run-nonce', FILTER_SANITIZE_SPECIAL_CHARS );

	// Handle the nonce check and die if there is a failure.
	if ( empty( $confirm_nonce ) || ! wp_verify_nonce( $confirm_nonce, \ScheduledContentChecks\NONCE_PREFIX . 'manual_run' ) ) {
		wp_die( esc_html__( 'There was an error validating the nonce.', 'scheduled-content-checks' ), esc_html__( 'Scheduled Content Checks', 'scheduled-content-checks' ), [ 'back_link' => true ] );
	}

	// Bail if current user doesn't have cap.
	if ( ! current_user_can( \ScheduledContentChecks\AdminSetup\get_user_cap_for_run() ) ) {
		wp_die( esc_html__( 'Sorry, you are not authorized to perform this action.', 'scheduled-content-checks' ), esc_html__( 'Scheduled Content Checks', 'scheduled-content-checks' ), [ 'back_link' => true ] );
	}

	// See if we missed any.
	$missed_ids = \ScheduledContentChecks\Queries\get_missed_scheduled_content();

	// If we have none, redirect with that.
	if ( empty( $missed_ids ) ) {

		// Now set up the link that'll redirect.
		$setup_args = [
			'scc-run-result' => 'empty',
			'scc-run-count'  => 0,
		];

		// Get the link itself.
		$setup_link  = add_query_arg( $setup_args, admin_url( '/' ) );

		// Do the redirect.
		wp_safe_redirect( $setup_link );
		exit;
	}

	// Now loop and publish any missing.
	foreach ( $missed_ids as $post_id ) {
		wp_publish_post( $post_id );
	}

	// Now set up the link that'll redirect.
	$setup_args = [
		'scc-run-result' => 'success',
		'scc-run-count'  => count( $missed_ids ),
	];

	// Get the link itself.
	$setup_link  = add_query_arg( $setup_args, admin_url( '/' ) );

	// Do the redirect.
	wp_safe_redirect( $setup_link );
	exit;
}

/**
 * Run our check on the hour to look for anything missed.
 *
 * @return void
 */
function maybe_check_missed_schedules() {

	// See if we have a trigger.
	$check_trigger  = filter_input( INPUT_GET, 'scc-run-check', FILTER_SANITIZE_SPECIAL_CHARS );

	// Don't try to run this while doing a manual.
	if ( ! empty( $check_trigger ) && 'yes' === $check_trigger ) {
		return;
	}

	// Get our timestamp.
	$last_run   = get_option( \ScheduledContentChecks\OPTION_PREFIX . 'last_run', 0 );

	// Bail if we aren't in the window.
	if ( ! empty( $last_run ) && ( absint( $last_run ) + HOUR_IN_SECONDS ) > time() ) {
		return;
	}

	// See if we missed any.
	$missed_ids = \ScheduledContentChecks\Queries\get_missed_scheduled_content();

	// Bail if we have none.
	if ( empty( $missed_ids ) ) {

		// Set our time run.
		update_option( \ScheduledContentChecks\OPTION_PREFIX . 'last_run', time() );

		// And be finished.
		return;
	}

	// Now loop and publish any missing.
	foreach ( $missed_ids as $post_id ) {
		wp_publish_post( $post_id );
	}

	// Set our time run.
	update_option( \ScheduledContentChecks\OPTION_PREFIX . 'last_run', time() );
}
