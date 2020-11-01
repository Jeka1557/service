<?php

namespace Model\Message;


/**
 * Class Video
 */

class Video extends \Model\Message {

    const ST_YOUTUBE = 'youtube';

    protected $TMPL_DEFAULT = 'Video';
    protected $TMPL_WC = 'wc/Video';
    protected $TMPL_VTB = 'wc/Video';
    protected $TMPL_BST4 = 'wc/Video';

    protected $_url;

    protected $_subtype;

    static public function newFromArray($data = [])
    {
        /* @var Video $entity */
        $entity = parent::newFromArray($data);

        $entity->_subtype = static::castVar($data['settings']['subtype'], 'TP\Text\Plain');

        switch ($entity->_subtype) {
            case self::ST_YOUTUBE:
                $url = static::castVar(isset($data['settings']['url'])?$data['settings']['url']:'','TP\Text\Plain', false);

                $parts = parse_url($url);
                $entity->_url = 'https://www.youtube.com/embed'.$parts['path'].(isset($parts['query'])?('?'.$parts['query']):'');
                break;
        }


        return $entity;
    }
}