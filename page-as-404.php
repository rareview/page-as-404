<?php
/**
 * Plugin Name: Page as 404
 * Description: Use any published Page as your 404 page. Direct visits return 200, missing URLs return 404 with page content. Excluded from loops, searches, blocks, and sitemaps.
 * Version:     1.0.2
 * Author:      Rareview
 * Author URI:  https://rareview.com/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: page-as-404
 *
 * @package Page_As_404
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/Activation.php';
require_once __DIR__ . '/includes/Helpers.php';
require_once __DIR__ . '/includes/Register.php';
require_once __DIR__ . '/includes/Settings.php';
require_once __DIR__ . '/includes/PageAs404Handler.php';
require_once __DIR__ . '/includes/PageAs404ServiceProvider.php';

// Boot plugin.
new PageAs404\Inc\PageAs404ServiceProvider();

// Register activation hook.
register_activation_hook( __FILE__, array( 'PageAs404\Inc\Activation', 'activate' ) );
