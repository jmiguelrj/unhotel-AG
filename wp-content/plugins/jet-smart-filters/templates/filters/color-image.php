<?php

if ( empty( $args ) ) {
	return;
}

$options             = $args['options'];
$display_options     = $args['display_options'];
$type                = $args['type'];
$filter_type         = ! empty( $args['behavior'] ) ? $args['behavior'] : 'checkbox';
$query_var           = $args['query_var'];
$extra_classes       = '';
$accessibility_label = $args['accessibility_label'];
$scroll_height_style = '';

if ( ! empty( $args['scroll_height'] ) ) {
	$scroll_height_style = 'style="max-height:' . absint( $args['scroll_height'] ) . 'px"';
}

if ( ! $options ) {
	return;
}

$current = $this->get_current_filter_value( $args );

?>
<div class="jet-color-image-list" <?php $this->filter_data_atts( $args ); ?>>
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	include jet_smart_filters()->get_template( 'common/filter-items-search.php' );

	if ( $scroll_height_style ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="jet-filter-items-scroll" ' . $scroll_height_style . '><div class="jet-filter-items-scroll-container">';
	}

	echo '<form class="jet-color-image-list-wrapper">';
	echo '<fieldset>';
	echo '<legend style="display:none;">' . esc_html( $accessibility_label ) . '</legend>';

	foreach ( $options as $optionKey => $optionData ) {

		$checked = '';

		extract(
			jet_smart_filters()->utils->сreate_option_data( $optionKey, $optionData ),
			EXTR_OVERWRITE
		);

		if ( $current ) {
			if ( is_array( $current ) && in_array( $value, $current ) ) {
				$checked = 'checked';
			}

			if ( ! is_array( $current ) && $value === $current ) {
				$checked = 'checked';
			}
		}

		if ( '' !== $value ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			include jet_smart_filters()->get_template( 'filters/color-image-item.php' );
		}
	}

	echo '</fieldset>';
	echo '</form>';

	if ( $scroll_height_style ) {
		echo '</div></div>';
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	include jet_smart_filters()->get_template( 'common/filter-items-moreless.php' );
	?>
</div>
