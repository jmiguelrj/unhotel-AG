<?php
namespace Jet_Engine_Layout_Switcher\Bricks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Layout_Switcher extends \Jet_Engine\Bricks_Views\Elements\Base {
	// Element properties
	public $category = 'jetengine'; // Use predefined element category 'general'
	public $name = 'jet-engine-layout-switcher'; // Make sure to prefix your elements
	public $icon = 'jet-engine-icon-layout-switcher'; // Themify icon font class
	public $css_selector = '.je-layout-switcher'; // Default CSS selector
	public $scripts = []; // Script(s) run when element is rendered on frontend or updated in builder

	public $jet_element_render = 'layout-switcher';

	// Return localised element label
	public function get_label() {
		return esc_html__( 'Layout Switcher', 'jet-engine' );
	}

	// Set builder control groups
	public function set_control_groups() {

		$this->register_jet_control_group(
			'section_general',
			[
				'title' => esc_html__( 'General', 'jet-engine' ),
				'tab'   => 'content',
			]
		);

		$this->register_jet_control_group(
			'section_group_style',
			[
				'title' => esc_html__( 'Group', 'jet-engine' ),
				'tab'   => 'content',
			]
		);

		$this->register_jet_control_group(
			'section_buttons_style',
			[
				'title' => esc_html__( 'Buttons', 'jet-engine' ),
				'tab'   => 'content',
			]
		);

	}

