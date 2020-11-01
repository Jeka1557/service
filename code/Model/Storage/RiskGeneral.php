<?php


namespace Model\Storage;
use Model\Storage as Storage;
use Lib\Model\Collection;

class RiskGeneral extends Storage {

    protected $tableName = 'risk_general';
    protected $contextTableName = 'risk_general_context';
    // protected $relContextTableName = 'dict.rel_risk_general_context';

    protected $collectionClass = '\Model\Collection\RiskGeneral';
    protected $defaultFieldName = 'risk_general';


    function createEntity($row, $contextData, $className = null, $copyId = 0) {
        return \Model\RiskGeneral::newFromArray(array(
            'id' => $row['id'],
            'header' => $row['header'],
            'level' => $row['level'],
            'contextData' => $contextData,
            'copyId' => $copyId,
        ));
    }

    function addExtraEntities(Collection $collection, $entities, $copyId = 0) { }

}