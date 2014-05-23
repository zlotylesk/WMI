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
                    'class' => 'btn btn-success bg-g2'
                )
        );
        $this->submit->setDecorators(array('ViewHelper'));

        //Przycisk anulowania
        $this->addElement(
                'reset', 
                'cancel', 
                array(
                    'label' => 'Nie',
                    'class' => 'btn btn-danger'
                    
                )
        );
        $this->cancel->setDecorators(array('ViewHelper'));
        $this->cancel->setAttrib('onclick', 'window.location =\''.$this->getView()->url(array('controller' => 'index', 'action' => 'index')).'\' ');
                 
    }


}

