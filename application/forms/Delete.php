<?php

class Application_Form_Delete extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        

        //Przycisk usuwania użytkownika
        $this->addElement(
                'submit', 
                'submit', 
                array(
                    'label' => 'Tak',
                    'class' => 'btn btn-primary'
                )
        );
        $this->submit->setDecorators(array('ViewHelper'));

        //Przycisk anulowania
        $this->addElement(
                'reset', 
                'cancel', 
                array(
                    'label' => 'Nie',
                    'class' => 'btn'
                    
                )
        );
        $this->cancel->setDecorators(array('ViewHelper'));
        $this->cancel->setAttrib('onclick', 'window.location =\''.$this->getView()->url(array('controller' => 'index', 'action' => 'index')).'\' ');
                 
    }


}

