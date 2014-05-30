<?php
class Application_My_Plugin_AccessControl extends Zend_Controller_Plugin_Abstract
{
    public function predispatch(Zend_Controller_Request_Abstract $request)
    {
        /* Lista kontroli dostępu */    
        $control = new Zend_Acl();        
        $control->addRole(new Zend_Acl_Role('guest'));
        $control->addRole(new Zend_Acl_Role('students'), 'guest');
        $control->addRole(new Zend_Acl_Role('wmistaff'), 'students');
        $control->addRole(new Zend_Acl_Role('admin'));
        $control->add(new Zend_Acl_Resource('admin'));
        $control->add(new Zend_Acl_Resource('auth'));
        $control->add(new Zend_Acl_Resource('error'));
        $control->add(new Zend_Acl_Resource('index'));
               
        $control->allow('students', 'auth');
        $control->allow('students', 'index', 'create');
        $control->allow('students', 'index', 'createform');
        $control->allow('students', 'index', 'edit');
        $control->allow('students', 'index', 'delete');
        $control->allow('students', 'index', 'showuserads');
        $control->allow('students', 'index', 'update');

        $control->allow('guest', 'auth', 'index');
        $control->allow('guest', 'auth', 'login');
        $control->allow('guest', 'index', 'index');
        $control->allow('guest', 'index', 'search');
        $control->allow('guest', 'index', 'show');
        $control->allow('guest', 'index', 'news');
	$control->allow('guest', 'index', 'showemployeeads');
        $control->allow('guest', 'index', 'showstudentads');
               
        $control->allow('admin');
        $control->allow('admin', 'admin');

        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            //$users = new Application_Model_DbTable_Users();
            
            //$user = $auth->getIdentity();
            //$username = $user->username;
            //$select = $users->select()
            //             ->where('username = ?', $username);
            //$data = $users->fetchRow($select);
            //$role = $data->role;
            //$rolescore = $role;
            $db = Zend_Db_Table::getDefaultAdapter();
            $query = $db->select()
                    ->from(array('u' => 'users'))
                    ->where('u.username = ?',substr(strrchr($auth->getIdentity(),'\\'),1));
            $row = $db->fetchRow($query);
            $role = $row['role'];
            $rolescore = $role;
        } 
        else {
            $rolescore = 'guest';
        }
        
        $controller = $request->controller;
        $action = $request->action;
        
        if (!$control->isAllowed($rolescore, $controller, $action)) {
            if ($rolescore == 'guest') {
                $request->setControllerName('index');
                $request->setActionName('accessrequired');
            } 
            else {
                $request->setControllerName('index');
                $request->setActionName('accessrequired');
            }
        }
    }
}
