<?php

namespace Model\Storage;
use Lib\Infr\Db\Select;
use Lib\Infr\DSN;
use Model\Storage as Storage;
use Lib\Model\Collection;
use PT\ConclusionId;


class Conclusion extends Storage {

    protected $tableName = 'conclusion';
    protected $contextTableName = 'conclusion_context';
    protected $fileTableName = 'conclusion_file';

    // protected $relContextTableName = 'dict.rel_conclusion_context';

    protected $collectionClass = '\Model\Collection\Conclusion';
    protected $defaultFieldName = 'conclusion';



    public function __construct(DSN $dsn) {
        parent::__construct($dsn);

        $this->fileTableName = $this->makeTableName($this->fileTableName);
    }


    function createEntity($row, $contextData, $className = null, $copyId = 0) {

        $data = array(
            'id' => $row['id'],
            'header' => $row['header'],
            'contextData' => $contextData,
            'copyId' => $copyId,
            'type' => $row['type'],
        );

        return \Model\Conclusion::newEntity(new \PT\ConclusionType($row['type']), $data);
    }

    function addExtraEntities(Collection $collection, $entities, $copyId = 0) {

    }


    /**
     * @param ConclusionId $conclusionId
     * @return int
     * @throws \Exception
     */
    public function getFileUpdated(ConclusionId $conclusionId) {
        $select = new Select($this->dbAdapter);

        $row = $select->from(['self' => $this->fileTableName])
            ->columns(['updated'])
            ->where('conclusion_id', $conclusionId->val())
            ->execute()
            ->fetchRow();

        if (!$row)
            throw new \Exception("Conclusion file id: {$conclusionId->val()} not found");

        return $row['updated'];
    }


    /**
     * @param ConclusionId $conclusionId
     * @return string
     * @throws \Exception
     */
    public function getFileBody(ConclusionId $conclusionId) {
        $select = new Select($this->dbAdapter);

        $row = $select->from(['self' => $this->fileTableName])
            ->where('conclusion_id', $conclusionId->val())
            ->execute()
            ->fetchRow();

        $fileBody =  pg_unescape_bytea($row['body']);

        return $fileBody;
    }

}