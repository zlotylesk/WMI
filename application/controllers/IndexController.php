<?php

class IndexController extends Zend_Controller_Action//extends Application_My_Controller
{

    public function init()
    {
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        if($auth->getIdentity()){
            $db = Zend_Db_Table::getDefaultAdapter();
            $query = $db->select()
                    ->from(array('u' => 'users', array('user_id' => 'id')))
                    ->where('u.username = ?',substr(strrchr($auth->getIdentity(),'\\'),1));
            $o = new stdClass();
            $o->user_id = $db->fetchOne($query);
            $identity = $o;
        }
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
        $e_ad_count=9;
        $u_ad_count=9;
        $data = array(
            'ep' => $this->getAddsArray($e_ad_count, 'wmistaff', $e_page,'Ogłoszenia pracowników'),
            'up' => $this->getAddsArray($u_ad_count, 'students', $u_page,'Ogłoszenia studentów')
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
        $e_page=1;
        if($this->getRequest()->getParam('page')!=null) $e_page = $this->getRequest()->getParam('page');
        $e_ad_count=25;
        $data =  $this->getAddsArray($e_ad_count, 'wmistaff', $e_page);
        $data['pages']=ceil($data['count']/$e_ad_count);
        $this->view->ads = $data;
    }

    public function showstudentadsAction()
    {
        $e_page=1;
        if($this->getRequest()->getParam('page')!=null) $e_page = $this->getRequest()->getParam('page');
        $e_ad_count=25;
        $data =  $this->getAddsArray($e_ad_count, 'students', $e_page);
        $data['pages']=ceil($data['count']/$e_ad_count);
        $this->view->ads = $data;
    }

    public function showuseradsAction()
       {
        $db = Zend_Db_Table::getDefaultAdapter();

        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
		
        if($identity)
        {
            // To nic nie robi :S 
//            $splitter = explode('\\', $identity);
//            $user = $splitter[1];
//            $users = new Application_Model_DbTable_Users();
//            $row = $users->select()
//                    ->from('users')
//                    ->where('username = ?', $user);
//            $fetch = $users->fetchRow($row);
            $ads = $db->select()
                    ->from(array('a' => 'ads'))
                    ->where('a.author = ?', $this->view->identity->user_id)
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
        // tinymce begin
        $this->Add_Js(array('tinymce/tinymce.min','tinymce.init'));
        $this->AddCSS("skin.min",'/lightgray');
        // tinymce end
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
                $data['author'] = $this->view->identity->user_id;
                $data['exp'] = substr(''.$form->getValue('exp'),0,-8).date('H:i:s');
                $data['exp'] = $form->getValue('exp')." ".date('H:i:s');
                $data['status'] = 1;
                $Ad = new Application_Model_DbTable_Ads();
                $id = $Ad->insert($data);
                //return $this->_helper->redirector('index');
                $this -> getHelper('viewRenderer') -> setNoRender(true);
                echo $this -> view -> render('index/_createSuccess.phtml');
                return;
            }
            $this->view->form = $form;
        }
        else {
            throw new Zend_Controller_Action_Exception('Błędny adres!', 404);
        }
    }

    public function deleteAction()
    {
        $users = new Application_Model_DbTable_Users();
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        $select = $users->select()
                ->where('username = ?', $this->view->identity->user_id);
            $username = $users->fetchRow($select);
        $id = $this->getRequest()->getParam('id');
        $Ad = new Application_Model_DbTable_Ads();
        $obj = $Ad->find($id)->current();
        if (!$obj) {
            throw new Zend_Controller_Action_Exception('Błędny adres!', 404);
        }
        if ($this->view->identity->user_id!= $obj->author && $username->role != 'admin') {
                return $this->_helper->redirector('accessrequired', 'index');
            }
        $post = $this->getRequest()->getPost();
        $form = new Application_Form_Delete();
        if ($post && $form->isValid($post))
        {
            $obj->delete();
            //$this->_helper->redirector->goToSimple('index', 'index');
            $this -> getHelper('viewRenderer') -> setNoRender(true);
            echo $this -> view -> render('index/_deleteSuccess.phtml');
            return;
        }
        $this->view->Delete = $form;
    }

    public function editAction()
    {
        $users = new Application_Model_DbTable_Users();
        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
        $select = $users->select()
                ->where('username = ?', $this->view->identity->user_id);
            $username = $users->fetchRow($select);
        $id = $this->getRequest()->getParam('id');
        $Ad = new Application_Model_DbTable_Ads();
        $obj = $Ad->find($id)->current();
        if (!$obj){
            throw new Zend_Controller_Action_Exception('Błędny adres', 404);
        }
        if ($this->view->identity->user_id != $obj->author && $username->role != 'admin') {
                return $this->_helper->redirector('accessrequired', 'index');
            }
        $this->view->form = new Application_Form_Ads();
        $this->view->form->populate($obj->toArray());
        $url = $this->view->url(array('action' => 'update', 'id' => $id));
        $this->view->form->setAction($url);
                $this->Add_Js(array('tinymce/tinymce.min','tinymce.init'));
                $this->AddCSS("skin.min",'/lightgray');
		$this->Add_Js(array('jquery-ui-1.10.4.custom.min','exp'));
		$this->AddCSS("jquery-ui-1.10.4.custom.min",'/smoothness');
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
                //return $this->_helper->redirector('index');
                $this -> getHelper('viewRenderer') -> setNoRender(true);
                echo $this -> view -> render('index/_editSuccess.phtml');
                return;
            }
            $this->view->form = $form;
            $this->Add_Js(array('tinymce/tinymce.min','tinymce.init'));
            $this->AddCSS("skin.min",'/lightgray');
            $this->Add_Js(array('jquery-ui-1.10.4.custom.min','exp'));
            $this->AddCSS("jquery-ui-1.10.4.custom.min",'/smoothness');
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

    public function accessrequiredAction()
    {

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

    function GetAdsFor($role="wmistaff",$order='DESC', $range=null, $page = 1,$status=1,$user_id=false) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $ads = $db->select()
                ->from(array('a' => 'ads', array('*', 'user_id' => 'author')))
                ->join(array('u' => 'users'), 'a.author = u.user_id')
                ->order("ad_id $order");
        if($range) $ads->limit ($range,($page-1)*$range);
        if($role) $ads->where ('u.role = ?', $role);
        if($status) {$ads->where('a.status = ?',$status);}
        if($user_id) $ads->where('u.user_id = ?',$user_id);
        return $db->fetchAll($ads);
    }

    function GetCount($role='wmistaff',$status=1) {
        $db = Zend_Db_Table::getDefaultAdapter();
        $str ="SELECT COUNT(*) AS count FROM ads JOIN users ON ads.author = users.user_id WHERE users.role = '$role'";
        if($status) $str.=" AND ads.status = $status";
        return $db->fetchOne($str);
    }
    
    private function getAddsArray($nr_per_page, $role, $page_number, $section_name='Ogłoszenia', $sort = 'DESC', $status=1) {
        return array(
                'section_name' => $section_name,
                'nr_per_page' => $nr_per_page,
                'ads' => $this->GetAdsFor($role,$sort,$nr_per_page,$page_number,$status),
                'page' => $page_number,
                'count' => $this->GetCount($role,$status),
            );
    }
}
