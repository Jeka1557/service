<?php


namespace Model\Storage;
use Model\Storage as Storage;
use Lib\Model\Collection;
use Lib\Infr\Db\Select;
use TP;

class Answer extends Storage {

    protected $tableName = 'answer';
    protected $contextTableName = 'answer_context';

    // protected $relContextTableName = 'dict.rel_answer_context';

    protected $collectionClass = '\Model\Collection\Answer';
    protected $defaultFieldName = 'answer';


    public function getByQuestion(TP\UInt2 $questionId, array $entities = array(), Collection $collection = null) {

        if (is_null($collection))
            $collection = new $this->collectionClass();


        $select = new Select($this->dbAdapter);
        $rows = $select->from($this->tableName)
            ->where("question_id", $questionId->val())
            ->execute()
            ->fetchAll();

        foreach ($rows as $row) {
            $entity = $this->createEntity($row, $this->getContextData($row['id']), $collection->getEntityClassName());

            if (!is_null($entity))
                $collection[$row['id']] = $entity;
        }

        $this->addExtraEntities($collection, $entities);

        return $collection;
    }

    function createEntity($row, $contextData, $className = null, $copyId = 0) {
        return \Model\Answer::newFromArray(array(
            'id' => $row['id'],
            'header' => $row['header'],
            'idx' => $row['idx'],
            'excl' => $row['excl'],
            'contextData' => $contextData,
        ));
    }

    function addExtraEntities(Collection $collection, $entities, $copyId = 0) { }

}