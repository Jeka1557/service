<?php


namespace Model;

use Model\Exception;

/**
 * Class Model_RiskGeneral
 *
 * @property-read $id
 * @property-read $header
 * @property-read $level
 * @property-read $text

 * @property-read \Model\Collection\Risk $reasons
 */


class RiskGeneral extends DictEntity {

    protected $_id;
    protected $_header;

    protected $_level;

    protected $_reasons;

    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var RiskGeneral $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'PT\RiskGeneralId');
        $entity->_header = static::castVar($data['header'],'TP\Text\Plain');
        $entity->_level = static::castVar($data['level'],'PT\RiskLevel');

        $entity->_extId =  "{$entity->_id}".($data['copyId']>0?"_{$data['copyId']}":'');

        $entity->_entityType = \PT\EntityType::RISK_GENERAL();

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        return $entity;
    }


    public function __set($name, $value) {
        switch ($name) {
            case 'reasons':
                $this->_reasons = static::castVar($value, '\Model\Collection\Risk');
                break;
            default:
                parent::__set($name, $value);
                break;
        }
    }


    public function export() {
        $data = parent::export();

        $data['level'] = $this->_level;
        $data['reasons'] = isset($this->_reasons)?$this->_reasons->export():[];

        return $data;
    }

}