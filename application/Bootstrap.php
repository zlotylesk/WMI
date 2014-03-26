<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initView()
    {
        $this->bootstrap('FrontController');
        $front = $this->getResource('FrontController');
	
        $view = new Zend_View();
        $view->doctype('HTML5');
        $view->headMeta()->setCharset('utf-8');
        $view->headMeta()->appendName('author', 'Adam Janda, Leszek Koziatek, Kamil Kuliński, Grzegorz Konieczny');
        $view->headTitle()->setSeparator(' - ')->prepend('Tablica WMI');
        $view->headLink()->appendStylesheet($front->getBaseUrl().'css/bootstrap.css');
        $view->headLink()->appendStylesheet($front->getBaseUrl().'css/main.css');
        $view->headScript()->appendFile($front->getBaseUrl(). 'js/jquery-2.1.0.min.js');
        $view->headScript()->appendFile($front->getBaseUrl(). 'js/bootstrap.min.js');
        
        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $viewRenderer->setView($view);

        return $view;
    }
}

