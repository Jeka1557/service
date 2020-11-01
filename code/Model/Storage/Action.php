<?php


namespace Model\Storage;
use Model\Storage as Storage;
use Lib\Model\Collection;
use Lib\Infr\Utility\Encoding;


class Action extends Storage {

    protected $tableName = 'action';
    protected $contextTableName = 'action_context';

    // protected $relContextTableName = 'dict.rel_risk_context';

    protected $collectionClass = '\Model\Collection\Action';
    protected $defaultFieldName = 'action';


    function createEntity($row, $contextData, $className = null, $copyId = 0) {

        $type = new \PT\ActionType($row['type']);

        $data = [
            'id' => $row['id'],
            'header' => $row['header'],
            'type' => $row['type'],
            'settings' => json_decode($row['settings'], true),
            'copyId' => $copyId,
        ];

        switch ($type->val()) {
            case \PT\ActionType::SENDMAIL:
                return \Model\Action\Sendmail::newFromArray($data);
            break;
            case \PT\ActionType::SAVEFILE:
                return \Model\Action\Savefile::newFromArray($data);
                break;
            case \PT\ActionType::EGRUL:
                return \Model\Action\Egrul::newFromArray($data);
                break;
            default:
                return \Model\Action::newFromArray($data);
            break;
        }
    }


    function addExtraEntities(Collection $collection, $entities, $copyId = 0) {

    }
}