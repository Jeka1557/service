<?php


namespace Model;
use Model\Exception;

class Document extends DictEntity {

    protected $_id;
    protected $_header;

    protected $_documentGeneralId;

    protected $_documentGeneral;

    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var Document $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'PT\DocumentId');
        $entity->_header = static::castVar($data['header'],'TP\Text\Plain');

        $entity->_documentGeneralId = static::castVar($data['documentGeneralId'],'PT\DocumentGeneralId');
        $entity->_extId =  "{$entity->_id}".($data['copyId']>0?"_{$data['copyId']}":'');

        $entity->_entityType = \PT\EntityType::DOCUMENT();

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        return $entity;
    }


    public function __set($name, $value) {
        switch ($name) {
            case 'documentGeneral':
                $this->_documentGeneral = static::castVar($value, '\Model\DocumentGeneral');
                break;
            default:
                parent::__set($name, $value);
                break;
        }
    }

}