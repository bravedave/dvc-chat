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
use Json;
use strings;

class controller extends \Controller {

	protected $label = config::label;
	protected $viewPath = __DIR__ . '/views';

	protected function before() {
		config::dvcchat_checkdatabase();
		parent::before();

	}

	protected function postHandler() {
		$action = $this->getPost('action');

		if ( 'get' == $action) {
			$remote = (int)$this->getPost( 'remote');
			$local = (int)$this->getPost( 'local');

			if ( $local) users::touch( $local);

			$version = (int)$this->getPost( 'version');

			$dao = new dao\dvc_chat;
			$_version = $dao->version();
			$unseen = $dao->getUnseen( $remote, $local);
			// \sys::logger( sprintf('<%s:%s => %s> %s', $remote, $local, $unseen, __METHOD__));

			if ( $_version <= $version) {
				Json::ack( $action)
					->add('version', $_version)
					->add('unseen', $unseen)
					->add('data', []);

			}
			else {
				$chats = $dao->getRecent( $local, $remote);
				Json::ack( $action)
					->add('version', $_version)
					->add('unseen', $unseen)
					->add('data', $chats);

			}

		}
		elseif ( 'get-unseen' == $action) {
			$local = (int)$this->getPost( 'local');

			if ( $local) users::touch( $local);

			$dao = new dao\dvc_chat;
			$_version = $dao->version();

			Json::ack( $action)
				->add('unseen', $dao->getUnseenAll( users::currentUser()));

		}
		elseif ( 'get-users' == $action) {
			Json::ack( $action)
				->add('users', users::getAll());

		}
		elseif ( 'post' == $action) {
			$local = (int)$this->getPost( 'local');
			$remote = $this->getPost( 'remote');
			$version = (int)$this->getPost( 'version');
			$a = [
				'created' => \db::dbTimeStamp(),
				'message' => $this->getPost( 'message'),
				'local' => $local,
				'remote' => $remote

			];

			$dao = new dao\dvc_chat;
			$id = $dao->Insert($a);
			$dao->SeenMark( $local, $remote, $id);

			Json::ack( $action);

		}
		elseif ( 'seen-mark' == $action) {
			$local = (int)$this->getPost( 'local');
			$remote = (int)$this->getPost( 'remote');
			$version = (int)$this->getPost( 'version');

			$dao = new dao\dvc_chat;
			$dao->SeenMark( $local, $remote, $version);

			Json::ack( $action);

		}
		else {
			parent::postHandler();

		}

	}

	protected function _index() {
		\dvc\pages\bootstrap::$primaryClass = 'col-sm-6 col-md-7 col-lg-8 pt-3 pb-4';
		\dvc\pages\bootstrap::$secondaryClass = 'col-sm-6 col-md-5 col-lg-4 pt-3 pb-4 d-print-none';

		$this->render([
      'title' => $this->title = $this->label,
      'primary' => 'blank',
      'secondary' => ['remotes']

		]);

	}

	public function chatbox( $remote = 0, $local = 0) {
		if ( $remote || $local) {
			$this->data = (object)[
				'local' => users::getUser( (int)$local),
				'remote' => users::getUser((int)$remote)

			];

			$this->load( 'chat-box');

		}
		else {
			$this->load( 'chat-box-remote-invalid');

		}

	}

}
