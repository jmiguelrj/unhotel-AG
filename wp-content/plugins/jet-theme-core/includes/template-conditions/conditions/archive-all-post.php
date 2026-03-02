<?php
namespace Jet_Theme_Core\Template_Conditions;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Archive_All_Post extends Base {

	/**
	 * Condition slug
	 *
	 * @return string
	 */
	public function get_id() {
		return 'archive-all-post';
	}

	/**
	 * Condition label
	 *
	 * @return string
	 */
	public function get_label() {
		return __( 'All Post Archives', 'jet-theme-core' );
	}

	/**
	 * Condition group
	 *
	 * @return string
	 */
	public function get_group() {
		return 'archive';
	}

	/**
	 * @return string
	 */
	public function get_sub_group() {
		return 'post-archive';
	}

	/**
	 * @return int
	 */
	public  function get_priority() {
		return 10;
	}

	/**
	 * @return string
	 */
	public function get_body_structure() {
		return 'jet_archive';
	}

	/**
	 * @return array
	 */
	public function get_node_data() {
		return [
			'node'   => $this->get_id(),
			'parent' => 'archive-all',
			'inherit' => [ 'entire', 'archive-all' ],
			'label'  => __( 'All Archives', 'jet-theme-core' ),
			'nodeInfo'  => [
				'title' => __( 'Blog Posts', 'jet-theme-core' ),
				'desc'  => __( 'Post Type templates', 'jet-theme-core' ),
			],
			'previewLink' => $this->get_blog_url(),
		];
	}

	/**
	 * @return string
	 */
	public function get_blog_url() {

		if ( get_option('show_on_front') === 'posts' ) {
			return home_url('/');
		} else {
			$blog_page_id = get_option( 'page_for_posts' );
			$blog_page_url = get_permalink( $blog_page_id );

			if ( $blog_page_url ) {
				return esc_url( $blog_page_url );
			}
		}

		return false;
	}

	/**
	 * Condition check callback
	 *
	 * @return bool
	 */
	public function check( $args ) {
		$show_on_front = get_option('show_on_front');

		if ( 'posts' === $show_on_front && is_home() && is_front_page() ) {
			return true;
		}

		if ( 'page' === $show_on_front && is_home() && ! is_front_page() ) {
			return true;
		}

		if ( is_post_type_archive('post') ) {
			return true;
		}

		if ( is_category() || is_tag() || is_tax() ) {
			$queried_object = get_queried_object();

			if ( isset( $queried_object->taxonomy ) ) {
				$taxonomy = $queried_object->taxonomy;
				$post_types = get_taxonomy( $taxonomy )->object_type ?? [];

				if ( in_array( 'post', $post_types, true ) ) {
					return true;
				}
			}
		}

		return false;
	}

}
