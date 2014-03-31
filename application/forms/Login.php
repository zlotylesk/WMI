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
                        array('messages' => array('isEmpty' => 'Proszę wpisać login'))),
                        array('StringLength', true, array('min' => 3, 'max' => 20)),
                        ),
                 )
                );
        $this->username->getValidator('StringLength')->setMessages(array(
        Zend_Validate_StringLength::TOO_LONG => 'Login za długi',
        Zend_Validate_StringLength::TOO_SHORT => 'Login za krótki',
        ));
        
        //Hasło
        $this->addElement(
                'password',
                'password',
                array(
                    'label' => 'Hasło',
                    'required' => true,
                    'validators' => array(array('notEmpty', true,
                        array('messages' => array('isEmpty' => 'Proszę wpisać hasło'))),
                        array('StringLength', true, array('min' => 4, 'max' => 34)),
                        ),
                )
         );
        $this->password->getValidator('StringLength')->setMessages(array(
        Zend_Validate_StringLength::TOO_LONG => 'Hasło za długi',
        Zend_Validate_StringLength::TOO_SHORT => 'Hasło za krótki',
        ));
         
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

