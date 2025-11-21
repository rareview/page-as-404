<?php
/**
 * Page As 404 service provider.
 *
 * @author Rareview <hello@rareview.com>
 *
 * @package Page As 404
 */

namespace PageAs404\Inc;

/**
 * Plugin service provider.
 */
class PageAs404ServiceProvider {

	/**
	 * The plugin features that should be bootstrapped.
	 *
	 * @var array
	 */
	public static array $services = [
		Register::class,
		Settings::class,
		PageAs404Handler::class,
		Activation::class,
	];

	/**
	 * Boot the service provider.
	 *
	 * @return void
	 */
	public function __construct() {
		foreach ( self::$services as $service ) {
			new $service();
		}
	}
}

