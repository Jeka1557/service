<?php

namespace Model\Message;


/**
 * Class IFrame
 */


class IFrame extends \Model\Message {

    protected $TMPL_DEFAULT = 'IFrame';
    protected $TMPL_WC = 'wc/IFrame';
    protected $TMPL_VTB = 'wc/IFrame';
    protected $TMPL_BST4 = 'wc/IFrame';

    protected $_src;

    static public function newFromArray($data = [])
    {
        /* @var IFrame $entity */
        $entity = parent::newFromArray($data);
        $entity->_text = ' ';

        $entity->_src = static::castVar(isset($data['settings']['src'])?$data['settings']['src']:'','TP\Text\Plain', false);;

        return $entity;
    }
}