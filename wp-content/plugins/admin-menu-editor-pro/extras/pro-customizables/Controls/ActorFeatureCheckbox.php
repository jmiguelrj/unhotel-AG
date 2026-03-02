<?php

namespace YahnisElsts\AdminMenuEditor\ProCustomizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\Controls\ClassicControl;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Context;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;

class ActorFeatureCheckbox extends ClassicControl {
	protected $type = 'actorCheckbox';
	protected $koComponentName = 'ame-actor-feature-checkbox';

	protected $hasTweakActions = false;
	protected $objectId = null;

	public function __construct($settings = [], $params = [], $children = []) {
		$this->hasPrimaryInput = true;
		parent::__construct($settings, $params, $children);

		if ( array_key_exists('hasTweakActions', $params) ) {
			$this->hasTweakActions = (bool)$params['hasTweakActions'];
		}
		if ( array_key_exists('objectId', $params) ) {
			$this->objectId = $params['objectId'];
		}
	}

	public function renderContent(Renderer $renderer, Context $context) {
		//This control relies on JS to function properly and the HTML version is just a placeholder.

		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<label>';
		echo $this->buildInputElement(
			$context, [
				'type'      => 'checkbox',
				'checked'   => false,
				'class'     => 'ame-actor-feature-checkbox',
				'name'      => '', //No name, as this input is not meant to be submitted.
				'data-bind' => $this->makeKoDataBind($this->getKoEnableBinding()),
			]
		);
		echo ' [PLACEHOLDER] ', $this->getLabel($context);

		$this->outputNestedDescription();
		echo '</label>';
		//phpcs:enable
	}

	protected function getKoComponentParams(): array {
		$params = parent::getKoComponentParams();
		if ( $this->hasTweakActions ) {
			$params['hasTweakActions'] = true;
		}
		if ( $this->objectId !== null ) {
			$params['objectId'] = $this->objectId;
		}
		return $params;
	}

	public function includesOwnLabel(): bool {
		return true;
	}

	protected static function enqueueDependencies() {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;

		wp_enqueue_script('ame-actor-manager');

		parent::enqueueDependencies();
	}
}