	// Set builder controls
	public function set_controls() {

		$this->start_jet_control_group( 'section_general' );

		$this->register_jet_control(
			'widget_id',
			[
				'label'       => esc_html__( 'Select a Listing Grid', 'jet-engine' ),
				'type'        => 'select',
				'optionsAjax' => [
					'action' => 'jet_engine_get_listings_elements_options',
				],
			]
		);

		$this->register_jet_control(
			'reload_control',
			[
				'label'  => esc_html__( 'Update Control List', 'jet-engine' ),
				'type'   => 'apply',
				'reload' => true,
			]
		);

		$repeater = new \Jet_Engine\Bricks_Views\Helpers\Repeater();

		$repeater->add_control(
			'label',
			[
				'label'          => esc_html__( 'Label', 'jet-engine' ),
				'type'           => 'text',
				'default'        => '',
				'hasDynamicData' => false,
			]
		);

		$repeater->add_control(
			'slug',
			[
				'label'          => esc_html__( 'Slug', 'jet-engine' ),
				'description'    => esc_html__( 'Should contain only Latin letters, numbers, `-` or `_` chars', 'jet-engine' ),
				'type'           => 'text',
				'default'        => '',
				'hasDynamicData' => false,
			]
		);

		$repeater->add_control(
			'icon',
			[
				'label' => esc_html__( 'Icon', 'jet-engine' ),
				'type'  => 'icon',
			]
		);

		$repeater->add_control(
			'settings_heading',
			[
				'label' => esc_html__( 'Settings', 'jet-engine' ),
				'type'  => 'separator',
			]
		);

		$repeater->add_control(
			'is_default_layout',
			[
				'label'   => esc_html__( 'Is Default Layout', 'jet-engine' ),
				'type'    => 'checkbox',
				'default' => false,
			]
		);

		$repeater->add_control(
			'lisitng_id',
			[
				'label'       => esc_html__( 'Listing', 'jet-engine' ),
				'type'        => 'select',
				'options'     => jet_engine()->listings->get_listings_for_options(),
				'inline'      => true,
				'clearable'   => true,
				'searchable'  => true,
				'pasteStyles' => false,
				'required'    => [ 'is_default_layout', '=', false ],
			]
		);

		$repeater->add_control(
			'columns',
			[
				'label'     => esc_html__( 'Columns', 'jet-engine' ),
				'type'      => 'select',
				'inline'    => true,
				'clearable' => true,
				'default'   => '',
				'options'   => [
					'' => esc_html__( 'Default', 'jet-engine' ),
					1  => 1,
					2  => 2,
					3  => 3,
					4  => 4,
					5  => 5,
					6  => 6,
					7  => 7,
					8  => 8,
					9  => 9,
					10 => 10,
					'auto' => esc_html__( 'Auto', 'jet-engine' ),
				],
				'css'      => [],
				'required' => [ 'is_default_layout', '=', false ],
			],
		);

		$repeater->add_control(
			'column_min_width',
			[
				'label'    => esc_html__( 'Column Min Width', 'jet-engine' ),
				'type'     => 'number',
				'default'  => 240,
				'min'      => 0,
				'max'      => 1600,
				'step'     => 1,
				'css'      => [],
				'required' => [
					[ 'is_default_layout', '=', false ],
					[ 'columns', '=', 'auto' ],
				],
			]
		);

		$repeater->add_control(
			'column_auto_note',
			[
				'tab'      => 'content',
				'type'     => 'info',
				'content'  => esc_html__( 'Note: The Masonry Listing combined with Auto Columns might cause unexpected results and break the layout.', 'jet-engine' ),
				'required' => [
					[ 'is_default_layout', '=', false ],
					[ 'columns', '=', 'auto' ],
				],
			]
		);

		$this->register_jet_control(
			'layouts',
			[
				'label'         => esc_html__( 'Layouts', 'jet-engine' ),
				'type'          => 'repeater',
				'titleProperty' => 'label',
				'fields'        => $repeater->get_controls(),
				'default'       => [
					[
						'label' => esc_html__( 'Grid', 'jet-engine' ),
						'slug'  => 'grid',
						'is_default_layout' => true,
					],
					[
						'label'   => esc_html__( 'List', 'jet-engine' ),
						'slug'    => 'list',
						'columns' => '1',
					],
				],
			]
		);

		$this->register_jet_control(
			'show_label',
			[
				'label'   => esc_html__( 'Show Labels', 'jet-engine' ),
				'type'    => 'checkbox',
				'default' => true,
			]
		);

		$this->end_jet_control_group();

		$this->start_jet_control_group( 'section_group_style' );

		$this->register_jet_control(
			'align',
			[
				'label' => esc_html__( 'Alignment', 'jet-engine' ),
				'type'  => 'align-items',
				'css'   => [
					[
						'selector' => '.je-layout-switcher',
						'property' => '--je-layout-switcher-align'
					],
					[
						'selector' => '.je-layout-switcher',
						'property' => '--je-layout-switcher-btn-grow',
						'value'    => 'initial',
						'required' => 'flex-start',
					],
					[
						'selector' => '.je-layout-switcher',
						'property' => '--je-layout-switcher-btn-grow',
						'value'    => 'initial',
						'required' => 'center',
					],
					[
						'selector' => '.je-layout-switcher',
						'property' => '--je-layout-switcher-btn-grow',
						'value'    => 'initial',
						'required' => 'flex-end',
					],
					[
						'selector' => '.je-layout-switcher',
						'property' => '--je-layout-switcher-btn-grow',
						'value'    => '1',
						'required' => 'stretch',
					],
				],
			]
		);

		$this->register_jet_control(
			'gap',
			[
				'label' => esc_html__( 'Gap', 'jet-engine' ),
				'type'  => 'number',
				'units' => true,
				'css'   => [
					[
						'selector' => '.je-layout-switcher__group',
						'property' => 'gap',
					],
				],
				'placeholder' => '5px',
			]
		);

		$this->end_jet_control_group();

		$this->start_jet_control_group( 'section_buttons_style' );

		$this->register_jet_control(
			'button_icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'jet-engine' ),
				'type'  => 'number',
				'units' => true,
				'css'   => [
					[
						'selector' => '.je-layout-switcher__btn-icon',
						'property' => 'font-size',
					],
				],
			]
		);

