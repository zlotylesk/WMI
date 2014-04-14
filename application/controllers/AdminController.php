<?php

class AdminController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function createaccountAction()
    {
        /*$auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {
            $this->_helper->redirector->goToSimple('index', 'index');
        }*/
        
        $this->view->headTitle('Utwórz konto');

        $form = new Application_Form_CreateAccount();
        $post = $this->getRequest()->getPost();

        if ($post) {
            if ($form->isValid($post)) {
                $post['salt'] = sha1(uniqid() + microtime());
                $post['password'] = sha1($post['salt'].$post['password']);
                
                $users = new Application_Model_DbTable_Users();
                $users->createRow()->setFromArray($post)->save();
                
                 $this -> getHelper('viewRenderer') -> setNoRender(true);
                 echo $this -> view -> render('admin/_account_created.phtml');
                 return;  
            }
        }
        $this->view->form = $form;
    }

}



