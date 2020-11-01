<?php


namespace Model\Storage;
use Model\Storage as Storage;
use Lib\Model\Collection;

class Document extends Storage {

    protected $tableName = 'document';
    protected $contextTableName = 'document_context';

    // protected $relContextTableName = 'dict.rel_document_context';

    protected $collectionClass = '\Model\Collection\Document';
    protected $defaultFieldName = 'document';


    function createEntity($row, $contextData, $className = null, $copyId = 0) {
        return \Model\Document::newFromArray(array(
            'id' => $row['id'],
            'header' => $row['header'],
            'documentGeneralId' => $row['document_general_id'],
            'contextData' => $contextData,
            'copyId' => $copyId,
        ));
    }

    function addExtraEntities(Collection $collection, $entities, $copyId = 0) {
        if (in_array('documentGeneral', $entities)) {
            $storage = new DocumentGeneral($this->dsn);
            $storage->setToCollection($collection, null, array(), $copyId);
        }
    }

}