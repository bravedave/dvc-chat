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

abstract class user {
    static function default( int $id) : dao\dto\user {
        $_ = new dao\dto\user;
        $_->id = $id;

        $_->name = 1 == $id ? 'Remote' : 'Local';

        return $_;

    }

    static function getUser( int $id) : dao\dto\user {
        if ( \class_exists('dao\users')) {
            $dao = new \dao\users;
            if ( $dto = $dao->getByID( $id)) {
                $_ = new dao\dto\user;
                $_->id = $dto->id;
                $_->name = $dto->name;

                return $_;

            }
            else {
                return self::default( $id);

            }

        }
        else {
            return self::default( $id);

        }

    }

}