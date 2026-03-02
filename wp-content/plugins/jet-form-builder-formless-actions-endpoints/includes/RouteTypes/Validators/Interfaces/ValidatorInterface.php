<?php

namespace JFB_Formless\RouteTypes\Validators\Interfaces;

use JFB_Formless\Services\RouteMeta;

interface ValidatorInterface {

	public function is_supported( RouteMeta $route_meta ): bool;

	public function apply( RouteMeta $route_meta );

}
