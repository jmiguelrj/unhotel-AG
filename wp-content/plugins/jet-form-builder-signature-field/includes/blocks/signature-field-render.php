<?php
namespace JFB_Signature_Field\Blocks;

use Jet_Form_Builder\Blocks\Dynamic_Value;
use Jet_Form_Builder\Blocks\Render\Base;
use JFBSignatureFieldCore\JetFormBuilder\RenderBlock;
use JFB_Signature_Field\Plugin;

class Signature_Field_Render extends Base {

	use RenderBlock;

	public static $styles_rendered = false;

	public function get_name() {
		return 'signature-field';
	}

	public function render_field( $attrs_string ) {
		return sprintf(
			'<div class="jet-form-builder-signature-with-input" style="width:%4$s">%2$s%3$s<input class="jet-form-builder-signature__input" type="text" %1$s data-jfb-sync/></div>',
			$attrs_string,
			$this->get_canvas_css(),
			$this->get_canvas_html(),
			$this->get_canvas_width()
		);
	}

	public function get_canvas_html() {
		return sprintf(
			'<div class="jet-form-builder-signature"><div class="jet-form-builder-signature__canvas-container" style="height:%2$s;"><canvas class="jet-form-builder-signature__canvas" data-attrs="%3$s" style="height:%2$s"></canvas></div>%1$s</div>',
			$this->get_clear_canvas_button(),
			$this->get_canvas_height(),
			$this->get_canvas_data_attrs()
		);
	}

	public function get_canvas_data_attrs() {

		$block_attrs = $this->block_type->block_attrs;
		$data_attrs = [
			'background'   => $block_attrs['canvas_bg'],
			'pen_color'    => $block_attrs['pen_color'],
			'image_format' => $block_attrs['image_format'],
			'storage_type' => $block_attrs['storage_type'],
			'svg_allowed'  => Plugin::instance()->is_svg_upload_allowed(),
		];
		return htmlspecialchars( wp_json_encode( $data_attrs ) );
	}

	public function get_canvas_height() {
		$height = isset( $this->block_type->block_attrs['canvas_height'] ) ? absint( $this->block_type->block_attrs['canvas_height'] ) : 250;
		return $height . 'px';
	}

	public function get_canvas_width() {
		$width = isset( $this->block_type->block_attrs['canvas_width'] ) ? $this->block_type->block_attrs['canvas_width'] : '100%';
		return $width;
	}



	public function get_clear_canvas_button() {
		$label = isset( $this->block_type->block_attrs['clear_label'] ) ? $this->block_type->block_attrs['clear_label'] : esc_html__( 'Clear', 'jet-form-builder-signature-field' );

		if ( ! $label ) {
			return '';
		} else {
			return sprintf( '<a href="#" class="jet-form-builder-signature__clear">%1$s</a>', $label );
		}
	}

	public function minify_css( $string ) {
		return str_replace( [ "\r", "\n", "\t" ], '', $string );
	}

	public function get_canvas_css() {

		if ( true === self::$styles_rendered ) {
			return '';
		}

		self::$styles_rendered = true;

		return $this->minify_css( "<style>
.jet-form-builder-signature-with-input {
	position: relative;
}
input.jet-form-builder-signature__input {
	position:absolute;
	z-index: 1;
	bottom: 5px;
	right: 5px;
	left: 5px;
	margin: 0;
	padding: 0;
	width: auto;
}
.jet-form-builder-signature {
	position: relative;
	z-index: 2;
}
.jet-form-builder-signature__canvas-container {
	width: 100%;
	height: 250px;
}
.jet-form-builder-signature__canvas {
	width: 100%;
	height: 250px;
	background: #fff;
	box-sizing: border-box;
}
.jet-form-builder-signature__clear {
	position: absolute;
	right: 0;
	bottom: 0;
	padding: 5px 10px;
	z-index: 999;
	font-size: 14px;
	line-height:20px;
}
</style>" );
	}
}
