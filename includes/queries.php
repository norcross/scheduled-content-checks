<?php
/**
 * Abstracted queries.
 *
 * @package ScheduledContentChecks
 */

// Declare our namespace.
namespace ScheduledContentChecks\Queries;

/**
 * Get any items that missed their schedule.
 *
 * @return mixed
 */
function get_missed_scheduled_content() {

	// Get the types we wanna check.
	$types  = \ScheduledContentChecks\AdminSetup\get_post_types_for_check();

	// Return an empty array if we have no types.
	if ( empty( $types ) ) {
		return [];
	}

	// Call the global.
	global $wpdb;

	// Flush out the cache first.
	$wpdb->flush();

	// Set up our query.
	// phpcs:ignore -- this is set up exactly like core does it. ugly, but works.
	// phpcs:disable
	$query_args = $wpdb->prepare("
		SELECT   ID
		FROM     $wpdb->posts
		WHERE    post_status = '%s'
		AND      post_date_gmt < '%s'
		AND      post_type IN ( '" . implode( "','", $types ) . "' )
	", esc_attr( 'future' ), gmdate( 'Y-m-d H:i:00' ) );
	// phpcs:enable

	// Process the query.
	// phpcs:ignore -- the following is a false positive; this SQL is safe, everything is escaped above.
	$query_run  = $wpdb->get_col( $query_args );

	// Return what we have.
	return empty( $query_run ) || is_wp_error( $query_run ) ? [] : array_unique( $query_run );
}
