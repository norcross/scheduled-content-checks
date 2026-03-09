<?php
/**
 * Plugin Name:      Scheduled Content Checks
 * Plugin URI:       https://github.com/norcross/scheduled-content-checks
 * Description:      A plugin to help check for missed scheduled content publishing.
 * Version:          0.0.2
 * Author:           Andrew Norcross
 * Author URI:       https://andrewnorcross.com/
 * Text Domain:      scheduled-content-checks
 * Domain Path:      /languages
 * License:          MIT
 * License URI:      https://opensource.org/licenses/MIT
 *
 * @package ScheduledContentChecks
 */

// Declare our namespace.
namespace ScheduledContentChecks;

// Call our CLI namespace.
use WP_CLI;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Define our plugin version.
define( __NAMESPACE__ . '\VERS', '0.0.2' );

// Set a few prefixes.
define( __NAMESPACE__ . '\ACTION_PREFIX', 'scc_' );
define( __NAMESPACE__ . '\NONCE_PREFIX', 'scc_nonce_' );
define( __NAMESPACE__ . '\OPTION_PREFIX', 'scc_setting_' );

// And load our files.
require_once __DIR__ . '/includes/admin-bar.php';
require_once __DIR__ . '/includes/notices.php';
require_once __DIR__ . '/includes/process.php';
require_once __DIR__ . '/includes/queries.php';
require_once __DIR__ . '/includes/setup.php';
/*
// Check that we have the constant available for loading CLI.
if ( defined( 'WP_CLI' ) && WP_CLI ) {

	// Load our individual commands files.
	require_once __DIR__ . '/includes/cli-command.php';

	// And add our commands.
	WP_CLI::add_command( 'member-commands', MemberCommands::class );
}
*/
