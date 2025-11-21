<?php
/**
 * Helpers class.
 *
 * @author Rareview <hello@rareview.com>
 *
 * @package Page As 404
 */

namespace PageAs404\Inc;

/**
 * Class Helpers
 */
class Helpers {

	/**
	 * Plugin assets manifest.
	 *
	 * @var array
	 */
	protected static $manifest;

	/**
	 * Plugin version.
	 *
	 * @return string Plugin version.
	 */
	public static function version() {
		return '1.0.0';
	}

	/**
	 * Get the name of the asset file from the generated manifest file.
	 *
	 * @param string $file Asset file to retrieve.
	 *
	 * @return string Asset name.
	 */
	public static function asset_name( $file ) {
		if ( ! static::$manifest ) {
			$directory        = plugin_dir_path( dirname( __FILE__ ) ) . 'dist';
			$manifest_path    = "{$directory}/manifest.json";
			
			if ( file_exists( $manifest_path ) ) {
				static::$manifest = json_decode( file_get_contents( $manifest_path ), true );
			} else {
				static::$manifest = array();
			}
		}

		if ( ! isset( static::$manifest[ $file ] ) ) {
			return $file;
		}

		return static::$manifest[ $file ];
	}

	/**
	 * Gets the assets url, useful for defining asset source files.
	 *
	 * @param string $file Asset file to retrieve.
	 *
	 * @return string Asset url.
	 */
	public static function asset_url( $file ) {
		return set_url_scheme( plugin_dir_url( dirname( __FILE__ ) ) . 'dist/' . self::asset_name( $file ) );
	}

	/**
	 * Get the plugin directory path.
	 *
	 * @return string Plugin directory path.
	 */
	public static function plugin_dir() {
		return plugin_dir_path( dirname( __FILE__ ) );
	}

	/**
	 * Get the plugin directory URL.
	 *
	 * @return string Plugin directory URL.
	 */
	public static function plugin_url() {
		return plugin_dir_url( dirname( __FILE__ ) );
	}
}

