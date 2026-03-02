<?php

namespace JFB_Formless\DB\Models;

use Jet_Form_Builder\Db_Queries\Base_Db_Model;
use JFB_Formless\DB\Constraints;

class RequestLogs extends Base_Db_Model {

	protected $gmt = 1;

	public static function table_name(): string {
		return 'request_logs';
	}

	public static function schema(): array {
		return array(
			'id'             => 'bigint(20) NOT NULL AUTO_INCREMENT',
			'route_id'       => 'bigint(20) NOT NULL',
			'body'           => 'longtext',
			'user_agent'     => 'text',
			'referrer'       => 'text',
			'ip_address'     => 'VARCHAR(15)',
			self::CREATED_AT => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
			self::UPDATED_AT => 'TIMESTAMP',
		);
	}

	public static function schema_keys(): array {
		return array(
			'id'             => 'primary key',
			'route_id'       => 'index',
			'ip_address'     => 'index',
			self::CREATED_AT => 'index',
		);
	}

	public function foreign_relations(): array {
		return array(
			new Constraints\Routes(),
		);
	}
}
