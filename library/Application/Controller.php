<?php

abstract class Application_Controller extends Zend_Controller_Action
{
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();

        if ($identity) {
            $this->view->identity = $identity->username;
        }
    }
}