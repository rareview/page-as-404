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

/**
 * Set a transient on plugin activation to show an admin notice.
 */
function pa404_activate() {
	set_transient( 'pa404_activation_notice', true, 60 );
}
register_activation_hook( __FILE__, 'pa404_activate' );

/**
 * Display the admin notice after activation.
 */
function pa404_display_activation_notice() {
	if ( get_transient( 'pa404_activation_notice' ) ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %s: URL to the Reading settings page */
					wp_kses_post( __( '<strong>Page As 404</strong> is active. Please go to <a href="%s">Settings &rarr; Reading</a> to select your 404 page.', 'page-as-404' ) ),
					esc_url( admin_url( 'options-reading.php?highlight=page-as-404-select' ) )
				);
				?>
			</p>
		</div>
		<?php
		delete_transient( 'pa404_activation_notice' );
	}
}
add_action( 'admin_notices', 'pa404_display_activation_notice' );

/**
 * Register setting in Settings → Reading.
 */
add_action(
	'admin_init',
	function () {
		register_setting(
			'reading',
			'page_as_404_id',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 0,
			)
		);

		add_settings_field(
			'page_as_404_id',
			__( '404 Page', 'page-as-404' ),
			function () {
				wp_dropdown_pages(
					array(
						'name'              => 'page_as_404_id',
						'show_option_none'  => esc_html__( '— Default —', 'page-as-404' ),
						'option_none_value' => '0',
						'selected'          => esc_attr( get_option( 'page_as_404_id' ) ),
					)
				);
				echo '<p class="description">' . esc_html__( 'Select a page to show for 404 errors.', 'page-as-404' ) . '</p>';
			},
			'reading',
			'default',
			array(
				'class' => 'page-as-404-select',
			)
		);
	}
);

/**
 * Handle both direct visits and 404 replacements.
 */
add_action(
	'template_redirect',
	function () {
		if ( is_admin() ) {
			return;
		}

		$page_id = (int) get_option( 'page_as_404_id' );
		if ( ! $page_id ) {
			return;
		}

		global $wp_query;

		// Normalize requested path.
		$request_uri    = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$requested_path = $request_uri ? trim( wp_parse_url( $request_uri, PHP_URL_PATH ), '/' ) : '';
		$page_slug      = get_post_field( 'post_name', $page_id );

		// Case 1: Direct visit to the 404 page → serve 200.
		if ( $requested_path === $page_slug ) {
			$page_post = get_post( $page_id );
			if ( ! $page_post ) {
				return;
			}

			$wp_query->is_404            = false;
			$wp_query->is_page           = true;
			$wp_query->is_singular       = true;
			$wp_query->found_posts       = 1;
			$wp_query->post_count        = 1;
			$wp_query->posts             = array( $page_post );
			$wp_query->post              = $page_post;
			$wp_query->queried_object    = $page_post;
			$wp_query->queried_object_id = $page_id;

			setup_postdata( $page_post );
			status_header( 200 );
			return;
		}

		// Case 2: Real 404 → replace query with chosen page but keep 404 status.
		if ( $wp_query->is_404() ) {
			$custom_404 = new WP_Query(
				array(
					'page_id'     => $page_id,
					'post_type'   => 'page',
					'post_status' => 'publish',
				)
			);

			if ( $custom_404->have_posts() ) {
				$page_post = $custom_404->posts[0];

				$wp_query->is_404            = true; // keep 404.
				$wp_query->is_page           = true;
				$wp_query->is_singular       = true;
				$wp_query->found_posts       = 1;
				$wp_query->post_count        = 1;
				$wp_query->posts             = array( $page_post );
				$wp_query->post              = $page_post;
				$wp_query->queried_object    = $page_post;
				$wp_query->queried_object_id = $page_id;
				$wp_query->is_page_as_404    = true; // flag for pre_get_posts.

				setup_postdata( $page_post );

				$template = get_page_template() ? get_page_template() : get_index_template();
				include $template;
				wp_reset_postdata();
				exit;
			}
		}
	},
	1
);

/**
 * Exclude the 404 page from front-end loops, searches, and Page List block queries.
 *
 * @param WP_Query $query The WP_Query instance.
 */
