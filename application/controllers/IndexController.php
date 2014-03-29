<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $Ad = new Application_Model_DbTable_Ads();
        $this->view->ads = $Ad->fetchAll();
    }

    public function createformAction()
    {
        $this->view->form = new Application_Form_Ads();
        $url = $this->view->baseUrl(array('controller'=>'index', 'action' => 'create'));
        $this->view->form->setAction($url);
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
                return $this->_helper->redirector(
                        'edit', 'index', NULL, array('id' => $id
                            ));
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
        if ($obj) {
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
        $url = $this->view->Url(array('action' => 'update', 'id' => $id));
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
                return $this->_helper->redirector(
                    'edit', 'index', null, array('id' => $id)                        
                        );}
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
}













