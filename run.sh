#!/bin/sh
PORT=13380

WD=`pwd`

cd www
echo "this application is available at http://localhost:$PORT"
php -S localhost:$PORT _mvp.php
cd $WD
