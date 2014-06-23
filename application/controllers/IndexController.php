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
                    ->from(array('u' => 'users'))
                    ->where('u.username = ?',substr(strrchr($auth->getIdentity(),'\\'),1));
            $o = new stdClass();
            $row = $db->fetchRow($query);
            $o->user_id = $row['user_id'];
            if (isset($_SESSION['ldap_user_name'])) {
                $o->username = $_SESSION['ldap_user_name'];
            }else{
                $o->username = $row['username'];
            }
            if(!($row['role']==='students'||$row['role']==='admin'))
                $row['role']='wmistaff';
            $o->role = $row['role'];
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
        foreach($data as $k1 => $add1){
            foreach($add1['ads'] as $k2 =>$v){
                $data[$k1]['ads'][$k2]['content']= $this->truncateHtml($v['content'], 200);
            }
        }
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
        foreach($data['ads'] as $k2 =>$v){
            $data['ads'][$k2]['content']= $this->truncateHtml($v['content'], 300);
        }
        $this->view->ads = $data;
    }

    public function showstudentadsAction()
    {
        $e_page=1;
        if($this->getRequest()->getParam('page')!=null) $e_page = $this->getRequest()->getParam('page');
        $e_ad_count=25;
        $data =  $this->getAddsArray($e_ad_count, 'students', $e_page);
        $data['pages']=ceil($data['count']/$e_ad_count);
        foreach($data['ads'] as $k2 =>$v){
            $data['ads'][$k2]['content']= $this->truncateHtml($v['content'], 300);
        }
        $this->view->ads = $data;
    }

    public function showuseradsAction()
       {
        $db = Zend_Db_Table::getDefaultAdapter();

        $auth = Zend_Auth::getInstance();
        $identity = $auth->getIdentity();
		
        if($identity)
        {
            $splitter = explode('\\', $identity);
            $user = $splitter[1];
            $users = new Application_Model_DbTable_Users();
            $row = $users->select()
                    ->from('users')
                    ->where('username = ?', $user);
            $fetch = $users->fetchRow($row);
            $ads = $db->select()
                    ->from(array('a' => 'ads'))
                    ->where('a.author = ?', $this->view->identity->user_id)
                    ->join(array('u' => 'users'), 'a.author = u.user_id')
                    ->group('a.ad_id')
                    ->order('a.datetime DESC');
            $data = $db->fetchAll($ads);
           foreach($data['ads'] as $k2 =>$v){
                     $data['ads'][$k2]['content']= $this->truncateHtml($v['content'], 300);
                }
            $this->view->ads = $data;
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
            // tinymce begin
        $this->Add_Js(array('tinymce/tinymce.min','tinymce.init'));
        $this->AddCSS("skin.min",'/lightgray');
        // tinymce end
        // datepicker begin
        $this->Add_Js(array('jquery-ui-1.10.4.custom.min','exp'));
        $this->AddCSS("jquery-ui-1.10.4.custom.min",'/smoothness');
        // datepicker end
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
                $data['exp'] = substr(''.$form->getValue('exp'),0,-8).date('H:i:s');
                $data['exp'] = $form->getValue('exp')." ".date('H:i:s');
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
               ->join(array('u' => 'users'), 'a.author = u.user_id')
			   ->where('ad_id = ?', $id);
        
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
            //$this->view->dane = $this->GetAdsFor(null,'DESC',$a['nr']);
            $this->view->dane = $this->getAddsArray($a['nr'], 'wmistaff', 1);
        }else{
            //$this->view->dane = $this->GetAdsFor(null,'DESC',10);
            $this->view->dane = $this->getAddsArray(10, 'wmistaff', 1);
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
function truncateHtml($text, $length = 100, $ending = '(...)', $exact = true, $considerHtml = true) {
	if ($considerHtml) {
		// if the plain text is shorter than the maximum length, return the whole text
		if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
			return $text;
		}
		// splits all html-tags to scanable lines
		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
		$total_length = strlen($ending);
		$open_tags = array();
		$truncate = '';
		foreach ($lines as $line_matchings) {
			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
			if (!empty($line_matchings[1])) {
				// if it's an "empty element" with or without xhtml-conform closing slash
				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					// do nothing
				// if tag is a closing tag
				} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
					// delete tag from $open_tags list
					$pos = array_search($tag_matchings[1], $open_tags);
					if ($pos !== false) {
					unset($open_tags[$pos]);
					}
				// if tag is an opening tag
				} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
					// add tag to the beginning of $open_tags list
					array_unshift($open_tags, strtolower($tag_matchings[1]));
				}
				// add html-tag to $truncate'd text
				$truncate .= $line_matchings[1];
			}
			// calculate the length of the plain text part of the line; handle entities as one character
			$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
			if ($total_length+$content_length> $length) {
				// the number of characters which are left
				$left = $length - $total_length;
				$entities_length = 0;
				// search for html entities
				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
					// calculate the real length of all entities in the legal range
					foreach ($entities[0] as $entity) {
						if ($entity[1]+1-$entities_length <= $left) {
							$left--;
							$entities_length += strlen($entity[0]);
						} else {
							// no more characters left
							break;
						}
					}
				}
				$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
				// maximum lenght is reached, so get off the loop
				break;
			} else {
				$truncate .= $line_matchings[2];
				$total_length += $content_length;
			}
			// if the maximum length is reached, get off the loop
			if($total_length>= $length) {
				break;
			}
		}
	} else {
		if (strlen($text) <= $length) {
			return $text;
		} else {
			$truncate = substr($text, 0, $length - strlen($ending));
		}
	}
	// if the words shouldn't be cut in the middle...
	if (!$exact) {
		// ...search the last occurance of a space...
		$spacepos = strrpos($truncate, ' ');
		if (isset($spacepos)) {
			// ...and cut the text in this position
			$truncate = substr($truncate, 0, $spacepos);
		}
	}
	// add the defined ending to the text
	$truncate .= $ending;
	if($considerHtml) {
		// close all unclosed html-tags
		foreach ($open_tags as $tag) {
			$truncate .= '</' . $tag . '>';
		}
	}
	return $truncate;
}
} 