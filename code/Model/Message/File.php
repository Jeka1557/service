<?php

namespace Model\Message;


/**
 * Class File
 */


class File extends \Model\Message {

    protected $TMPL_DEFAULT = 'File';
    protected $TMPL_WC = 'wc/File';
    protected $TMPL_VTB = 'wc/File';
    protected $TMPL_BST4 = 'wc/File';

    protected $_text;
    protected $_textHeader;

    static public function newFromArray($data = [])
    {
        /* @var Image $entity */
        $entity = parent::newFromArray($data);

        return $entity;
    }

    protected function applyContext($contextData) {
        $context = parent::applyContext($contextData);

        if (!is_null($context))
            $this->_textHeader = $context['header'];

        return $context;
    }
}
