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
        $this->view->slideshow = $Ad->fetchAll(null,'ad_id DESC',10);
        
        $e_page=1;
        if($this->getRequest()->getParam('ep')!=null) $e_page = $this->getRequest()->getParam('ep');
        $u_page = 1; 
        if($this->getRequest()->getParam('up')!=null) $u_page = $this->getRequest()->getParam('up');
        $e_ad_count=4;
        $u_ad_count=4;
        $data = array(
            'ep' => array(
                'section_name' => 'Ogłoszenia pracowników',
                'nr_per_page' => $e_ad_count,
                'ads' => $this->GetAdsFor('employee','DESC',$e_ad_count,$e_page),
                'page' => $e_page,
                'count' => $this->GetCount(),
                
            ),
            'up' => array(
                'section_name' => 'Ogłoszenia studentów',
                'nr_per_page' => $u_ad_count,
                'ads' => $this->GetAdsFor('user','DESC',$u_ad_count,$u_page),
                'page' => $u_page,
                'count' => $this->GetCount('user'),
            )
        );
        $data['ep']['pages']=ceil($data['ep']['count']/$e_ad_count);
        $data['up']['pages']=ceil($data['up']['count']/$u_ad_count);
        $this->view->ad_sections = $data;
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
                    ->where('LOWER(u.username) LIKE LOWER(?)', $query);

            $this->view->search = $db->fetchAll($search);
        }
    }
    
    public function showemployeeadsAction()
    {
//        $db = Zend_Db_Table::getDefaultAdapter();
//        
//        $employee = 'employee';
//        $ads = $db->select()
//                ->from(array('a' => 'ads', array('*', 'user_id' => 'author')))
//                ->join(array('u' => 'users'), 'a.author = u.user_id')
//                ->where('u.role = ?', $employee);
//            
//        $this->view->ads = $db->fetchAll($ads);
        $this->view->ads = $this->GetAdsFor();
    }
    
    public function showstudentadsAction()
    {
//        $db = Zend_Db_Table::getDefaultAdapter();
//        
//        $student = 'user';
//        $ads = $db->select()
//                ->from(array('a' => 'ads', array('*', 'user_id' => 'author')))
//                ->join(array('u' => 'users'), 'a.author = u.user_id')
//                ->where('u.role = ?', $student);
//            
//        $this->view->ads = $db->fetchAll($ads);
        $this->view->ads = $this->GetAdsFor('user');
    }
    
    public function showuseradsAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        
        if($identity)
        {      
            $ads = $db->select()
                    ->from(array('a' => 'ads'))
                    ->where('a.author = ?', $identity->user_id)
                    ->join(array('u' => 'users'), 'a.author = u.user_id')
                    ->group('a.ad_id')
                    ->order('a.datetime DESC'); 
           
            $this->view->ads = $db->fetchAll($ads);
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
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        
        if ($this->getRequest()->isPost()){
            $form = new Application_Form_Ads();
            if ($form->isValid($this->getRequest()->getPost()))
            {
                $data = $form->getValues();
                $data['author'] = $identity->user_id;
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
        $db = Zend_Db_Table::getDefaultAdapter();
        
        $author = $db->select()
               ->from(array('a' => 'ads', array('*', 'user_id' => 'author')))
               ->join(array('u' => 'users'), 'a.author = u.user_id');
        
        $obj = $Ad->find($id)->current();
        if (!$obj){
            throw new Zend_Controller_Action_Exception('Błędny adres', 404);
        }
        $this->view->object = $obj;
        $this->view->author = $db->fetchRow($author);
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
            $this->view->dane = $this->GetAdsFor(null,'DESC',$a['nr']);
        }else{
            $this->view->dane = $this->GetAdsFor(null,'DESC',10);
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
    
    function GetAdsFor($role="employee",$order='DESC', $range=null, $page = 1) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $ads = $db->select()
                ->from(array('a' => 'ads', array('*', 'user_id' => 'author')))
                ->join(array('u' => 'users'), 'a.author = u.user_id')
                ->order("ad_id $order");
        if($range) $ads->limit ($range,($page-1)*$range);
        if($role) $ads->where ('u.role = ?', $role);
        return $db->fetchAll($ads);
    }
    
    function GetCount($role='employee') {
        $db = Zend_Db_Table::getDefaultAdapter();
        return $db->fetchOne("SELECT COUNT(*) AS count FROM ads JOIN users ON ads.author = users.user_id WHERE users.role = '$role'");
    }
}
