<?php


namespace Jet_FB_HR_Select\JetFormBuilder\Blocks;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use JFB_Modules\Block_Parsers\Field_Data_Parser;
use JFB_Modules\Block_Parsers\Fields\Default_Parser;
use JFB_Modules\Block_Parsers\Interfaces\Exclude_Self_Parser;
use JFB_Modules\Block_Parsers\Interfaces\Multiple_Parsers;

class HrSelectParserNew extends Field_Data_Parser
	implements Multiple_Parsers,
	Exclude_Self_Parser {

	public function type() {
		return 'hr-select';
	}

	public function generate_parsers(): \Generator {
		$levels = $this->settings['levels'] ?? array();

		foreach ( $levels as $level ) {
			if ( empty( $level['name'] ) ) {
				continue;
			}
			$label = empty( $level['label'] ) ? $level['name'] : $level['label'];

			$parser = new Default_Parser();
			$parser->set_type( $this->get_type() . '-level' );
			$parser->set_name( $level['name'] );
			$parser->set_setting( 'label', $label );
			$parser->set_context( $this->get_context() );

			yield $parser;
		}
	}
}
