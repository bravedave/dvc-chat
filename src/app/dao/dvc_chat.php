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

	public function getRecent( int $local, int $remote, int $limit = 10) : array {
		$_sql = sprintf( 'SELECT
				*
			FROM
				`%s`
				WHERE id IN (SELECT id FROM `%s` WHERE `local` IN (%d,%d) AND `remote` IN (%d,%d) ORDER BY ID DESC LIMIT %d)',
			$this->_db_name,
			$this->_db_name,
			$local,$remote,
			$local,$remote,
			$limit);

		// \sys::logSQL( $_sql);

		if ( $res = $this->Result( $_sql)) {
			return $res->dtoSet();

		}

		return [];

	}

	public function getUnseen( int $local) : int {
		$sql = sprintf(
			'SELECT
				count(`id`) c
			FROM
				`%s`
			WHERE
				(`local` = %d AND `local_seen` = 0)
				OR (`remote` = %d AND `remote_seen` = 0)',
			$this->_db_name,
			$local,
			$local

		);

		if ( $res = $this->Result( $sql)) {
			if ( $dto = $res->dto()) {
				return $dto->c;

			}

		}

		return 0;

	}

	public function Insert( $a ) {
		$id = parent::Insert( $a );
		if ( \config::$DB_CACHE == 'APC') {
			$cache = \dvc\cache::instance();
			$key = $this->cacheKey( 0, 'version');
			$cache->set( $key, $id);

		}

		return $id;

	}

	public function SeenMark( int $local, int $version) {
		$this->Q( sprintf(
			'UPDATE
				`%s`
			SET
				`local_seen` = 1
			WHERE
				`local` = %d
				AND `id` <= %d
				',
			$this->_db_name,
			$local,
			$version

		));

		$this->Q( sprintf(
			'UPDATE
				`%s`
			SET
				`remote_seen` = 1
			WHERE
				`remote` = %d
				AND `id` <= %d
				',
			$this->_db_name,
			$local,
			$version

		));

	}

	public function version() {
		if ( \config::$DB_CACHE == 'APC') {
			$cache = \dvc\cache::instance();
			$key = $this->cacheKey( 0, 'version');
			if ( $v = $cache->get( $key)) {
				return ( $v);

			}

		}

		if ( $res = $this->Result( sprintf( 'SELECT `id` FROM `%s` ORDER BY `id` DESC LIMIT 1', $this->_db_name))) {
			if ( $dto = $res->dto()) {
				if ( \config::$DB_CACHE == 'APC') {
					$cache->set( $key, $dto->id);

				}

				return $dto->id;

			}

		}

		return 0;

	}

}
