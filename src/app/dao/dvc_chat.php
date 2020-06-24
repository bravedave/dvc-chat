<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\chat\dao;
use dao\_dao;

class dvc_chat extends _dao {
	protected $_db_name = 'dvc_chat';

	public function getRecent( int $limit = 10) : array {
		$_sql = sprintf( 'SELECT
				*
			FROM
				`%s`
				WHERE id IN (SELECT id FROM `%s` ORDER BY ID DESC LIMIT %d)',
			$this->_db_name,
			$this->_db_name,
			$limit);

		if ( $res = $this->Result( $_sql)) {
			return $res->dtoSet();

		}

		return [];

	}

}
