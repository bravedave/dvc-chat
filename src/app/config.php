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

class config extends \config {
	const dvcchat_db_version = 0.03;

	const label = 'Chat';

	static protected $_DVCCHAT_VERSION = 0;

	static protected function dvcchat_version( $set = null) {
		$ret = self::$_DVCCHAT_VERSION;

		if ( (float)$set) {
			$config = self::dvcchat_config();

			$j = file_exists( $config) ?
				json_decode( file_get_contents( $config)):
				(object)[];

			self::$_DVCCHAT_VERSION = $j->dvcchat_version = $set;

			file_put_contents( $config, json_encode( $j, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

		}

		return $ret;

	}

	static function dvcchat_checkdatabase() {
		if ( self::dvcchat_version() < self::dvcchat_db_version) {
			config::dvcchat_version( self::dvcchat_db_version);

			$dao = new dao\dbinfo;
			$dao->dump( $verbose = false);

		}

		// sys::logger( 'bro!');

	}

	static function dvcchat_config() {
		return implode( DIRECTORY_SEPARATOR, [
            rtrim( self::dataPath(), '/ '),
            'dvcchat.json'

        ]);

	}

	static function dvcchat_keys() : object {
		$a = [
			'pubKey' => '',
			'privKey' => ''

		];

		$pubPath = implode(
			DIRECTORY_SEPARATOR, [
				self::dvcchat_KeyPath(),
				'public_key.txt'

			]

		);

		$privPath = implode(
			DIRECTORY_SEPARATOR, [
				self::dvcchat_KeyPath(),
				'private_key.txt'

			]

		);

		if ( file_exists( $privPath) && file_exists( $pubPath)) {
			$a['privKey'] = file_get_contents( $privPath);
			$a['pubKey'] = file_get_contents( $pubPath);

		}
		else {
			/**
			 * they need to be created
			 */

			if ( \class_exists( 'Minishlink\WebPush\VAPID')) {
				$keys = (object)\Minishlink\WebPush\VAPID::createVapidKeys();
				$a['privKey'] = $keys->privateKey;
				$a['pubKey'] = $keys->publicKey;

				if ( file_exists( $privPath)) @unlink( $privPath);
				if ( file_exists( $pubPath)) @unlink( $pubPath);

				file_put_contents( $privPath, $keys->privateKey);
				file_put_contents( $pubPath, $keys->publicKey);

			}

		}

		return (object)$a;

	}

	static function dvcchat_init() {
		if ( file_exists( $config = self::dvcchat_config())) {
			$j = json_decode( file_get_contents( $config));

			if ( isset( $j->dvcchat_version)) {
				self::$_DVCCHAT_VERSION = (float)$j->dvcchat_version;

			};

			if ( isset( $j->dvc_chat_refresh)) {
				self::$DVC_CHAT_REFRESH = (float)$j->dvc_chat_refresh;

			};

		}


	}

	static function dvcchat_KeyPath() {
		$path = implode( DIRECTORY_SEPARATOR, [
			self::dvcchat_Path(),
			'keys'

		]);

		if ( ! is_dir( $path)) {
			mkdir( $path);
			chmod( $path, 0777 );

		}

		return $path;

	}

	static function dvcchat_Path() {
		$path = implode( DIRECTORY_SEPARATOR, [
			rtrim( self::dataPath(), '/'),
			'chat'

		]);

		if ( ! is_dir( $path)) {
			mkdir( $path);
			chmod( $path, 0777 );

		}

		return $path;

	}

}

config::dvcchat_init();
