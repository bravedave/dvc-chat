<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dvc\chat\dao;

use dvc\chat\sys;

$dbc = sys::dbCheck('dvc_chat');

$dbc->defineField( 'created', 'datetime');
$dbc->defineField( 'sender', 'bigint');
$dbc->defineField( 'target', 'bigint');
$dbc->defineField( 'message', 'text');

$dbc->check();
