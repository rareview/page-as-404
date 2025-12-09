<?php
/**
 * PageAs404Handler class for main plugin functionality.
 *
 * @author Rareview <hello@rareview.com>
 *
 * @package Page As 404
 */

namespace PageAs404\Inc;

/**
 * Class PageAs404Handler
 */
class PageAs404Handler {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'handle_template_redirect' ), 1 );
		add_action( 'pre_get_posts', array( $this, 'exclude_page_from_queries' ), 1 );
		add_filter( 'rest_post_query', array( $this, 'exclude_from_rest_query' ), 10, 1 );
		add_filter( 'wp_sitemaps_posts_query_args', array( $this, 'exclude_from_sitemap' ), 10, 2 );
		add_filter( 'wp_robots', array( $this, 'add_noindex_robots' ) );
	}

	/**
	 * Handle template redirect for 404 pages.
	 *
	 * @return void
	 */
	public function handle_template_redirect() {
		if ( is_admin() ) {
			return;
		}

		$page_id = Settings::get_page_id();
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
			$custom_404 = new \WP_Query(
				array(
					'page_id'     => $page_id,
					'post_type'   => 'page',
					'post_status' => 'publish',
				)
			);

			if ( $custom_404->have_posts() ) {
				$page_post = $custom_404->posts[0];

				$wp_query->is_404                  = true; // keep 404.
				$wp_query->is_page                 = true;
				$wp_query->is_singular             = true;
				$wp_query->found_posts             = 1;
				$wp_query->post_count              = 1;
				$wp_query->posts                   = array( $page_post );
				$wp_query->post                    = $page_post;
				$wp_query->queried_object          = $page_post;
				$wp_query->queried_object_id       = $page_id;
				$wp_query->is_rareview_page_as_404 = true; // flag for pre_get_posts.

				setup_postdata( $page_post );

				$template = get_page_template() ? get_page_template() : get_index_template();
				include $template;
				wp_reset_postdata();
				exit;
			}
		}
	}

	/**
	 * Exclude the 404 page from front-end loops, searches, and Page List block queries.
	 *
	 * @param \WP_Query $query The WP_Query instance.
	 * @return void
	 */
	public function exclude_page_from_queries( $query ) {
		if ( is_admin() ) {
			return;
		}

		$page_id = Settings::get_page_id();
		if ( ! $page_id ) {
			return;
		}

		// Allow direct visits.
		if ( isset( $query->query_vars['page_id'] ) && (int) $query->query_vars['page_id'] === $page_id ) {
			return;
		}

		// Skip exclusion for 404 replacement queries.
		if ( isset( $query->is_rareview_page_as_404 ) && $query->is_rareview_page_as_404 ) {
			return;
		}

		$post_type = $query->get( 'post_type' );
		if ( 'page' === $post_type || ( is_array( $post_type ) && in_array( 'page', $post_type, true ) ) || '' === $post_type ) {
			$not_in   = (array) $query->get( 'post__not_in', array() );
			$not_in[] = $page_id;
			$query->set( 'post__not_in', array_unique( $not_in ) );
		}
	}

	/**
	 * Exclude the 404 page from REST API queries (used by Page List block).
	 *
	 * @param array $args Query arguments.
	 * @return array Modified query arguments.
	 */
	public function exclude_from_rest_query( $args ) {
		$page_id = Settings::get_page_id();
		if ( ! $page_id ) {
			return $args;
		}

		if ( isset( $args['post_type'] ) && ( 'page' === $args['post_type'] || ( is_array( $args['post_type'] ) && in_array( 'page', $args['post_type'], true ) ) ) ) {
			$args['post__not_in'] = array_merge( $args['post__not_in'] ?? array(), array( $page_id ) ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
		}

		return $args;
	}

	/**
	 * Exclude the 404 page from sitemap.xml.
	 *
	 * @param array  $args      Query arguments.
	 * @param string $post_type Post type.
	 * @return array Modified query arguments.
	 */
	public function exclude_from_sitemap( $args, $post_type ) {
		if ( 'page' !== $post_type ) {
			return $args;
		}

		$page_id = Settings::get_page_id();
		if ( ! $page_id ) {
			return $args;
		}

		$args['post__not_in'] = array_merge( $args['post__not_in'] ?? array(), array( $page_id ) ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
		return $args;
	}

	/**
	 * Add a noindex robots directive for direct visits.
	 *
	 * @param array $robots Robots directives.
	 * @return array Modified robots directives.
	 */
	public function add_noindex_robots( $robots ) {
		$page_id = Settings::get_page_id();
		if ( is_page( $page_id ) ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
}
