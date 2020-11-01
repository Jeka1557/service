<?php

namespace Model\Storage;
use Model\Storage as Storage;
use Lib\Model\Collection;
use Lib\Infr\Utility\Encoding;


class Info extends Storage {

    protected $tableName = 'info';
    protected $contextTableName = 'info_context';

    // protected $relContextTableName = 'dict.rel_info_context';

    protected $collectionClass = '\Model\Collection\Info';
    protected $defaultFieldName = 'info';


    function createEntity($row, $contextData, $className = null, $copyId = 0) {

        $data = array(
            'id' => $row['id'],
            'header' => $row['header'],
            'defaultAnswerId' => $row['default_answer_id'],
            'defaultValue' => $row['default_value'],
            'placeholder' => $row['placeholder'],
            'required' => $row['required'],
            'contextData' => $contextData,
            'copyId' => $copyId,
            'settings' => json_decode($row['settings'], true),
        );

        return \Model\Info::newEntity(new \PT\InfoType($row['type']), $data);
    }

    function addExtraEntities(Collection $collection, $entities, $copyId = 0) {

    }

}