<?php
namespace Jet_Popup\Render;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Action_Button_Render extends Base_Render {

	/**
	 * [$name description]
	 * @var string
	 */
	protected $name = 'action-button-render';

	/**
	 * [init description]
	 * @return [type] [description]
	 */
	public function init() {}

	/**
	 * [get_name description]
	 * @return [type] [description]
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * [render description]
	 * @return [type] [description]
	 */
	public function render() {
		$action_type = $this->get( 'action_type', 'link' );
		$button_alignment = $this->get( 'button_alignment', 'center' );
		$button_text = $this->get( 'button_text', __( 'Click Here', 'jet-popup' ) );
		$button_link = $this->get( 'button_link', '#' );
		$is_blank = $this->get( 'button_link_blank', false );
		$is_nofollow = $this->get( 'button_link_nofollow', false );
		$button_icon_html = $this->get( 'icon_html', false );
		$icon_html = '';
		$label_html = '';

		if ( ! empty( $button_icon_html ) ) {
			$icon_html = sprintf( '<div class="jet-popup-action-button__icon">%1$s</div>', $button_icon_html );
		}

		if ( ! empty( $button_text ) ) {
			$label_html = sprintf( '<div class="jet-popup-action-button__text">%1$s</div>', $button_text );
		}

		$classes = [
			'jet-popup-action-button',
			'jet-popup-action-button--' . $button_alignment,
		];

		if ( 'link' === $action_type ) {
			$button_link = ! empty( $button_link ) ? $button_link : '#';
			$blank_attr = filter_var( $is_blank, FILTER_VALIDATE_BOOLEAN ) ? ' target="_blank"' : '';
			$nofollow_attr = filter_var( $is_nofollow, FILTER_VALIDATE_BOOLEAN ) ? ' rel="nofollow"' : '';
			$instance_html = sprintf( '<a class="jet-popup-action-button__instance" href="%1$s" role="button"%2$s%3$s>%4$s%5$s</a>', $button_link, $blank_attr, $nofollow_attr, $icon_html, $label_html );
		} else {
			$instance_html = sprintf( '<div class="jet-popup-action-button__instance" role="button">%1$s%2$s</div>', $icon_html, $label_html );
		}

		echo sprintf(
			'<div class="%1$s" data-is-block="jet-popup/action-button" data-action-type="%2$s">%3$s</div>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $action_type ),
			wp_kses_post( $instance_html )
		);

	}

}
