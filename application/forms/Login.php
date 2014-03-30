<?php

class Application_Form_Login extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
   
        //Login (Nazwa użytkownika)
        $this->addElement(
                'text', 
                'username', 
                array(
                    'label' => 'Login',
                    'required' => true,
                    'filters' => array('StringTrim'),
                    'validators' => array(array('notEmpty', true,
                            array('messages' => array('isEmpty' => 'Proszę wpisać login')
                            ))),
                 )
                
        );
        
        //Hasło
        $this->addElement(
                'password',
                'password',
                array(
                    'label' => 'Hasło',
                    'required' => true,
                    'validators' => array(array('notEmpty', true,
                            array('messages' => array('isEmpty' => 'Proszę wpisać hasło')
                            ))),
                )
         );
         
         //Przycisk logowania
        $this->addElement(
                'submit', 
                'submit', 
                array(
                    'label' => 'Zaloguj się',
                    'class' => 'btn btn-primary'
                )
        );
        $this->submit->setDecorators(array('ViewHelper'));
        $this->submit->addDecorator('HtmlTag', array('tag' => 'dd', 'class' => 'first-button'));
    }


}

