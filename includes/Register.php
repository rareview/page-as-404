<?php
/**
 * Register class for admin assets.
 *
 * @author Rareview <hello@rareview.com>
 *
 * @package Page As 404
 */

namespace PageAs404\Inc;

/**
 * Class Register
 */
class Register {

	const PREFIX = 'rareview-pa404';

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->register_admin_assets();
	}

	/**
	 * Register admin assets.
	 *
	 * @return void
	 */
	public function register_admin_assets() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue admin assets for settings page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		// Only load on the Reading settings page.
		if ( 'options-reading.php' !== $hook_suffix ) {
			return;
		}

		// Enqueue admin styles.
		wp_enqueue_style(
			self::PREFIX . '-admin-style',
			Helpers::asset_url( 'admin-styles.css' ),
			array(),
			Helpers::version()
		);

		// Enqueue admin script.
//		wp_enqueue_script(
//			self::PREFIX . '-admin-script',
//			Helpers::asset_url( 'admin.js' ),
//			array(),
//			Helpers::version(),
//			true
//		);
	}
}
