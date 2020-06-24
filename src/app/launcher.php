<?php
/**
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\chat;

class launcher extends \application {
	function __construct( $rootPath) {
		$this->defaultController = 'dvc\chat\controller';
		parent::__construct( $rootPath);

	}

	static function run( $dir = null) {
		new self( $dir ? $dir : dirname( __DIR__));

	}

}

