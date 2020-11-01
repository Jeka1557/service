<?php

namespace Model;
use Lib\Model\Storage as LibStorage;
use Lib\Model\Collection;
use Lib\Model\Value;
use Lib\Infr\Db\Adapter;
use Lib\Infr\DSN;
use Lib\Infr\Db\Select;
use Infr\Config;
use TP;


abstract class Storage extends LibStorage {

    /**
     * @var Adapter
     */
    protected $dbAdapter;
    /**
     * @var DSN
     */
    protected $dsn;

    static protected $tableSchema1;
    static protected $tableSchema2;
    static protected $tablePrefix = 'dct';

    protected $tableName;
    protected $contextTableName;
    protected $relContextTableName;

    protected $collectionClass;
    protected $defaultFieldName;


    abstract function createEntity($row, $contextData, $className = null, $copyId = 0);

    abstract function addExtraEntities(Collection $collection, $entities, $copyId = 0);

    public static function setTableSchema($name1, $name2 = null) {
        self::$tableSchema1 = $name1;
        self::$tableSchema2 = is_null($name2)?$name1:$name2;
    }

    public static function setTablePrefix($prefix) {
        self::$tablePrefix = $prefix;
    }

    static public function makeTableName($tableName, $schema = 2) {
        if ($schema==1)
            return (self::$tableSchema1?self::$tableSchema1.'.':'').(self::$tablePrefix?self::$tablePrefix.'_':'').$tableName;
        else
            return (self::$tableSchema2?self::$tableSchema2.'.':'').(self::$tablePrefix?self::$tablePrefix.'_':'').$tableName;
    }


    public function __construct(DSN $dsn) {
        $this->dsn = $dsn;
        $this->dbAdapter = Adapter::create($dsn::DRIVER, $dsn);

        $this->tableName = $this->makeTableName($this->tableName, 1);
        $this->contextTableName = $this->makeTableName($this->contextTableName, 2);
        $this->relContextTableName = $this->makeTableName($this->relContextTableName, 2);
    }


    public function getEntityContexts(\TP\Type $id) {
        $select = new Select($this->dbAdapter);
        $rows = $select->from(array('self' => $this->relContextTableName))
            ->joinLeft(array('ctx' => 'dict.context'),'ctx.id = self.context_id')
            ->columns(array(
                'id' => 'ctx.id',
                'name' => 'ctx.name',
            ))
            ->where("object_id", $id->val())
            ->order('ctx.id')
            ->execute()
            ->fetchAll();

        $result = new \Model\Collection\Context();

        foreach ($rows as $row) {
            $result[] = \Model\Context::newFromArray($row);
        }

        return $result;
    }


    protected function getContextData($id) {
        $select = new Select($this->dbAdapter);
        $rows = $select->from($this->contextTableName)
            ->where("object_id", $id)
            ->execute()
            ->fetchAll();

        $result = array();


        foreach ($rows as $row) {
            $result[$row['context_id']] = array(
                'contextId' => $row['context_id'],
                'header' => isset($row['header'])?$row['header']:'',
                'text' => $row['text'],
                'notExists' => $row['not_exists'],
                'updated' => isset($row['updated'])?(int)$row['updated']:0,
                'html' => isset($row['html'])?($row['html']=='t'?true:false):true,

            );
        }

        return $result;
    }

    public function getById(\TP\Type $id, array $entities = array(), $className = null, $copyId = 0) {
        /** @var \Model\DictEntity $entity */

        $select = new Select($this->dbAdapter);
        $row = $select->from($this->tableName)
            ->where("id", $id->val())
            ->execute()
            ->fetchRow();

        if (is_null($row))
            return null;

        $contextData = in_array('contextData',$entities)?$this->getContextData($id->val()):array();

        $entity = $this->createEntity($row, $contextData, $className, $copyId);

        if (is_null($entity))
            return null;

        $collection = new $this->collectionClass();
        $collection[] = $entity;

        $this->addExtraEntities($collection, $entities, $copyId);


        return $entity;
    }

