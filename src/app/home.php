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

namespace dvc\chat;
use strings;
use Response;

class home extends controller {
  function index() {
    Response::redirect( strings::url( 'chat'));

  }

}