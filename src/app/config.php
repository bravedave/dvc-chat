<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

class config extends bravedave\dvc\config {

  static function cmsStore() {

    return self::dataPath();
  }
}
