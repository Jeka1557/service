<?php


namespace Model\Storage;
use Model\Storage as Storage;
use Lib\Model\Collection;


class DocumentGeneral extends Storage {

    protected $tableName = 'document_general';
    protected $contextTableName = 'document_general_context';

    // protected $relContextTableName = 'dict.rel_document_general_context';

    protected $collectionClass = '\Model\Collection\DocumentGeneral';
    protected $defaultFieldName = 'documentGeneral';


    function createEntity($row, $contextData, $className = null, $copyId = 0) {
        return \Model\DocumentGeneral::newFromArray(array(
            'id' => $row['id'],
            'header' => $row['header'],
            'contextData' => $contextData,
            'copyId' => $copyId,
        ));
    }

    function addExtraEntities(Collection $collection, $entities, $copyId = 0) { }

}