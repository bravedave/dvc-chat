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

use dvc\service;

class updater extends service {
  protected function _upgrade() {
		config::route_register( 'home', 'dvc\chat\home');
		config::route_register( 'chat', 'dvc\chat\controller');
    echo( sprintf('%s : %s%s', 'updated', __METHOD__, PHP_EOL));

  }

  static function upgrade() {
    $app = new self( launcher::startDir());
    $app->_upgrade();

  }

}
