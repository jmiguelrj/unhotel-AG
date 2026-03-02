<?php

namespace JFB_Formless\RouteTypes\Injectors\Interfaces;

use JFB_Formless\RouteTypes\Interfaces\RouteInterface;

interface RouteTypeInjectorInterface {

	public function inject( RouteInterface $route );

}