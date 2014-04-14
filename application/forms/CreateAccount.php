<?php

class Application_Form_CreateAccount extends Zend_Form
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
                    'validators' => array(
                            array('Db_RecordExists', true),
                            array('NotEmpty', true),
                            array('stringLength', true,
                            array('min'=>3, 'max'=>12),
                            array ('Regex', true, 
                            array ('pattern' => '/^[a-zA-Z0-9_\-]+$/', 
                            'messages'=>array('regexNotMatch' => 'Użyto niedozwolone znaki')
                            )),
                     ),
                 )
        ));
        $this->username->getValidator('Db_RecordExists')->setMessages(array(
        Zend_Validate_Db_RecordExists::ERROR_RECORD_FOUND => 'Login jest już zajęty'
        ));
        $this->username->getValidator('NotEmpty')->setMessages(array(
        Zend_Validate_NotEmpty::IS_EMPTY => 'musisz podać login',
        Zend_Validate_NotEmpty::INVALID => 'proszę wpisać login'
        ));
        $this->username->getValidator('stringLength')->setMessages(array(
        Zend_Validate_StringLength::TOO_SHORT => 'proszę podać dłuższy login',
        Zend_Validate_StringLength::TOO_LONG => 'login jest za długi'
        ));
        
        
        //Hasło
        $this->addElement(
                'password',
                'password',
                array(
                    'label' => 'Hasło',
                    'required' => true,    
                    'validators' => array(
                        array('notEmpty', true,
                        array('messages' => array('isEmpty'=>'Proszę wpisać hasło i je potwierdzić')
                        )),
                        array('stringLength', true, 
                        array('min' => 3, 'max' => 20, 'messages'=>
                        array('stringLengthTooShort' => 'Hasło musi składać się z co najmniej 3 znaków',
                              'stringLengthTooLong' => 'Hasło musi składać się z maksymalnie 20 znaków',
                        )
                        )),
                     ),
                )
         );
         
         //Powtórz hasło
         $this->addElement(
                 'password', 
                 'passwordconfirm', 
                 array(
                     'label' => 'Powtórz hasło',
                     'required' => true,
                     'validators' => array(
                         array('notEmpty', true, 
                         array('messages' => array('isEmpty' => 'Proszę wpisać hasło i je potwierdzić')
                        )),
                        array('identical', true, 
                        array('token'=>'password', 'messages' => 
                        array('notSame' => 'Hasła muszą być takie same')
                        )),
                     ),
                 )
        );
        
        //E-mail
        $this->addElement(
                'text', 
                'email', 
                array(
                    'label' => 'E-mail',
                    'required' => true,
                    'filters' => array(
                        array('StringTrim'),
                        array('StringToLower'),
                        array('StripNewlines'),
                        array('StripTags')
                     ),
                    'validators' => array(
                        array('Db_NoRecordExists', true,
                        array('table' => 'users', 'field' => 'email', 'messages' => array('recordFound' => 'Ten e-mail jest już zajęty'))),
                        array('NotEmpty', true),
                        array('EmailAddress', true),
                        ),  
                
                )
                
        );
        $this->email->getValidator('Db_NoRecordExists')->setMessages(array(
        Zend_Validate_Db_NoRecordExists::ERROR_NO_RECORD_FOUND => "Niem ma takiego adresu w bazie"
        ));
        $this->email->getValidator('NotEmpty')->setMessages(array(
        Zend_Validate_NotEmpty::INVALID => 'email nie może być pusty',
        Zend_Validate_NotEmpty::IS_EMPTY => 'musisz podać adress email'
        ));
        $this->email->getValidator('EmailAddress')->setMessages(array(
        Zend_Validate_EmailAddress::INVALID => 'Niepoprawny adress email',
        Zend_Validate_EmailAddress::INVALID_FORMAT => 'Niepoprawny format email'
        ));
        
        
        $this->addElement(
                'select', 
                'role',
                array(
                    'label' => 'Wybierz rolę',
                    'multiOptions' => array(
                        'user' => 'Student',
                        'employee' => 'Pracownik'
                    )
                ));

            
        //Przycisk tworzenia konta
        $this->addElement(
                'submit', 
                'submit', 
                array(
                    'label' => 'Utwórz konto',
                    'class' => 'btn btn-primary'
                )
        );
        $this->submit->setDecorators(array('ViewHelper'));
        $this->submit->addDecorator('HtmlTag', array('tag' => 'dd', 'class' => 'first-button'));
        
        //Wyczyść formularz
        $this->addElement(
                'reset', 
                'reset', 
                array(
                    'label' => 'Wyczyść formularz',
                    'class' => 'btn'
                    
                )
        );
        $this->reset->setDecorators(array('ViewHelper'));
        $this->reset->addDecorator('HtmlTag', array('tag' => 'dd'));
    }


}

