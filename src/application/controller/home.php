<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * styleguide : https://codeguide.co/
*/

class home extends controller {
  protected function _index() {
    Response::redirect( strings::url( 'chat'));

  }

}
