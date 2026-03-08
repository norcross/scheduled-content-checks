<?php
/**
 * Handle the admin notices.
 *
 * @package ScheduledContentChecks
 */

// Declare our namespace.
namespace ScheduledContentChecks\AdminNotices;

/**
 * Start our engines.
 */
add_action( 'admin_notices', __NAMESPACE__ . '\display_admin_notices' );

/**
 * Possibly display some of the admin notices.
 *
 * @return void
 */
function display_admin_notices() {

	// Check the result string.
	$isa_result = filter_input( INPUT_GET, 'scc-run-result', FILTER_SANITIZE_SPECIAL_CHARS );

	// Only filter these on our results.
	if ( empty( $isa_result ) || ! in_array( $isa_result, ['empty', 'success'], true ) ) { // phpcs:ignore -- there is no nonce needed.
		return;
	}

	// Get my result count.
	$get_count  = filter_input( INPUT_GET, 'scc-run-count', FILTER_SANITIZE_NUMBER_INT );

	// Handle our empty ones.
	if ( 'empty' === $isa_result || empty( $get_count ) ) {

		// Set the text.
		$setup_text = esc_html__( 'There were no missed scheduled items found.', 'scheduled-content-checks' );

		// And display the notice.
		wp_admin_notice(
			'<strong>' . esc_html( $setup_text ) . '</strong>',
			[
				'type'        => 'info',
				'dismissible' => true,
			]
		);

		// And be done.
		return;
	}

	// Generate our success text.
	/* translators: %d: how many were found */
	$setup_text = sprintf( __( 'Success! %d missed items were published.', 'scheduled-content-checks' ), absint( $get_count ) );

	// And display the notice.
	wp_admin_notice(
		'<strong>' . esc_html( $setup_text ) . '</strong>',
		[
			'type'        => 'success',
			'dismissible' => true,
		]
	);

	// And be done.
	return;
}
