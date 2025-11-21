<?php
/**
 * Settings class for admin settings.
 *
 * @author Rareview <hello@rareview.com>
 *
 * @package Page As 404
 */

namespace PageAs404\Inc;

/**
 * Class Settings
 */
class Settings {

	const OPTION_NAME = 'rareview_pa404_page_id';
	const PREFIX      = 'rareview-pa404';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'plugin_action_links_page-as-404/page-as-404.php', array( $this, 'add_settings_link' ) );
		add_filter( 'display_post_states', array( $this, 'display_post_states' ), 10, 2 );
	}

	/**
	 * Register settings and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'reading',
			self::OPTION_NAME,
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 0,
			)
		);

		add_settings_field(
			self::OPTION_NAME,
			__( '404 Page', 'page-as-404' ),
			array( $this, 'render_settings_field' ),
			'reading',
			'default',
			array(
				'class' => self::PREFIX . '-select',
			)
		);
	}

	/**
	 * Render the settings field.
	 *
	 * @return void
	 */
	public function render_settings_field() {
		wp_dropdown_pages(
			array(
				'name'              => self::OPTION_NAME,
				'id'                => self::OPTION_NAME,
				'show_option_none'  => esc_html__( '— Default —', 'page-as-404' ),
				'option_none_value' => '0',
				'selected'          => esc_attr( get_option( self::OPTION_NAME ) ),
			)
		);
		echo '<p class="description">' . esc_html__( 'Select a page to show for 404 errors.', 'page-as-404' ) . '</p>';
	}

	/**
	 * Add a Settings link on the plugins page.
	 *
	 * @param array $links The existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="options-reading.php?highlight=' . self::PREFIX . '-select">' . __( 'Settings', 'page-as-404' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Add a "404 Page" label in Pages list.
	 *
	 * @param array    $states Post states.
	 * @param \WP_Post $post   Post object.
	 * @return array Modified post states.
	 */
	public function display_post_states( $states, $post ) {
		if ( (int) get_option( self::OPTION_NAME ) === $post->ID ) {
			$states[ self::PREFIX ] = __( '404 Page', 'page-as-404' );
		}
		return $states;
	}

	/**
	 * Get the 404 page ID.
	 *
	 * @return int
	 */
	public static function get_page_id() {
		return (int) get_option( self::OPTION_NAME, 0 );
	}
}

