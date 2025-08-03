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

use bravedave\dvc\{dao, dto as dvcDTO};

class dvc_chat extends dao {
	protected $_db_name = 'dvc_chat';

	public function getFor(int $remote, int $local) {
		$sql = sprintf(
			'SELECT * FROM `%s`
      WHERE `local` IN (%d,%d)
        AND `remote` IN (%d,%d)
      ORDER BY `created` DESC',
			$this->_db_name,
			$local,
			$remote,
			$remote,
			$local

		);

		// \sys::logSQL( $sql);

		return $this->Result($sql);
	}

	public function getRecent(int $local, int $remote, int $limit = 10): array {
		$_sql = sprintf(
			'SELECT *
			FROM `%s`
			WHERE `local` IN (%d,%d)
				AND `remote` IN (%d,%d)
			ORDER BY `id` DESC
			LIMIT %d',
			$this->_db_name,
			$local,
			$remote,
			$local,
			$remote,
			$limit
		);

		$sql = sprintf('CREATE TEMPORARY TABLE _tmp AS %s', $_sql);
		// \sys::logSQL( $sql);
		$this->Q($sql);

		$_sql = 'SELECT * FROM _tmp ORDER BY id ASC';
		if ($res = $this->Result($_sql)) {
			return $res->dtoSet();
		}

		return [];
	}

	public function getUnseen(int $remote, int $local): int {
		/**
		 * local is me, so where I am remote, which ones haven't I seen
		 * .. so it's reversed ..
		 */
		$sql = sprintf(
			'SELECT
				count(`id`) count
			FROM
				`%s`
			WHERE
				`local` = %d
				AND `remote` = %d
				AND `seen` = 0',
			$this->_db_name,
			$remote,
			$local
		);

		// logger::sql( $sql);
		if ($dto = (new dvcDTO)($sql)) {

			return $dto->count ?? 0;
		}

		return 0;
	}

	public function getUnseenAll(int $local): array {
		/**
		 * local is me, so where I am remote, which ones haven't I seen
		 * .. so it's reversed ..
		 */
		$sql = sprintf(
			'SELECT
				c.local,
				count(c.`id`) count
			FROM
				`%s` c
			WHERE
				c.`remote` = %d AND c.`seen` = 0
			GROUP by `local`',
			$this->_db_name,
			$local

		);

		if ($res = $this->Result($sql)) {
			return $res->dtoSet();
		}

		return [];
	}

	public function Insert($a) {
		$id = parent::Insert($a);
		if (\config::$DB_CACHE == 'APC') {
			$cache = \dvc\cache::instance();
			$key = $this->cacheKey(0, 'version');
			$cache->set($key, $id);
		}

		return $id;
	}

	public function SeenMark(int $local, int $remote, int $version) {
		/**
		 * local is me, so where I am remote,
		 * for the local I'm viewing
		 * I've read these
		 * .. so it's reversed ..
		 */
		$this->Q(sprintf(
			'UPDATE
				`%s`
			SET
				`seen` = 1
			WHERE
				`local` = %d
				AND `remote` = %d
				AND `id` <= %d',
			$this->_db_name,
			$remote,
			$local,
			$version

		));
	}

	public function version() {
		if (\config::$DB_CACHE == 'APC') {
			$cache = \dvc\cache::instance();
			$key = $this->cacheKey(0, 'version');
			if ($v = $cache->get($key)) {
				return ($v);
			}
		}

		if ($res = $this->Result(sprintf('SELECT `id` FROM `%s` ORDER BY `id` DESC LIMIT 1', $this->_db_name))) {
			if ($dto = $res->dto()) {
				if (\config::$DB_CACHE == 'APC') {
					$cache->set($key, $dto->id);
				}

				return $dto->id;
			}
		}

		return 0;
	}
}
