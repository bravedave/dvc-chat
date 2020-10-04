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

use application;
use dvc\service;

class updater extends service {
  protected function _upgrade() {
		config::route_register( 'home');
		config::route_register( 'chat', 'dvc\chat\controller');
    echo( sprintf('%s : %s%s', 'updated', __METHOD__, PHP_EOL));

  }

  static function upgrade() {
    $app = new self( application::startDir());
    $app->_upgrade();

  }

}
