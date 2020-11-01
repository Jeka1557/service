<?php

namespace Model\Storage;
use Model\Storage as Storage;
use Lib\Model\Collection;
use Lib\Infr\Db\Select;
use Lib\Infr\DSN;


class Expression extends Storage {

    protected $tableName = 'expression';
    protected $contextTableName = 'expression_context';

    // protected $relContextTableName = 'dict.rel_expression_context';

    protected $collectionClass = '\Model\Collection\Expression';
    protected $defaultFieldName = 'expression';

    protected $answerTableName = 'expression_answer';

    public function __construct(DSN $dsn) {
        parent::__construct($dsn);

        $this->answerTableName = $this->makeTableName($this->answerTableName);
    }


    function createEntity($row, $contextData, $className = null, $copyId = 0) {

        $items = array();

        if (isset($row['items'])) {
            foreach ($this->parseArrayField($row['items']) as $e) {
                list($objectId, $entityType, $entityId, $idx, $defaultValue) = explode(',', trim($e, '()'));
                $items[] = array('objectId' => $objectId, 'entityType' => $entityType, 'entityId' => $entityId, 'key' => $idx, 'defaultValue' => $defaultValue);
            }
        }

        $data = array(
            'id' => $row['id'],
            'header' => $row['header'],
            'items' => $items,
            'contextData' => $contextData,
            'copyId' => $copyId,
        );

        $type = new \PT\ExpressionType($row['type']);
        $entity = \Model\Expression::newEntity($type, $data);

        $this->addAnswers($entity);

        return $entity;
    }

    function addExtraEntities(Collection $collection, $entities, $copyId = 0) {

    }


    protected function addAnswers(\Model\Expression $entity) {

        $result = array();

        $select = new Select($this->dbAdapter);
        $rows = $select->from($this->answerTableName)
            ->where("expression_id", $entity->id)
            ->execute()
            ->fetchAll();


        foreach ($rows as $row) {
            $row['expressionId'] = $entity->id;
            $answer = \Model\Expression\Answer::newFromArray($row);
            $result[$answer->id] = $answer;
        }

        $entity->setAnswers($result);
    }

}