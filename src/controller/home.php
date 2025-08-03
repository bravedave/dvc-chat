<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

class home extends Controller {

  protected function _index() {
    Response::redirect(strings::url('chat'));
  }
}
