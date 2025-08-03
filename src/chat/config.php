<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\chat;

use config as baseConfig;
use RuntimeException;

class config extends baseConfig {
	const dvcchat_db_version = 1;
	const label = 'Chat';

	static function checkdatabase() {

		$dao = new dao\dbinfo(null, method_exists(__CLASS__, 'cmsStore') ? self::cmsStore() : self::dataPath());
		$dao->checkVersion('dvc_chat', self::dvcchat_db_version);
	}

	static function dvcchat_Path() {
		$basePath = rtrim(self::dataPath(), '/ ');
		$path = $basePath . DIRECTORY_SEPARATOR . 'chat';

		if (!is_dir($path)) {

			if (!mkdir($path, 0777, true) && !is_dir($path)) {

				throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
			}

			chmod($path, 0777);
		}

		return $path;
	}
}
