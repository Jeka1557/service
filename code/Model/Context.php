<?php


namespace Model;
use TP;

/**
 * @property-read TP\UInt2 $id
 * @property-read TP\UInt2 $header
 */

class Context extends DictEntity {

    protected $_id;
    protected $_name;

    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var Context $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'PT\ContextId');
        $entity->_name = static::castVar($data['name'],'TP\Text\Plain');
        $entity->_extId = $entity->_id;

        return $entity;
    }

}