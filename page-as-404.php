<?php
/**
 * Plugin Name: Page as 404
 * Description: Use any published Page as your 404 page. Direct visits return 200, missing URLs return 404 with page content. Excluded from loops, searches, blocks, and sitemaps.
 * Version:     1.0.0
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

// Require Composer autoloader if it exists.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
	new PageAs404\Inc\PageAs404ServiceProvider();
} else {
	add_action(
		'admin_notices',
		function () {
			?>
			<div class="notice notice-error">
				<p><?php esc_html_e( 'Page As 404: You must run "composer install" before running this plugin.', 'page-as-404' ); ?></p>
			</div>
			<?php
		}
	);
}

// Register activation hook.
register_activation_hook( __FILE__, array( 'PageAs404\Inc\Activation', 'activate' ) );
