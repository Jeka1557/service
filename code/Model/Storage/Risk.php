<?php


namespace Model\Storage;
use Model\Storage as Storage;
use Lib\Model\Collection;



class Risk extends Storage {

    protected $tableName = 'risk';
    protected $contextTableName = 'risk_context';

    // protected $relContextTableName = 'dict.rel_risk_context';

    protected $collectionClass = '\Model\Collection\Risk';
    protected $defaultFieldName = 'risk';


    function createEntity($row, $contextData, $className = null, $copyId = 0) {
        return \Model\Risk::newFromArray(array(
            'id' => $row['id'],
            'header' => $row['header'],
            'documentId' => $row['document_id'],
            'documentGeneralId' => $row['document_general_id'],
            'riskGeneralId' => $row['risk_general_id'],
            'contextData' => $contextData,
            'copyId' => $copyId,
        ));
    }

    function addExtraEntities(Collection $collection, $entities, $copyId = 0) {
        if (in_array('document', $entities) or array_key_exists('document', $entities)) {
            $storage = new Document($this->dsn);
            $storage->setToCollection($collection, null, isset($entities['document'])?$entities['document']:array(), $copyId);
        }
        if (in_array('documentGeneral', $entities) or array_key_exists('documentGeneral', $entities)) {
            $storage = new DocumentGeneral($this->dsn);
            $storage->setToCollection($collection, null, isset($entities['documentGeneral'])?$entities['documentGeneral']:array(), $copyId);
        }
    }

}