<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dao;

use bravedave\dvc\dao;

class users extends dao {

  const development = true;

  public function getActive(): array {
    // this is a stub for the dao\users::getActive method
    // it should return active users, if available
    return [];
  }

  public function validate(): bool {

    throw new \Exception('not implemented');
  }
}
