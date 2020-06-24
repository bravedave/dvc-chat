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
			$dao = new dao\dvc_chat;
			Json::ack( $action)
				->add('data', $dao->getRecent());



        }
		elseif ( 'post' == $action) {
			$a = [
				'created' => \db::dbTimeStamp(),
				'message' => $this->getPost( 'message'),
				'sender' => $this->getPost( 'sender'),
				'target' => $this->getPost( 'target')

			];

			$dao = new dao\dvc_chat;
			$dao->Insert($a);

            Json::ack( $action);

        }
        else { Json::nak( $action); }

    }

	protected function _index() {
        $this->render([
            'title' => $this->title = $this->label,
            'primary' => 'home',
            'secondary' => ['blank']

        ]);

    }

    function chatbox() {
        $this->load( 'chat-box');

    }

}
