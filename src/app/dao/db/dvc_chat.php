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

use sys;

$dbc = sys::dbCheck('dvc_chat');

$dbc->defineField( 'created', 'datetime');
$dbc->defineField( 'local', 'bigint');
$dbc->defineField( 'local_seen', 'tinyint');
$dbc->defineField( 'remote', 'bigint');
$dbc->defineField( 'remote_seen', 'tinyint');
$dbc->defineField( 'message', 'text');

$dbc->check();
