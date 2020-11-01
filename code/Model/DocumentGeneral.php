<?php


namespace Model;
use Model\Exception;

class DocumentGeneral extends DictEntity {

    protected $_id;
    protected $_header;

    protected $_properties;

    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var DocumentGeneral $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'PT\DocumentGeneralId');
        $entity->_header = static::castVar($data['header'],'TP\Text\Plain');

        $entity->_extId =  "{$entity->_id}".($data['copyId']>0?"_{$data['copyId']}":'');

        $entity->_entityType = \PT\EntityType::DOCUMENT_GENERAL();

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        return $entity;
    }


    public function __set($name, $value) {
        switch ($name) {
            case 'properties':
                $this->_properties = static::castVar($value, '\Model\Collection\Document');
                break;
            default:
                parent::__set($name, $value);
                break;
        }
    }


    public function export() {
        $data = parent::export();
        $data['properties'] = isset($this->_properties)?$this->_properties->export():[];

        return $data;
    }

}