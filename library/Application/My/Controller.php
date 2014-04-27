<?php

abstract class Application_My_Controller extends Zend_Controller_Action
{
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();

        if ($identity) {
            $this->view->identity = $identity;
        }
        $action = $this->getRequest()->getActionName(); $session = new Zend_Session_Namespace;
        
        if ($action) {
            $session = new Zend_Session_Namespace;
            $session->back_url = $this->view->serverUrl().$this->getRequest()->getRequestUri();
        }
    }
}