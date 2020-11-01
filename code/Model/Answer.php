<?php


namespace Model;

use TP;
use Model\Exception;

/**
 * @property-read TP\UInt2 $id
 * @property-read TP\UInt2 $header
 */

class Answer extends DictEntity {

    protected $_id;
    protected $_header;
    protected $_idx;
    protected $_excl;

    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var Answer $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'TP\UInt2');
        $entity->_header = static::castVar($data['header'],'TP\Text\Plain');
        $entity->_idx = static::castVar($data['idx'],'TP\UInt2');
        $entity->_excl = static::castVar($data['excl'],'TP\TBool');

        $entity->_entityType = \PT\EntityType::ANSWER();

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        return $entity;
    }

}