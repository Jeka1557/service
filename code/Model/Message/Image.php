<?php

namespace Model\Message;


/**
 * Class Image
 */


class Image extends \Model\Message {

    const ST_URL = 'url';
    const ST_FILE = 'file';

    protected $TMPL_DEFAULT = 'Image';
    protected $TMPL_WC = 'wc/Image';
    protected $TMPL_VTB = 'wc/Image';
    protected $TMPL_BST4 = 'wc/Image';

    protected $_subtype;

    protected $_url;

    static public function newFromArray($data = [])
    {
        /* @var Image $entity */
        $entity = parent::newFromArray($data);

        $entity->_text = '';

        $entity->_subtype = static::castVar($data['settings']['subtype'], 'TP\Text\Plain');

        switch ($entity->_subtype) {
            case self::ST_URL:
                $entity->_url = static::castVar(isset($data['settings']['url'])?$data['settings']['url']:'','TP\Text\Plain', false);
                break;
        }

        return $entity;
    }
}
