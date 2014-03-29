<?php

class Application_Form_Ads extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        $this->addElement('text', 'topic', array('label' => 'Temat'));
        $this->addElement('text', 'content', array('label' => 'Treść Ogłoszenia:'));
        $this->addElement('text', 'exp', array('label' => 'Data wygaśnięcia'));
        $this->addElement('submit', 'submit', array('label' => 'Zatwierdź'));
    }


}

