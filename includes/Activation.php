<?php
/**
 * Activation class for plugin activation.
 *
 * @author Rareview <hello@rareview.com>
 *
 * @package Page As 404
 */

namespace PageAs404\Inc;

/**
 * Class Activation
 */
class Activation {

	const TRANSIENT_NAME = 'rareview_pa404_activation_notice';
	const PREFIX         = 'rareview-pa404';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'display_activation_notice' ) );
	}

	/**
	 * Set a transient on plugin activation to show an admin notice.
	 *
	 * @return void
	 */
	public static function activate() {
		set_transient( self::TRANSIENT_NAME, true, 60 );
	}

	/**
	 * Display the admin notice after activation.
	 *
	 * @return void
	 */
	public function display_activation_notice() {
		if ( get_transient( self::TRANSIENT_NAME ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					printf(
						/* translators: %s: URL to the Reading settings page */
						wp_kses_post( __( '<strong>Page As 404</strong> is active. Please go to <a href="%s">Settings &rarr; Reading</a> to select your 404 page.', 'page-as-404' ) ),
						esc_url( admin_url( 'options-reading.php?highlight=' . self::PREFIX . '-select' ) )
					);
					?>
				</p>
			</div>
			<?php
			delete_transient( self::TRANSIENT_NAME );
		}
	}
}

