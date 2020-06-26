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

class controller extends \Controller {

	protected $label = config::label;

	protected function before() {

		config::dvcchat_checkdatabase();
		// config::route_register( 'docmgr', 'dvc\docmgr\controller');
		parent::before();

		// sys::logger( sprintf('<%s> %s', 'hear me !', __METHOD__));

	}

	protected function getView( $viewName = 'index', $controller = null, $logMissingView = true) {
		$view = sprintf( '%s/views/%s.php', __DIR__, $viewName );		// php
		if ( file_exists( $view))
			return ( $view);

		return parent::getView( $viewName, $controller, $logMissingView);

	}

	protected function posthandler() {
		$action = $this->getPost('action');

		if ( 'get' == $action) {
			$local = (int)$this->getPost( 'local');
			$remote = (int)$this->getPost( 'remote');
			$version = (int)$this->getPost( 'version');

			$dao = new dao\dvc_chat;
			$_version = $dao->version();
			// \sys::logger( sprintf('<%s> %s', $_version, __METHOD__));

			if ( $_version <= $version) {
				Json::ack( $action)
					->add('version', $_version)
					->add('unseen', $dao->getUnseen( $local))
					->add('data', []);

			}
			else {
				$chats = $dao->getRecent( $local, $remote);
				Json::ack( $action)
					->add('version', $_version)
					->add('unseen', $dao->getUnseen( $local))
					->add('data', $chats);

			}

        }
		elseif ( 'get-unseen' == $action) {
			$local = (int)$this->getPost( 'local');
			$remote = (int)$this->getPost( 'remote');

			$dao = new dao\dvc_chat;
			$_version = $dao->version();

			Json::ack( $action)
				->add('unseen', $dao->getUnseen( $local));

        }
		elseif ( 'post' == $action) {
			$local = (int)$this->getPost( 'local');
			$version = (int)$this->getPost( 'version');
			$a = [
				'created' => \db::dbTimeStamp(),
				'message' => $this->getPost( 'message'),
				'local' => $local,
				'remote' => $this->getPost( 'remote')

			];

			$dao = new dao\dvc_chat;
			$id = $dao->Insert($a);
			$dao->SeenMark( $local, $id);

            Json::ack( $action);

        }
		elseif ( 'seen-mark' == $action) {
			$local = (int)$this->getPost( 'local');
			$version = (int)$this->getPost( 'version');

			$dao = new dao\dvc_chat;
			$dao->SeenMark( $local, $version);

            Json::ack( $action);

        }
        else { Json::nak( $action); }

    }

	protected function _index() {
        $this->render([
            'title' => $this->title = $this->label,
            'primary' => 'blank',
            'secondary' => ['blank']

        ]);

    }

    function chatbox( $remote = 0, $local = 0) {
		if ( $remote || $local) {
			$this->data = (object)[
				'local' => user::getUser( (int)$local),
				'remote' => user::getUser((int)$remote)

			];

			$this->load( 'chat-box');

		}
		else {
			$this->load( 'chat-box-remote-invalid');

		}

    }

}
