<?php


namespace Model\Storage;

use PT;
use Model\Storage as Storage;
use Lib\Model\Collection;



class Message extends Storage {

    protected $tableName = 'message';
    protected $contextTableName = 'message_context';

    // protected $relContextTableName = 'dict.rel_risk_context';

    protected $collectionClass = '\Model\Collection\Message';
    protected $defaultFieldName = 'message';

    /**
     * @param $row
     * @param $contextData
     * @param null $className
     * @param int $copyId
     * @return \Model\Message|null
     * @throws \Exception
     */

    function createEntity($row, $contextData, $className = null, $copyId = 0) {

        $items = [];

        if (isset($row['items'])) {
            foreach ($this->parseArrayField($row['items']) as $e) {

                list($id, $objectId, $entityType, $entityId, $idx, $settings) = explode(',', trim($e, '()'));
                $settings = json_decode(base64_decode($settings), true);

                $items[] = [
                    'id' => $id,
                    'objectId' => $objectId,
                    'entityType' => $entityType,
                    'entityId' => $entityId,
                    'idx' => $idx,
                    'settings' => $settings
                ];
            }
        }

        $data = [
            'id' => $row['id'],
            'header' => $row['header'],
            'hidden' => $row['hidden'],
            'copyId' => $copyId,
            'contextData' => $contextData,
            'settings' => json_decode($row['settings'], true),
            'items' => $items,
        ];

        return \Model\Message::newEntity(new PT\MessageType($row['type']), $data);

    }

    function addExtraEntities(Collection $collection, $entities, $copyId = 0) {
    }

}