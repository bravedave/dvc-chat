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

use bravedave\dvc\{json, push, ServerRequest};

final class handler {

  public static function get(ServerRequest $request): json {

    $action = $request('action');
    $remote = (int)$request('remote');
    $local = (int)$request('local');

    if ($local) users::touch($local);

    $version = (int)$request('version');

    $dao = new dao\dvc_chat;
    $_version = $dao->version();
    $unseen = $dao->getUnseen($remote, $local);
    // \sys::logger( sprintf('<%s:%s => %s> %s', $remote, $local, $unseen, __METHOD__));

    if ($_version <= $version) {

      return json::ack($action, [])
        ->add('version', $_version)
        ->add('unseen', $unseen);
    } else {

      $chats = $dao->getRecent($local, $remote);
      return json::ack($action, $chats)
        ->add('version', $_version)
        ->add('unseen', $unseen);
    }
  }

  public static function getUnseen(ServerRequest $request): json {


    $action = $request('action');
    $local = (int)$request('local');

    if ($local) users::touch($local);

    $dao = new dao\dvc_chat;
    $_version = $dao->version();

    return json::ack($action)
      ->add('unseen', $dao->getUnseenAll(users::currentUser()));
  }

  public static function getUsers(ServerRequest $request): json {

    $action = $request('action');
    return json::ack($action)
      ->add('users', users::getAll());
  }

  public static function post(ServerRequest $request): json {

    $action = $request('action');
    $local = (int)$request('local');
    $remote = $request('remote');
    $version = (int)$request('version');
    $a = [
      'created' => \db::dbTimeStamp(),
      'message' => $request('message'),
      'local' => $local,
      'remote' => $remote
    ];

    $dao = new dao\dvc_chat;
    $id = $dao->Insert($a);
    $dao->SeenMark($local, $remote, $id);

    if (push::enabled()) push::send($a['message'], $remote);
    return json::ack($action);
  }

  public static function seenMark(ServerRequest $request): json {

    $action = $request('action');
    $local = (int)$request('local');
    $remote = (int)$request('remote');
    $version = (int)$request('version');

    (new dao\dvc_chat)->SeenMark($local, $remote, $version);
    return json::ack($action);
  }
}
