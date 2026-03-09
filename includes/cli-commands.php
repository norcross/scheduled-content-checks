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
class ScheduledContentCommands extends WP_CLI_Command {

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
	 *     wp scc-commands run-check
	 *     wp scc-commands run-check --dry-run=true
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

		// See if we missed any.
		$missed_ids = \ScheduledContentChecks\Queries\get_missed_scheduled_content();

		// Throw an empty line.
		WP_CLI::log( '', $assoc_args );

		// Show the message if none were found.
		if ( empty( $missed_ids ) ) {

			// Show the message.
			WP_CLI::log( WP_CLI::colorize( '%c' . __( 'There is currently no missed scheduled content.', 'scheduled-content-checks' ) . '%n' ), $assoc_args );

			// Then break and end.
			WP_CLI::log( '', $assoc_args );
			WP_CLI::halt( 0 );
		}

		// Set our count.
		$missed_num = count( $missed_ids );

		// If we did a dry run, just show the count.
		if ( ! empty( $parse_cli_args['dry-run'] ) ) {

			// Show the message.
			WP_CLI::log( WP_CLI::colorize( '%c' . sprintf( _n( '%d missed scheduled item was found.', '%d missed scheduled items were found.', absint( $missed_num ), 'scheduled-content-checks' ), absint( $missed_num ) ) . '%n' ), $assoc_args );

			// Then break and end.
			WP_CLI::log( '', $assoc_args );
			WP_CLI::halt( 0 );
		}

		// Set up the progress bar.
		$setup_progress = \WP_CLI\Utils\make_progress_bar( __( 'Beginning missed content publishing...', 'scheduled-content-checks' ), absint( $missed_num ) );

		// Now loop and publish any missing.
		foreach ( $missed_ids as $post_id ) {

			// Set the ticker.
			$setup_progress->tick();

			// Just run the publish, which doesn't return anything.
			wp_publish_post( $post_id );
		}

		// Now finish the progress bar.
		$setup_progress->finish();

		// Include a line break.
		WP_CLI::log( '', $assoc_args );

		// Now tell me I did a good job.
		WP_CLI::success( sprintf( _n( 'Complete! %d content item was published.', 'Complete! %d content items were published.', absint( $missed_num ), 'scheduled-content-checks' ), absint( $missed_num ) ) );

		// Then break and end.
		WP_CLI::log( '', $assoc_args );
		WP_CLI::halt( 0 );
	}

	// End all custom CLI commands.
}
