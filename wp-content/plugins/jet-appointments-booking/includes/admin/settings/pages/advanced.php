<?php
namespace JET_APB\Admin\Settings;

use JET_APB\Admin\Settings\Appointment_Settings_Base as Appointment_Settings_Base;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Advanced extends Appointment_Settings_Base {

	/**
	 * Returns module slug
	 *
	 * @return void
	 */
	public function get_page_slug() {
		return 'jet-apb-advanced-settings';
	}

	/**
	 * [get_page_name description]
	 * @return [type] [description]
	 */
	public function get_page_name() {
		return esc_html__( 'Advanced', 'jet-dashboard' );
	}

	/**
	 * Return  page config object
	 *
	 * @return [type] [description]
	 */
	public function page_settings() {
		
		$settings = parent::page_settings();
		$templates_post_types = apply_filters(
			'jet-apb/settings/templates-post-types',
			[ 'wp_template', 'wp_template_part' ]
		);

		$posts = get_posts( [ 'post_type' => $templates_post_types, 'posts_per_page' => -1 ] );
		$templates = [];

		foreach ( $posts as $post ) {

			if ( ! empty( $post->post_title ) ) {
				$templates[] = [
					'value' => $post->ID,
					'label' => $post->post_title,
				];
			}

		}

		if ( ! empty( $templates ) ) {
			$templates = array_merge( [ [
				'value' => '',
				'label' => __( 'Select...', 'jet-engine' ),
			] ], $templates );
		}

		$settings->add( 'allowed_templates', $templates );

		return $settings;
	}

	/**
	 * [page_templates description]
	 * @param  array  $templates [description]
	 * @param  string $subpage   [description]
	 * @return [type]            [description]
	 */
	public function page_templates( $templates = array(), $page = false, $subpage = false ) {
		$templates['jet-apb-advanced-settings'] = JET_APB_PATH .'templates/admin/jet-apb-settings/settings-advanced.php' ;

		return $templates;
	}
}
