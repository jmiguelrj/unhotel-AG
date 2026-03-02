<?php

namespace JFB_Formless\DB\Models;

use Jet_Form_Builder\Db_Queries\Base_Db_Model;

class Routes extends Base_Db_Model {

	protected $gmt = 1;

	public static function table_name(): string {
		return 'dynamic_routes';
	}

	public static function schema(): array {
		return array(
			'id'                => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'form_id'           => 'bigint(20) NOT NULL',
			'action_type'       => 'tinyint(1)',
			'restricted'        => 'tinyint(1)',
			'restriction_type'  => 'tinyint(1)',
			'restriction_cap'   => 'varchar(100)',
			'restriction_roles' => 'varchar(255)',
			'log'               => 'tinyint(1)',
			self::CREATED_AT    => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
			self::UPDATED_AT    => 'TIMESTAMP',
		);
	}

	public static function schema_keys(): array {
		return array(
			'id'             => 'primary key',
			'form_id'        => 'index',
			'action_type'    => 'index',
			self::CREATED_AT => 'index',
		);
	}
}
