<?php
/**
 * Section Block Render Callback
 */
function unhotel_render_section_block($attributes, $content) {
    $fullWidth = !empty($attributes['fullWidth']) ? 'fullwidth' : '';
    $minHeight = !empty($attributes['minHeight']) ? intval($attributes['minHeight']) : 0;
    $backgroundColor = !empty($attributes['backgroundColor']) ? $attributes['backgroundColor'] : '';
    $contentWidth = !empty($attributes['contentWidth']) ? $attributes['contentWidth'] : 'container';
    $contentWidthColor = !empty($attributes['contentWidthColor']) ? $attributes['contentWidthColor'] : '#ffffff';

    $style = '';
    if (!empty($backgroundColor)) {
        $style .= 'background-color:' . esc_attr($backgroundColor) . ';';
    }
    if ($minHeight) {
        $style .= 'min-height:' . esc_attr($minHeight) . 'px;';
    }

    $innerClass = $contentWidth === 'container' ? 'section-inner-container' : 'section-inner-full';
    $innerStyle = '';
    
    // Only apply background color if contentWidthColor is set
    if ( ! empty( $contentWidthColor ) ) {
        $innerStyle = 'background-color:' . esc_attr($contentWidthColor) . ';';
    }

    $output = '<div class="unhotel-section ' . esc_attr($fullWidth) . '"' . (!empty($style) ? ' style="' . $style . '"' : '') . '>';
    $output .= '<div class="' . esc_attr($innerClass) . '"' . ( ! empty( $innerStyle ) ? ' style="' . $innerStyle . '"' : '' ) . '>' . $content . '</div>';
    $output .= '</div>';
    return $output;
}

add_action('init', function() {
    register_block_type(__DIR__, [
        'render_callback' => 'unhotel_render_section_block',
        'attributes' => [
            'fullWidth' => [ 'type' => 'boolean', 'default' => false ],
            'minHeight' => [ 'type' => 'number', 'default' => 0 ],
            'backgroundColor' => [ 'type' => 'string', 'default' => '' ],
            'contentWidth' => [ 'type' => 'string', 'default' => 'container' ],
            'contentWidthColor' => [ 'type' => 'string', 'default' => '#ffffff' ],
        ],
    ]);
});

// Enqueue block editor assets with version
add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script(
        'unhotel-section-block',
        get_template_directory_uri() . '/blocks/section/block.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        filemtime(get_template_directory() . '/blocks/section/block.js'),
        true
    );
});
