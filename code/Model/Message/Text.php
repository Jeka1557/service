<?php

namespace Model\Message;

use Infr\Template;

/**
 * Class Text
 *
 * @property-read $text
 * @property-read $textUpdated
 * @property-read $textRendered
 *
 */


class Text extends \Model\Message {

    protected $TMPL_DEFAULT = 'Text';
    protected $TMPL_WC = 'wc/Text';
    protected $TMPL_VTB = 'wc/Text';
    protected $TMPL_BST4 = 'wc/Text';

    protected $_alertClass;

    protected $_textUpdated;

    protected $textRendered = false;


    static public function newFromArray($data = [])
    {
        /* @var Text $entity */
        $entity = parent::newFromArray($data);

        if (isset($data['settings']['alert_class']))
            $entity->_alertClass = $data['settings']['alert_class'];


        return $entity;
    }


    public function __get($name) {
        if ($name=='text')
            $this->renderText();

        return parent::__get($name);
    }

    public function render($inGroup = false) {
        $this->renderText();
        return parent::render($inGroup);
    }


    protected function applyContext($contextData) {
        $context = parent::applyContext($contextData);

        if (!is_null($context))
            $this->_textUpdated = $context['updated'];

        return $context;
    }


    protected function renderText() {
        if ($this->textRendered)
            return;

        if (strpos($this->_text, '{')!==false) {
            $templ = new Template\DB('Message', $this->_id, $this->_textUpdated, $this->_text);
            $this->_text = $templ->parse();
        }

        if ($this->_textHTML)
            $this->_text = $this->renderTables($this->_text);
        else
            $this->_text = nl2br($this->_text);


        $this->textRendered = true;
    }


    protected function renderTables($text) {
        if (stripos($text,'<table')===false)
            return $text;

        $text = preg_replace_callback('~<table[^>]*>~i',
            function ($matches) {
                $tableClass = 'table table-bordered table-hover ';
                $tableTag = $matches[0];

                if (stripos($tableTag,'class=')!==false) {
                    $tableTag = preg_replace('~(class=[\'"])~i', '$1'.$tableClass, $tableTag);
                } else {
                    $tableTag = preg_replace('~>$~', 'class="'.$tableClass.'" >', $tableTag);
                }

                return $tableTag;
            },
            $text
        );

        return $text;
    }


}