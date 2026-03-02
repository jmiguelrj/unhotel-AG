<?php

namespace JFB_Formless\DB\Models;

use Jet_Form_Builder\Db_Queries\Base_Db_Model;
use Jet_Form_Builder\Db_Queries\Exceptions\Sql_Exception;
use JFB_Formless\DB\Constraints;

class RoutesMeta extends Base_Db_Model {

	protected $gmt = 1;

	public static function table_name(): string {
		return 'dynamic_routes_meta';
	}

	public static function schema(): array {
		return array(
			'id'             => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'route_id'       => 'bigint(20) NOT NULL',
			'route_key'      => 'varchar(255)',
			'route_value'    => 'varchar(255)',
			self::CREATED_AT => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
			self::UPDATED_AT => 'TIMESTAMP',
		);
	}

	public static function schema_keys(): array {
		return array(
			'id'          => 'primary key',
			'route_id'    => 'index',
			'route_key'   => 'index',
			'route_value' => 'index',
		);
	}

	public function foreign_relations(): array {
		return array(
			new Constraints\Routes(),
		);
	}
}
