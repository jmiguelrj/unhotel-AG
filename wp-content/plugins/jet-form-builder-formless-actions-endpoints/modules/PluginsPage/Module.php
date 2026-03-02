<?php

namespace JFB_Formless\Modules\PluginsPage;

use JFB_Formless\Modules\Routes\Pages\Routes;

class Module {

	private $page;

	public function __construct( Routes $page ) {
		$this->page = $page;

		add_filter(
			'plugin_action_links_' . JFB_FORMLESS_PLUGIN_BASE,
			array( $this, 'plugin_action_links' )
		);
	}

	public function plugin_action_links( array $actions ): array {
		return array_merge(
			array(
				'endpoints' => sprintf(
					'<a href="%1$s"><b>%2$s</b></a>',
					$this->page->get_url(),
					__( 'Endpoints', 'jet-form-builder-formless-action-endpoints' )
				),
			),
			$actions
		);
	}


}
