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
		parent::__construct( $rootPath);
		config::route_register( 'chat', 'dvc\chat\controller');

	}

	static function run( $dir = null) {
		new self( $dir ? $dir : dirname( __DIR__));

	}

}

