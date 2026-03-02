<?php
/**
 * CSS parser engine
 */

namespace Crocoblock\Blocks_Style;

class Style_Inserter {

	protected $class_name = '';

	protected $data = array();

	public function __construct( $class_name = '', $data = array() ) {
		$this->class_name = $class_name;
		$this->data       = $data;
	}

	/**
	 * Insert styles into the given content.
	 *
	 * 1. If the content is empty, it returns an empty string.
	 * 2. If $this->data contains not empty 'styles' key,
	 *    it returns the styles wrapped in a <style> tag and adds to the begining
	 *    of the content.
	 * 3. Also it adds $this->class_name to the content wrapper class.
	 * 4. To do this we need to check - if first tag of the content is contains
	 *    class attribute, we will add our class to it.
	 * 5. If the first tag does not contain class attribute, we create a new class attribute with
	 *    our class and add it to the first tag.
	 * 6. Now we need to check if $this->data contains not empty 'variables' key,
	 *    if so, we need to add these variables to the style attribute of first tag in the content.
	 * 7. If the first tag does not contain style attribute, we create a new style attribute
	 *    with our variables and add it to the first tag if already contain - we will append our
	 *    variables to it.
	 *
	 * @param string $content Content to insert styles into.
	 * @return string
	 */
	public function insert_styles( $content = '' ) {

		if ( empty( $content ) ) {
			return '';
		}

		$variables = '';

		if ( ! empty( $this->data['variables'] ) ) {
			$variables = trim( ' ' . $this->data['variables'] );
			$variables = sprintf( '.%1$s { %2$s }', $this->class_name, $variables );
		}

		$styles = trim( $variables . ' ' . $this->data['styles'] );

		if ( ! empty( $styles ) ) {

			if ( ! empty( $this->class_name ) ) {
				$content = $this->with_class_name( $content );
			}

			if ( ! Style_Cache::get_instance()->is_printed( $this->class_name ) ) {
				if ( ! did_action( 'wp_head' ) ) {
					// If the wp_head action isn't called yet, we will add the styles to the head.
					add_action(
						'wp_head',
						function () use ( $styles ) {
							echo wp_kses_post( $this->styles_with_tag( $styles ) );
						}
					);
				} else {
					// If the wp_head action is already called, we will add the styles to the content.
					$content = $this->styles_with_tag( $styles ) . $content;
				}
			}
		}

		return $content;
	}

	/**
	 * Add class name to the content wrapper.
	 *
	 * @param string $content Content to add class name to.
	 * @return string
	 */
	public function with_class_name( $content = '' ) {

		if ( empty( $content ) || empty( $this->class_name ) ) {
			return $content;
		}

		// Check if the first tag contains class attribute.
		if ( preg_match( '/<(\w+)([^>]*)class="([^"]*)"/', $content, $matches ) ) {
			// If it does, we will add our class to it.
			$content = str_replace(
				$matches[0],
				sprintf( '<%s%s class="%s %s"', $matches[1], $matches[2], $matches[3], $this->class_name ),
				$content
			);
		} else {
			// If it does not, we create a new class attribute with our class and add it to the first tag.
			$content = preg_replace( '/<(\w+)/', sprintf( '<$1 class="%s"', $this->class_name ), $content, 1 );
		}

		return $content;
	}

	/**
	 * Get styles wrapped into the <style> tag.
	 *
	 * @param string $css CSS styles to insert.
	 * @return string
	 */
	public function styles_with_tag( $css = '' ) {
		return '<style>' . $css . '</style>';
	}
}
