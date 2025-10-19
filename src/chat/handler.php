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

use bravedave\dvc\{json, logger, push, ServerRequest};
use Fiber;

final class handler {

  public static function get(ServerRequest $request): json {

    // Release the session file lock
    session_write_close();

    $action = $request('action');
    $remote = (int)$request('remote');
    $local = (int)$request('local');

    if ($local) users::touch($local);

    $version = (int)$request('version');
    $start = time();
    $fiber = new Fiber(function () use ($version) {
      $dao = new dao\dvc_chat;
      $_version = $dao->version();
      if ($_version > $version) return;
      while (true) {
        Fiber::suspend();
        $_version = $dao->version();
        if ($_version > $version) return;
      }
    });

    $started = false;
    while (true) {
      if (!$started) {
        $fiber->start();
        $started = true;
      } else {
        $fiber->resume();
      }

      if ($fiber->isTerminated()) break;

      if ((time() - $start) >= config::fibre_wait_interval) break;
      sleep(config::fibre_sleep_interval);
    }

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

    $debug = false;
    // $debug = true;

    // Release the session file lock
    session_write_close();

    $action = $request('action');
    $local = (int)$request('local');
    $version = (int)$request('version');

    if ($local) users::touch($local);

    $dao = new dao\dvc_chat;

    $start = time();
    $fiber = new Fiber(function () use ($dao, $version) {
      // initial check
      $_version = $dao->version();
      if ($_version > $version) {
        return [
          'version' => $_version,
          'unseen'  => $dao->getUnseenAll(users::currentUser())
        ];
      }
      // wait to be resumed until version changes
      while (true) {
        Fiber::suspend();
        $_version = $dao->version();
        if ($_version > $version) {
          return [
            'version' => $_version,
            'unseen'  => $dao->getUnseenAll(users::currentUser())
          ];
        }
      }
    });

    /**
     * drive the fibre, retrying (non-blocking inside the fibre) for up
     * to config::fibre_wait_interval seconds
     * resume the fibre periodically so other work could run between resumes
     */
    $result = null;
    $started = false;
    while (true) {

      if (! $started) {
        $fiber->start();
        $started = true;
      } else {
        $fiber->resume();
      }

      if ($fiber->isTerminated()) {
        $result = $fiber->getReturn();
        break;
      }

      if ((time() - $start) >= config::fibre_wait_interval) {

        // timeout - return current state
        $_version = $dao->version();
        $result = [
          'version' => $_version,
          'unseen'  => $dao->getUnseenAll(users::currentUser())
        ];
        break;
      }

      // small pause before next resume (keeps this loop lightweight)
      sleep(config::fibre_sleep_interval);
    }

    if ($debug) logger::debug(sprintf(
      '<%s %s %s> %s',
      $version,
      $version == $result['version'] ? '==' : '!=',
      $result['version'],
      logger::caller()
    ));

    return json::ack($action)
      ->add('unseen', $result['unseen'])
      ->add('version', $result['version']);
  }

  public static function getUsers(ServerRequest $request): json {

    $action = $request('action');
    return json::ack($action)
      ->add('users', users::getAll());
  }

  public static function post(ServerRequest $request): json {

    logger::info(sprintf('<%s> %s', 'got post ..', logger::caller()));


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

    logger::info(sprintf('<%s> %s', 'done post ..', logger::caller()));
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
