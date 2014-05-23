<?php

class Application_Form_Ads extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        $this->addElement(
                'text',
                'topic',
                array(
                    'label' => 'Temat',
                    'class' => 'w930p',
                    'required' => true,
                    'filters' => array('StringTrim'),
                    'validators' => array(
                        array('NotEmpty', true),
                        array('StringLength', true, array('min' => 4, 'max' => 90)),
                        array('Regex', true, array ('pattern' => '/[0-9a-zA-Z\s\'.;-]+/', 'messages '=> array('regexNotMatch' => 'Użyto niedozwolone znaki')))
                        )
                 )
                );
        /*$this->topic->getValidator('StringLength')->setMessages(array(
        Zend_Validate_StringLength::TOO_LONG => 'Temat za długi',
        Zend_Validate_StringLength::TOO_SHORT => 'Temat za krótki',
        ));
        $this->topic->getValidator('NotEmpty')->setMessages(array(
        Zend_Validate_NotEmpty::IS_EMPTY => 'Temat nie może być pusty' 
        ));
        $this->topic->getValidator('Alnum')->setMessages(array(
        Zend_Validate_Alnum::NOT_ALNUM => 'Można używać tylko liter i cyfr'
        ));*/
        
        $this->addElement(
                'textarea', 
                'content', 
                array(
                    'label' => 'Treść Ogłoszenia:',
                    'class' => 'w930p h500p',
                    'required' => true,
                    'filters' => array('StringTrim'),
                    'validators' => array(
                        array('NotEmpty',true),
                        array('StringLength', true, array('min' => 4)),
                        array('Regex', true, array ('pattern' => '/[0-9a-zA-Z\s\'.;-]+/', 'messages '=> array('regexNotMatch' => 'Użyto niedozwolone znaki')))
                    )
                    ));
        /*$this->content->getValidator('StringLength')->setMessages(array(
        Zend_Validate_StringLength::TOO_SHORT => 'Treść musi być dłuższa niź 4 znaki'
        ));
        $this->content->getValidator('NotEmpty')->setMessages(array(
        Zend_Validate_NotEmpty::IS_EMPTY => 'Treść nie moze byc pusta',
        Zend_Validate_NotEmpty::INVALID => 'Treść ogłoszenia jest niepoprawna'
        ));
        $this->content->getValidator('Alnum')->setMessages(array(
        Zend_Validate_Alnum::NOT_ALNUM => 'Treść zawiera niedopuszczalne znaki',
        Zend_Validate_Alnum::INVALID => 'Treść zawiera niedopuszczalne znaki'
        ));*/
        
        $this->addElement(
                'text', 
                'exp', 
                array(
                    'label' => 'Data wygaśnięcia',
                    'filters' => array('StringTrim'),
                    'validators' => array(
                    array('NotEmpty', true),
                    array('Date', true),
                    array('Between', true, array('min' => '2014-01-01','max'=>'2099-01-01')))
                    ));
        $this->exp->getValidator('Date')->setMessages(array(
            Zend_Validate_Date::FALSEFORMAT => 'Poprawny format daty to Rok-Miesiąc-Dzień',
            Zend_Validate_Date::INVALID => 'Niepoprawna data',
            ));
            $this->exp->getValidator('Between')->setMessages(array(
            Zend_Validate_Between::NOT_BETWEEN => 'Możliwe dodanie daty po 2014-01-01', 
            ));
        $this->addElement('submit', 'submit', array('label' => 'Zatwierdź', 'class'=>'btn btn-success bg-g2 h35p',));
    }


}