		$this->register_jet_control(
			'button_icon_spacing',
			[
				'label' => esc_html__( 'Icon Spacing', 'jet-engine' ),
				'type'  => 'number',
				'units' => true,
				'css'   => [
					[
						'selector' => '.je-layout-switcher__btn',
						'property' => 'gap',
					],
				],
			]
		);

		$this->register_jet_control(
			'button_padding',
			[
				'label' => esc_html__( 'Padding', 'jet-engine' ),
				'type'  => 'spacing',
				'css'   => [
					[
						'selector' => '.je-layout-switcher__btn',
						'property' => 'padding',
					],
				],
			]
		);

		$this->register_jet_control(
			'button_color',
			[
				'label' => esc_html__( 'Color', 'jet-engine' ),
				'type'  => 'color',
				'css'   => [
					[
						'selector' => '.je-layout-switcher__btn',
						'property' => 'color',
					],
				],
			]
		);

		$this->register_jet_control(
			'button_bg',
			[
				'label' => esc_html__( 'Background', 'jet-engine' ),
				'type'  => 'color',
				'css'   => [
					[
						'selector' => '.je-layout-switcher__btn',
						'property' => 'background-color',
					],
				],
			]
		);

		$this->register_jet_control(
			'button_border',
			[
				'label' => esc_html__( 'Border', 'jet-engine' ),
				'type'  => 'border',
				'css'   => [
					[
						'selector' => '.je-layout-switcher__btn',
						'property' => 'border',
					],
				],
			]
		);

		$this->register_jet_control(
			'button_color_active',
			[
				'label' => esc_html__( 'Active Color', 'jet-engine' ),
				'type'  => 'color',
				'css'   => [
					[
						'selector' => '.je-layout-switcher__btn--active',
						'property' => 'color',
					],
				],
			]
		);

		$this->register_jet_control(
			'button_bg_active',
			[
				'label' => esc_html__( 'Active Background', 'jet-engine' ),
				'type'  => 'color',
				'css'   => [
					[
						'selector' => '.je-layout-switcher__btn--active',
						'property' => 'background-color',
					],
				],
			]
		);

		$this->register_jet_control(
			'button_border_color_active',
			[
				'label' => esc_html__( 'Active Border Color', 'jet-engine' ),
				'type'  => 'color',
				'css'   => [
					[
						'selector' => '.je-layout-switcher__btn--active',
						'property' => 'border-color',
					],
				],
			]
		);

		$this->end_jet_control_group();
	}

	public function set_controls_before() {
		parent::set_controls_before();
		$this->controls['_typography']['css'][0]['selector'] = '.je-layout-switcher__btn';
	}

	// Enqueue element styles and scripts
	public function enqueue_scripts() {}

	public function parse_jet_render_attributes( $attrs = [] ) {
		$attrs['view'] = 'bricks';
		$attrs['show_label'] = $attrs['show_label'] ?? false;
		return $attrs;
	}

	// Render element HTML
	public function render() {

		// STEP: Listing Grid field is empty: Show placeholder text
		if ( empty( $this->get_jet_settings( 'widget_id' ) ) ) {
			$this->render_element_placeholder(
				[
					'title' => esc_html__( 'Please select the Listing Grid to show the Layout Switcher.', 'jet-engine' )
				]
			);
			return;
		}

		//$this->enqueue_scripts();

		$render = $this->get_jet_render_instance();

		// STEP: Layout Switcher renderer class not found: Show placeholder text
		if ( ! $render ) {
			$this->render_element_placeholder(
				[
					'title' => esc_html__( 'Layout Switcher renderer class not found', 'jet-engine' )
				]
			);
			return;
		}

		echo "<div {$this->render_attributes( '_root' )}>";
		$render->render_content();
		echo "</div>";
	}

	public function css_selector( $mod = null ) {
		return sprintf( '%1$s%2$s', $this->css_selector, $mod );
	}
}