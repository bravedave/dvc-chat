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

use dao\users as daoUsers,
  dao\dto\user as dtoUser;

abstract class users {

  protected static function developmentUser(int $id): dtoUser {
    /**
     * These are users for development
     */
    $_ = new dtoUser;
    $_->id = $id;

    $_->name = 'Local';
    if (1 == $id) $_->name = 'Billy';
    elseif (2 == $id) $_->name = 'Franco';
    elseif (3 == $id) $_->name = 'Davido';

    if (file_exists($file = self::touchPath($_))) {
      $_->access = date('c', fileatime($file));
    }

    return $_;
  }

  static function currentUser(): int {
    if (\class_exists('currentUser')) {
      return \currentUser::id();  // this is how dvc would do it

    } else {
      return 0;
    }
  }

  static function getAll(): array {
    // $path = config::dvcchat_Path();
    // $a = [];
    // $gi = new \GlobIterator($path . '/*.json', \FilesystemIterator::KEY_AS_FILENAME);
    // foreach ($gi as $key => $item) {
    //   $_ = new dao\dto\user;
    //   $_->id = (int)str_replace('.json', '', $item->getBasename());
    //   $_->access = date('c', $item->getMTime());
    //   $o = \json_decode(\file_get_contents($item->getRealPath()));
    //   $_->name = $o->name;
    //   $a[] = $_;

    //   // $modTime = filemtime( $item->getRealPath());

    // }
    // return ($a);

    if (daoUsers::development ?? null) return [
      self::developmentUser(0),
      self::developmentUser(1),
      self::developmentUser(2),
      self::developmentUser(3)
    ];


    $a = [];
    if (class_exists('dao\users')) {

      $dao = new \dao\users;
      if (method_exists($dao, 'getActive')) {

        $a = array_map(function ($dto) {
          $_ = new dao\dto\user;
          $_->id = $dto->id;
          $_->name = $dto->name;
          return $_;
        }, $dao->getActive());
      } elseif (method_exists($dao, 'getAll')) {

        if ($res = $dao->getAll()) {

          while ($dto = $res->dto()) {
            $_ = new dao\dto\user;
            $_->id = $dto->id;
            $_->name = $dto->name;
            $a[] = $_;
          }
        }
      }
    }

    return $a;
  }

  static function getUser(int $id): dao\dto\user {

    if (daoUsers::development ?? null) return self::developmentUser($id);

    $dao = new daoUsers;
    if ($dto = $dao->getByID($id)) {

      $_ = new dao\dto\user;
      $_->id = $dto->id;
      $_->name = $dto->name;

      if (file_exists($file = self::touchPath($dto))) {

        $_->access = date('Y-m-d h:i:s', filemtime($file));
      }

      return $_;
    } else {

      return self::developmentUser($id);
    }
  }

  static function touch(int $user) {
    if ($dto = self::getUser($user)) {
      $file = self::touchPath($dto);
      if (!\file_exists($file)) {
        \file_put_contents($file, \json_encode((object)['name' => $dto->name]));
      }

      touch($file);
      // \sys::logger( sprintf('<%s> %s', $dto->name, __METHOD__));

    }
  }

  protected static function touchPath(object $dto): string {
    return implode(DIRECTORY_SEPARATOR, [
      config::dvcchat_Path(),
      $dto->id . '.json'

    ]);
  }
}
