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

use bravedave\dvc\{cache, dao, dto as dvcDTO, dtoSet, logger};
use dvc\chat\config;

class dvc_chat extends dao {
	protected $_db_name = 'dvc_chat';

	public function getFor(int $remote, int $local) : array {
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

		return (new dtoSet)($sql);
	}

	public function getRecent(int $local, int $remote, int $limit = 10): array {
		$sql = sprintf(
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

		$dtoSet = (new dtoSet)($sql);
		// logger::dump($dtoSet);

		// reverse the order
		usort($dtoSet, fn($a, $b) => $a->id <=> $b->id);
		// logger::dump($dtoSet);
		return $dtoSet;
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

		return (new dtoSet)($sql);
	}

	public function Insert($a) {

		$id = parent::Insert($a);
		if (config::$DB_CACHE == 'APC') {

			$cache = cache::instance();
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
			'UPDATE `%s` SET `seen` = 1
			WHERE `local` = %d AND `remote` = %d AND `id` <= %d',
			$this->_db_name,
			$remote,
			$local,
			$version
		));
	}

	public function version() {

		if (config::$DB_CACHE == 'APC') {

			$cache = cache::instance();
			$key = $this->cacheKey(0, 'version');
			if ($v = $cache->get($key)) return ($v);
		}

		$sql = sprintf('SELECT `id` FROM `%s` ORDER BY `id` DESC LIMIT 1', $this->_db_name);
		if ($dto = (new dvcDTO)($sql)) {

			if (config::$DB_CACHE == 'APC') {

				$cache->set($key, $dto->id);
			}

			return $dto->id;
		}

		return 0;
	}
}