function pa404_exclude_page_from_queries( $query ) {
	if ( is_admin() ) {
		return;
	}

	$page_id = (int) get_option( 'page_as_404_id' );
	if ( ! $page_id ) {
		return;
	}

	// Allow direct visits.
	if ( isset( $query->query_vars['page_id'] ) && (int) $query->query_vars['page_id'] === $page_id ) {
		return;
	}

	// Skip exclusion for 404 replacement queries.
	if ( isset( $query->is_page_as_404 ) && $query->is_page_as_404 ) {
		return;
	}

	$post_type = $query->get( 'post_type' );
	if ( 'page' === $post_type || ( is_array( $post_type ) && in_array( 'page', $post_type, true ) ) || '' === $post_type ) {
		$not_in   = (array) $query->get( 'post__not_in', array() );
		$not_in[] = $page_id;
		$query->set( 'post__not_in', array_unique( $not_in ) );
	}
}
add_action( 'pre_get_posts', 'pa404_exclude_page_from_queries', 1 );

/**
 * Exclude the 404 page from REST API queries (used by Page List block).
 */
add_filter(
	'rest_post_query',
	function ( $args ) {
		$page_id = (int) get_option( 'page_as_404_id' );
		if ( ! $page_id ) {
			return $args;
		}

		if ( isset( $args['post_type'] ) && ( 'page' === $args['post_type'] || ( is_array( $args['post_type'] ) && in_array( 'page', $args['post_type'], true ) ) ) ) {
			$args['post__not_in'] = array_merge( $args['post__not_in'] ?? array(), array( $page_id ) ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
		}

		return $args;
	},
	10,
	1
);

/**
 * Exclude the 404 page from sitemap.xml.
 */
add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'page' !== $post_type ) {
			return $args;
		}

		$page_id = (int) get_option( 'page_as_404_id' );
		if ( ! $page_id ) {
			return $args;
		}

		$args['post__not_in'] = array_merge( $args['post__not_in'] ?? array(), array( $page_id ) ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
		return $args;
	},
	10,
	2
);

/**
 * Add a “404 Page” label in Pages list.
 */
add_filter(
	'display_post_states',
	function ( $states, $post ) {
		if ( (int) get_option( 'page_as_404_id' ) === $post->ID ) {
			$states['page_as_404'] = __( '404 Page', 'page-as-404' );
		}
		return $states;
	},
	10,
	2
);

/**
 * Add a noindex robots directive for direct visits.
 */
add_filter(
	'wp_robots',
	function ( $robots ) {
		$page_id = (int) get_option( 'page_as_404_id' );
		if ( is_page( $page_id ) ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
);

/**
 * Add highlight effect for settings link.
 */
function pa404_admin_assets() {
	$screen = get_current_screen();
	if ( ! $screen || 'options-reading' !== $screen->id ) {
		return;
	}
	?>
	<style>
		@keyframes pa404-glow-and-fade {
			0% {
				box-shadow: 0 0 0 0 rgba(0, 124, 186, 0.7);
			}
			50% {
				box-shadow: 0 0 20px 10px rgba(0, 124, 186, 0.3);
			}
			100% {
				box-shadow: 0 0 0 0 rgba(0, 124, 186, 0);
			}
		}
		select#page_as_404_id.highlight-setting {
			animation: pa404-glow-and-fade 2s ease-out;
			border-radius: 3px;
		}
	</style>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const params = new URLSearchParams(window.location.search);
			if (params.get('highlight') === 'page-as-404-select') {
				const element = document.getElementById('page_as_404_id');
				if (element) {
					element.classList.add('highlight-setting');
					setTimeout(() => {
						element.classList.remove('highlight-setting');
						const url = new URL(window.location);
						url.searchParams.delete('highlight');
						window.history.replaceState({}, document.title, url);
					}, 2000);
				}
			}
		});
	</script>
	<?php
}
add_action( 'admin_footer', 'pa404_admin_assets' );

/**
 * Add a Settings link on the plugins page.
 *
 * @param array $links The existing plugin action links.
 */
function pa404_add_settings_link( $links ) {
	$settings_link = '<a href="options-reading.php?highlight=page-as-404-select">' . __( 'Settings', 'page-as-404' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'pa404_add_settings_link' );
