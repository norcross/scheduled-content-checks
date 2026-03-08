<?php
/**
 * Handle our admin side setup.
 *
 * @package ScheduledContentChecks
 */

// Declare our namespace.
namespace ScheduledContentChecks\AdminSetup;

/**
 * Start our engines.
 */
add_filter( 'plugins_api', __NAMESPACE__ . '\prevent_plugin_update_check', 100, 3 );
add_filter( 'removable_query_args', __NAMESPACE__ . '\admin_removable_args' );

/**
 * Prevent the WP plugin update check from looking at this one.
 *
 * @param  false|object|array $result  The result object or array. Default false.
 * @param  string             $action  The type of information being requested from the Plugin Installation API.
 * @param  object             $args    Plugin API arguments.
 *
 * @return boolean
 */
function prevent_plugin_update_check( $result, $action, $args ) {

	// Return the initial value if this isn't us.
	if ( empty( $args ) || ! is_object( $args ) || 'scheduled-content-checks' !== $args->slug ) {
		return $result;
	}

	// Create a new object.
	$empty_result = new \stdClass();

	// Set the plugin name because it wants that.
	$empty_result->name = __( 'Scheduled Content Checks', 'scheduled-content-checks' );

	// Return our new phantom.
	return $empty_result;
}

/**
 * Add our custom strings to the vars.
 *
 * @param  array $args  The existing array of args.
 *
 * @return array $args  The modified array of args.
 */
function admin_removable_args( $args ) {

	// Set an array of the args we wanna exclude.
	$remove = [
		'scc-run-check',
		'scc-run-nonce',
		'scc-run-result',
		'scc-run-count',
	];

	// Include my new args and return.
	return wp_parse_args( $remove, $args );
}

/**
 * Grab an array of post types we want the scheduled checks on.
 *
 * @return array
 */
function get_post_types_for_check() {
	return apply_filters( \ScheduledContentChecks\ACTION_PREFIX . 'checked_post_types', ['post', 'page'] );
}

/**
 * Set the default user permisson.
 *
 * @return string
 */
function get_user_cap_for_run() {
	return apply_filters( \ScheduledContentChecks\ACTION_PREFIX . 'allowed_user_perm', 'edit_others_posts' );
}
