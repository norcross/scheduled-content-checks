<?php
/**
 * Include a basic CLI command for running our manual check.
 *
 * @package ScheduledContentChecks
 */

// Declare our namespace (same as the main).
namespace ScheduledContentChecks;

// Pull in the CLI items.
use WP_CLI;
use WP_CLI_Command;

/**
 * Extend the CLI command class with our own.
 */
class ScheduledContentCommand extends WP_CLI_Command {

	/**
	 * Get the array of arguments for the runcommand function.
	 *
	 * @param  array $custom_args  Any custom args to pass.
	 *
	 * @return array
	 */
	protected function get_command_args( $custom_args = [] ) {

		// Set my base args.
		$setup_args = [
			'return'     => true,
			'parse'      => 'json',
			'launch'     => false,
			'exit_error' => false,
		];

		// Return either the base args, or the merged item.
		return ! empty( $custom_args ) ? wp_parse_args( $custom_args, $setup_args ) : $setup_args;
	}

	/**
	 * Run our manual check.
	 *
	 * ## OPTIONS
	 *
	 * [--dry-run]
	 * : Whether to perform a dry run, showing what would be updated without actually updating.
	 * ---
	 * default: false
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp locus-content run-check
	 *     wp locus-content run-check --dry-run=true
	 *
	 * @alias run-check
	 *
	 * @when after_wp_load
	 */
	function run_manual_check( $args = [], $assoc_args = [] ) {

		// Parse out the associatives.
		$parse_cli_args = wp_parse_args( $assoc_args, [
			'dry-run' => false,
		]);

		// Get the possible book data.
		$post_data  = Queries\get_current_posts_for_book_reviews( [ 'numberposts' => absint( $parse_cli_args['count'] ) ] );

		// Bail if it failed.
		if ( empty( $post_data ) ) {
			WP_CLI::error( __( 'No post data could be found to use.', 'scheduled-content-checks' ) );
		}

		// Say how many we have.
		WP_CLI::line( sprintf( __( '%d possible post(s) found!', 'scheduled-content-checks' ), count( $post_data ) ) );

		// And set some counters.
		$updated    = 0;
		$errored    = 0;

		// Set up the progress bar.
		$setup_progress = \WP_CLI\Utils\make_progress_bar( __( 'Beginning book review conversion...', 'scheduled-content-checks' ), count( $post_data ) );

		// Now loop them.
		foreach ( $post_data as $post_id ) {

			// Try to increase our 5 minute time limit with an additional 2.
			set_time_limit( 200 );

			// Set the ticker.
			$setup_progress->tick();

			// Attempt to run our update.
			$attempt_conversion = PostActions\convert_post_to_review( $post_id );

			// Bail if the conversion didn't work.
			if ( empty( $attempt_conversion ) || ! empty( $attempt_conversion['error'] ) ) {

				// Increment it.
				$errored++;

				// Set some flag.
				update_post_meta( $post_id, Core\META_PREFIX . 'convert_failed', true );

				// And continue.
				continue;
			}

			// Increment.
			$updated++;

			// Nothing left here.
		}

		// Now finish the progress bar.
		$setup_progress->finish();

		// Now tell me I did a good job.
		WP_CLI::success( sprintf( __( 'Finished. %d item(s) were converted and %d item(s) failed.', 'scheduled-content-checks' ), $updated, $errored ) );
		WP_CLI::halt( 0 );
	}

	// End all custom CLI commands.
}
