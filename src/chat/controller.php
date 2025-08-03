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

use bravedave\dvc\{logger, Response, ServerRequest};
use bravedave\dvc\esse\modal;

class controller extends \Controller {

	protected $label = config::label;

	protected function before() {

		config::checkdatabase();
		$this->viewPath[] = __DIR__ . '/views';
		parent::before();
	}

	protected function postHandler() {

		$request = new ServerRequest;
		$action = $request('action');

		return match ($action) {
			'get' => handler::get($request),
			'get-unseen' => handler::getUnseen($request),
			'get-users' => handler::getUsers($request),
			'post' => handler::post($request),
			'seen-mark' => handler::seenMark($request),
			default => parent::postHandler()
		};
	}

	protected function _index() {
		// \dvc\pages\bootstrap::$primaryClass = 'col-sm-6 col-md-7 col-lg-8 pt-3 pb-4';
		// \dvc\pages\bootstrap::$secondaryClass = 'col-sm-6 col-md-5 col-lg-4 pt-3 pb-4 d-print-none';

		$this->data = (object)[
			'aside' => ['remotes'],
			'searchFocus' => true,
			'title' => $this->title = config::$WEBNAME,
			'title' => $this->title = $this->label,
		];

		$this->renderBS5([
			'main' => fn() => $this->load('blank')
		]);
	}

	public function chatbox($remote = 0, $local = 0) {

		if ($remote || $local) {

			// logger::info(sprintf('<%s %s> %s', $remote, $local, logger::caller()));
			$this->data = (object)[
				'local' => users::getUser((int)$local),
				'remote' => users::getUser((int)$remote)
			];

			$this->load('chat-box');
		} else {

			$this->load('chat-box-remote-invalid');
		}
	}

  public function js(string $lib = '') {

		if ( 'chatworker' == $lib) {

			Response::serve(__DIR__ . '/chat-worker.js');
		} else {

			parent::js( $lib);
		}

	}

	public function report($remote = 0) {

		if ($remote = (int)$remote) {

			$dao = new dao\dvc_chat;
			if ($user = users::getUser($remote)) {
				$this->data = (object)[
					'remote' => $user,
					'dtoSet' => $dao->dtoSet($dao->getFor(users::currentUser(), $remote)),
					'title' => $this->title = $user->name,
				];

				$this->load('chat-report');
			} else {

				modal::alertSM([
					'title' => 'Remote User Not Found',
					'text' => 'The remote user you are trying to access does not exist.',
				]);
			}
		} else {

			modal::alertSM([
				'title' => 'Remote User Not Found',
				'text' => 'The remote user you are trying to access does not exist.',
			]);
		}
	}
}