    public function getByIds(\TP\Arr\Arr $ids, array $entities = array(), Collection $collection = null, $copyId = 0) {

        if (is_null($collection))
            $collection = new $this->collectionClass();

        if (!count($ids))
            return $collection;

        $select = new Select($this->dbAdapter);
        $rows = $select->from($this->tableName)
            ->where("id", $ids->toArray())
            ->execute()
            ->fetchAll();

        foreach ($rows as $row) {
            $contextData = in_array('contextData',$entities)?$this->getContextData($row['id']):array();
            $entity = $this->createEntity($row, $contextData, $collection->getEntityClassName(), $copyId = 0);

            if (is_null($entity))
                continue;

            $collection[$row['id']] = $entity;
        }

        $this->addExtraEntities($collection, $entities, $copyId);

        return $collection;
    }

    /**
     *  Набросок, надо доработать.
     */

    public function getCollection(array $conditions = array(), array $entities = array(), Collection $collection = null, $copyId = 0) {
        if (is_null($collection))
            $collection = new $this->collectionClass();

        $select = new Select($this->dbAdapter);
        $select->from($this->tableName);
        //->where("id", $ids->toArray())

        if (isset($conditions['type']))
            $select->where('type', $conditions['type']);

        $rows = $select->execute()
            ->fetchAll();

        foreach ($rows as $row) {
            $contextData = in_array('contextData',$entities)?$this->getContextData($row['id']):array();
            $entity = $this->createEntity($row, $contextData, $collection->getEntityClassName(), $copyId);

            if (is_null($entity))
                continue;

            $collection[$row['id']] = $entity;
        }

        return $collection;
    }


    public function getCount(array $conditions = array()) {}

    public function setToCollection(Collection $collection, $nameField = null, array $entities = array(), $copyId = 0) {
        if (!count($collection))
            return ;

        if (is_null($nameField))
            $nameField = $this->defaultFieldName;

        $idNameField = $nameField.'Id';

        $ids = $collection->getGroupProperty($idNameField);

        if (!count($ids))
            return ;

        $select = new Select($this->dbAdapter);
        $rows = $select->from($this->tableName)
            ->where("id", $ids)
            ->execute()
            ->fetchAll();

        $chEntities = new $this->collectionClass();

        foreach ($rows as $row) {
            $contextData = in_array('contextData',$entities)?$this->getContextData($row['id']):array();
            $entity = $this->createEntity($row, $contextData, null, $copyId);

            if (is_null($entity))
                continue;

            $chEntities[$row['id']] = $entity;
        }

        foreach ($collection as $entity) {
            if (isset($chEntities[$entity->$idNameField]))
                $entity->$nameField = $chEntities[$entity->$idNameField];
        }

        $this->addExtraEntities($chEntities, $entities, $copyId);
    }

    public function setToEntity(Value $target, $nameField = null, array $entities = array(), $copyId = 0) {
        if (is_null($nameField))
            $nameField = $this->defaultFieldName;

        $idNameField = $nameField.'Id';

        $select = new Select($this->dbAdapter);
        $row = $select->from($this->tableName)
            ->where("id", $target->$idNameField)
            ->execute()
            ->fetchRow();

        if ($row) {
            $contextData = in_array('contextData',$entities)?$this->getContextData($row['id']):array();
            $entity = $this->createEntity($row, $contextData, null, $copyId);
            $target->$nameField = $entity;

            if (!is_null($entity)) {
                $collection = new $this->collectionClass();
                $collection[] = $target->$nameField;

                $this->addExtraEntities($collection, $entities, $copyId);
            }
        }
    }



    protected function parseArrayField($array, $asText = false) {
        if (is_null($array))
            return array();

        $s = $array;
        $retval = null;

        if ($asText) {
            $s = str_replace("{", "array('", $s);
            $s = str_replace("}", "')", $s);
            $s = str_replace(",", "','", $s);
        } else {
            $s = str_replace("{", "array(", $s);
            $s = str_replace("}", ")", $s);
        }

        $s = "\$retval = $s;";
        eval($s);
        return $retval;
    }
}