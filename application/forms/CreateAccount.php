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
                            array('Db_NoRecordExists', true,
                            array('table' => 'users', 'field' => 'username', 'messages' => array('recordFound' => 'Ten login jest już zajęty'))
                            ),
                            array('notEmpty', true,
                            array('messages' => array('isEmpty' => 'Proszę wpisać login')
                            )),
                            array('stringLength', true,
                            array('min'=>3, 'max'=>12, 'messages'=>
                            array('stringLengthTooShort' => 'Login musi składać się z co najmniej 3 znaków',
                                  'stringLengthTooLong' => 'Login musi składać się z maksymalnie 12 znaków',
                            )
                            )),
                            array ('Regex', true, 
                            array ('pattern' => '/^[a-zA-Z0-9_\-]+$/', 
                            'messages'=>array('regexNotMatch' => 'Użyto niedozwolone znaki')
                            )),
                     ),
                 )
        );
        
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
                        array('notEmpty', true, 
                        array('messages' => array('isEmpty' => 'Proszę wpisać adres e-mail')
                        )),
                        array('EmailAddress', true, 
                        array('messages' => 
                        array(Zend_Validate_EmailAddress::INVALID => 'Wpisany adres e-mail jest niepoprawny', Zend_Validate_EmailAddress::INVALID_FORMAT => 'Wpisany adres e-mail jest niepoprawny'
                        ))),
                        ),  
                
                )
                
        );
        
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

