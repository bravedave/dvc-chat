<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
 * noting that a default system will have a class
 * dao\users, which will have methods
 *  => getByID( $id) to return that user
 *  => getActive() to return active users (optional)
 *  => getAll() to return all users (fallback if getActive not found)
 *
*/

namespace dvc\chat;
use sys;

abstract class users {
    protected static function development( int $id) : dao\dto\user {
        /**
         * These are users for development
         */
        $_ = new dao\dto\user;
        $_->id = $id;

        $_->name = 'Local';
        if ( 1 == $id) $_->name = 'Billy';
        elseif ( 2 == $id) $_->name = 'Franco';
        elseif ( 3 == $id) $_->name = 'Davido';

        return $_;

    }

    static function currentUser() : int {
        if ( \class_exists('currentUser')) {
            return \currentUser::id();  // this is how dvc would do it

        }
        else {
            return 0;

        }

    }

    static function getAll() : array {
        $a = [];
        if ( \class_exists('dao\users')) {
            $dao = new \dao\users;
            if ( method_exists($dao, 'getActive')) {
                if ( $res = $dao->getActive()) {
                    while ( $dto = $res->dto()) {
                        $_ = new dao\dto\user;
                        $_->id = $dto->id;
                        $_->name = $dto->name;
                        $a[] = $_;

                    }

                }
                // else it's an empty list

            }
            elseif ( method_exists($dao, 'getAll')) {
                if ( $res = $dao->getAll()) {
                    while ( $dto = $res->dto()) {
                        $_ = new dao\dto\user;
                        $_->id = $dto->id;
                        $_->name = $dto->name;
                        $a[] = $_;

                    }

                }
                // else it's an empty list

            }
            // else it's an empty list

        }
        else {

            $a[] = self::development(0);
            $a[] = self::development(1);
            $a[] = self::development(2);
            $a[] = self::development(3);

        }

        return $a;

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
                return self::development( $id);

            }

        }
        else {
            return self::development( $id);

        }

    }

}