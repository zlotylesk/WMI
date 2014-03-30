<?php

class AuthController extends Zend_Controller_Action
{

    public function loginAction()
    {
        $auth = Zend_Auth::getInstance();
               
        if ($auth->hasIdentity()) {
            $this->_helper->redirector->goToSimple('index', 'index');
        }
        
        $this->view->headTitle('Zaloguj się');

        $form = new Application_Form_Login();
        $post = $this->getRequest()->getPost();
        
        if ($post) {
            if ($form->isValid($post)) {
                $adapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter(), 'users', 'username', 'password', 'SHA1(CONCAT(salt, ?))');

                $adapter->setIdentity($form->getValue('username'));
                $adapter->setCredential($form->getValue('password'));

                $result = $auth->authenticate($adapter);

                if ($result->isValid()) {
                    $data = $adapter->getResultRowObject(null, array('password', 'salt'));
                    $auth->getStorage()->write($data);
                    
                    $this->_helper->redirector->goToSimple('index', 'index');
                }
                else {
                    $form->password->addError('Login lub hasło jest nieprawidłowe');
                }
                    
            }
        }
       $this->view->form = $form; 
    }

    public function logoutAction()
    {
        $auth = Zend_Auth::getInstance();
        
        $auth->clearIdentity();
        $this->_helper->redirector->goToSimple('index', 'index');
    }


}





