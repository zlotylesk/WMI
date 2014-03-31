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
                    'required' => true,
                    'filters' => array('StringTrim'),
                    'validators' => array(array('notEmpty', true,
                        array('messages' => array('isEmpty' => 'Ogłoszenie musi mieć temat'))),
                        array('StringLength', true, array('min' => 4, 'max' => 90)),
                        ),
                 )
                );
        $this->topic->getValidator('StringLength')->setMessages(array(
        Zend_Validate_StringLength::TOO_LONG => 'Temat za długi',
        Zend_Validate_StringLength::TOO_SHORT => 'Temat za krótki',
        ));
        $this->addElement(
                'text', 
                'content', 
                array(
                    'label' => 'Treść Ogłoszenia:',
                    'required' => true,
                    'filters' => array('StringTrim'),
                    'validators' => array(array('notEmpty', true,
                        array('messages' => array('isEmpty' => 'Ogłoszenie musi zawierać treść'))),
                        array('StringLength', true, array('min' => 4)))
  
                    ));
        $this->content->getValidator('StringLength')->setMessages(array(
        Zend_Validate_StringLength::TOO_SHORT => 'Treść musi być dłuższa niź 4 znaki',
        ));
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
        $this->addElement('submit', 'submit', array('label' => 'Zatwierdź'));
    }


}

