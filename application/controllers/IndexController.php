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
        // obiekt z filtrem na slideshow
        $this->view->slideshow = $Ad->fetchAll(null,null,10);
    }
    
    public function searchAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();

        $query = '';
        $this->view->searchValue = $query;
        
        if (isset($_GET['searchValue'])) 
        {
            $searchValue = $_GET['searchValue'];
            $tmp = explode(' ', $searchValue);
            $query = '%'.implode('%', $tmp).'%';
            
            $this->view->searchValue = $searchValue;
        }
        
        if ($_GET['filter'] == 'topic')
        {   
            $search = $db->select()
                    ->from(array('a' => 'ads', array('*', 'user_id' => 'author')))
                    ->join(array('u' => 'users'), 'a.author = u.user_id')
                    ->group('a.ad_id')
                    ->order('a.ad_id ASC')
                    ->where('LOWER(topic) LIKE LOWER(?)', $query);

            $this->view->search = $db->fetchAll($search);
        }
        
        elseif ($_GET['filter'] == 'content') 
        {
            $search = $db->select()
                    ->from(array('a' => 'ads', array('*', 'user_id' => 'author')))
                    ->join(array('u' => 'users'), 'a.author = u.user_id')
                    ->group('a.ad_id')
                    ->order('a.ad_id ASC')
                    ->where('LOWER(content) LIKE LOWER(?)', $query);

            $this->view->search = $db->fetchAll($search);
        }
        
        else
        {
            $search = $db->select()
                    ->from(array('a' => 'ads', array('*', 'user_id' => 'author')))
                    ->join(array('u' => 'users'), 'a.author = u.user_id')
                    ->group('a.ad_id')
                    ->order('a.ad_id ASC')
                    ->where('LOWER(author) LIKE LOWER(?)', $query);

            $this->view->search = $db->fetchAll($search);
        }
    }

    public function createformAction()
    {
        $this->view->form = new Application_Form_Ads();
        $url = $this->view->url(array('controller' => 'index', 'action' => 'create'));
        $this->view->form->setAction($url);
        // datepicker begin
        $this->Add_Js(array('jquery-ui-1.10.4.custom.min','exp'));
        $this->AddCSS("jquery-ui-1.10.4.custom.min",'/smoothness');
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
        $this->AddJs('news');
        $this->AddCSS("news");
        $a = $this->_getAllParams();
        if(is_numeric($a['nr'])){
            $this->view->dane = $this->GetAds($a['nr']);
        }else{
            $this->view->dane = $this->GetAds(10);
        }
    }
    function GetAds($range=5){
        $r =  new Application_Model_DbTable_Ads();
        return $r->fetchAll(null, null, $range);
    }
    
    function GetAdsObject($range=5){
        return (object) $this->GetAds($range); 
    }
    function Add_CSS($css_array) {
        if(is_array($css_array)){
            foreach ($css_array as $css) {
                $this->AddCSS($css, "");
            }
        }elseif (is_string($css_array)) {
            $this->AddCSS($css_array, "");
        }
    }
    
    function AddCSS($css_name="",$sub_path="") {
        $this->_helper->layout()->getView()->headLink()->appendStylesheet($this->_helper->layout()->getView()->baseUrl("/css$sub_path/$css_name.css")); 
    }
    
    function AddJs($js_name="",$sub_path=""){
        $this->_helper->layout()->getView()->headScript()->appendFile($this->_helper->layout()->getView()->baseUrl("/js$sub_path/$js_name.js"));
    }
    
    function Add_Js($js_array) {
        if(is_array($js_array)){
            foreach ($js_array as $js) {
                $this->AddJs($js,"");
            }
        }elseif(is_string($js_array)){
            $this->AddJs($js_array,"");
        }
    }
}
