<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();

        if ($identity) {
            $this->view->identity = $identity;
        }    
    }

    public function indexAction()
    {
        $Ad = new Application_Model_DbTable_Ads();
        $this->view->ads = $Ad->fetchAll();
    }

    public function createformAction()
    {
        $this->view->form = new Application_Form_Ads();
        $url = $this->view->url(array('controller' => 'index', 'action' => 'create'));
        $this->view->form->setAction($url);
        // datepicker begin
        $this->_helper->layout()->getView()->headScript()->appendFile($this->_helper->layout()->getView()->baseUrl('/js/jquery-ui-1.10.4.custom.min.js'));
        $this->_helper->layout()->getView()->headScript()->appendFile($this->_helper->layout()->getView()->baseUrl('/js/exp.js')); // datepicker dla id="exp"
        $this->_helper->layout()->getView()->headLink()->appendStylesheet($this->_helper->layout()->getView()->baseUrl('/css/smoothness/jquery-ui-1.10.4.custom.min.css'));
        // datepicker end
    }

    public function createAction()
    {
        if ($this->getRequest()->isPost()){
            $form = new Application_Form_Ads();
            if ($form->isValid($this->getRequest()->getPost()))
            {
                $data = $form->getValues();
                $Ad = new Application_Model_DbTable_Ads();
                $id = $Ad->insert($data);
                return $this->_helper->redirector('index');
            }
            $this->view->form = $form;
        }
        else {
            throw new Zend_Controller_Action_Exception('Błędny adres!', 404);
        }
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $Ad = new Application_Model_DbTable_Ads();
        $obj = $Ad->find($id)->current();
        if (!$obj) {
            throw new Zend_Controller_Action_Exception('Błędny adres!', 404);
        }
        $obj->delete();
        return $this->_helper->redirector('index');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $Ad = new Application_Model_DbTable_Ads();
        $obj = $Ad->find($id)->current();
        if (!$obj){
            throw new Zend_Controller_Action_Exception('Błędny adres', 404);
        }
        $this->view->form = new Application_Form_Ads();
        $this->view->form->populate($obj->toArray());
        $url = $this->view->url(array('action' => 'update', 'id' => $id));
        $this->view->form->setAction($url);
        $this->view->object = $obj;
        
    }

    public function updateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $Ad = new Application_Model_DbTable_Ads();
        $obj = $Ad->find($id)->current();
        if(!$obj){
            throw new Zend_Controller_Action_Exception('Błędny adres', 404);
        }
        if ($this->getRequest()->isPost()){
            $form = new Application_Form_Ads();
            if ($form->isValid($this->getRequest()->getPost())){
                $data = $form->getValues();
                $obj->setFromArray($data);
                $obj->save();
                return $this->_helper->redirector('index');
            }
            $this->view->form = $form;
        }
        else {
            throw new Zend_Controller_Action_Exception('Błędny adres', 404);
        }
    }

    public function showAction()
    {
        $id = $this->getRequest()->getParam('id');
        $Ad = new Application_Model_DbTable_Ads();
        $obj = $Ad->find($id)->current();
        if (!$obj){
            throw new Zend_Controller_Action_Exception('Błędny adres', 404);
        }
        $this->view->object = $obj;
    }
    
    public function newsAction()
    {
        $this->_helper->layout()->setLayout('news');
        $this->_helper->layout()->getView()->headLink()->offsetUnset(1);
        $this->_helper->layout()->getView()->headLink()->offsetUnset(0);
        $this->_helper->layout()->getView()->headLink()->appendStylesheet($this->_helper->layout()->getView()->baseUrl('/css/news.css')); 
        $this->view->dane = $this->GetAds(7);
        //$ah = new Application_Model_NewsHelper_AnimationSetup();
        //$this->view->style = $ah->GetAnimation($this->view->dane, 10, 1, "slide");
        $this->view->delay=11;
    }
    function GetAds($range=5){
        $r =  new Application_Model_DbTable_Ads();
        return $r->fetchAll(null, null, $range);
    }
    
    function GetAdsObject($range=5){
        return (object) $this->GetAds($range); 
    }
}
