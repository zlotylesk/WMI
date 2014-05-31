<?php

class AuthController extends Zend_Controller_Action //extends Application_My_Controller
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
                /*$adapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter(), 'users', 'username', 'password', 'SHA1(CONCAT(salt, ?))');
                $adapter->setIdentity($form->getValue('username'));
                $adapter->setCredential($form->getValue('password'));*/
                
                $username = $this->_request->getParam('username');
                $password = $this->_request->getParam('password');
                $config = new Zend_Config_Ini('../application/configs/config.ini','production');
                $options = $config->ldap->toArray();
                unset($options['ldap_path']);
                $adapter = new Zend_Auth_Adapter_Ldap($options, $username, $password);
                $result = $auth->authenticate($adapter);
                if ($result->isValid()) {
                $user = new Zend_Auth_Adapter_DbTable (null, 'users', 'username');
                $user->setIdentity($username);
                $users = new Application_Model_DbTable_Users();
                $account = $adapter->getAccountObject();
                $row = $users->select()
                        ->from('users')
                        ->where('username = ?', $username);
                $fetch = $users->fetchRow($row);
                if (!$fetch)
                {
                        $arr = explode('\\',$account->homedirectory);
                        $role=$arr[count($arr)-2];
                        if($role!=='students'){
                            $role='wmistaff';
                        }
                        $dane = array(
                                'username'   => $username,
                                'role'	=>	$role,
                                'displayname' => $account->displayname
                        );  
                        $obj = $users->createRow($dane);  
                        $obj->save();  		
                }
                $adapter->setIdentity($fetch['user_id']);
                $_SESSION['ldap_user_name'] = $account->displayname;
                $this->_helper->redirector->goToSimple('index', 'index');
                }
                else {
                    $form->password->addError('Login lub hasło jest nieprawidłowe');
                }
                    
            }
        }
       $this->view->form = $form; 
    }
    
    /*public function loginAction()
    {       
        $this->view->form = new Application_Form_Login();
        
        $auth = Zend_Auth::getInstance();
        
        $username = $this->_request->getParam('username');
        $password = $this->_request->getParam('password');
        
        $config = new Zend_Config_Ini('../application/configs/application.ini',
                'production');
        $log_path = $config->ldap->log_path;
        $options = $config->ldap->toArray();
        unset($options['log_path']);
        
        $adapter = new Zend_Auth_Adapter_Ldap($options, $username, $password);
        $result = $auth->authenticate($adapter);
        echo var_dump($result);
        if($log_path){
            $messages = $result->getMessages();
            $logger = new Zend_Log();
            $logger->addWriter(new Zend_Log_Writer_Stream($log_path));
            $filter = new Zend_Log_Filter_Priority(Zend_Log::DEBUG);
            $logger->addFilter($filter);
            
            foreach ($messages as $i => $message){
                if ($i-- > 1){
                    $message = str_replace("/n", "/n ", $message);
                    $logger->log("LDAP: $i: $message", Zend_Log::DEBUG);
                }
            }
        }
        
        $identity = $auth->getIdentity();
        if ($identity) {
            echo 'działa';
        }
        else {
            echo 'dupa wołowa';
        }
    }
    */
    public function logoutAction()
    {
        $auth = Zend_Auth::getInstance();
        
        $auth->clearIdentity();
        Zend_Registry::_unsetInstance();
        $this->_helper->redirector->goToSimple('index', 'index');
    }


}





