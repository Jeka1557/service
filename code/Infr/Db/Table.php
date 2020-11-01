<?php


namespace Infr\Db;
use Infr\Config;
use Lib\Infr\DSN;
use Lib\Infr\Db\Adapter;


class Table extends \Lib\Infr\Db\Table {

    protected $dsn;

    static protected $tableSchema;
    static protected $tablePrefix = '';

    public function __construct(DSN $dsn, $tableName = null) {
        $this->dsn = $dsn;

        $tableName = static::makeTableName(is_null($tableName)?$this->_tableName:$tableName);
        parent::__construct($tableName);
    }

    protected function _setupAdapter($dsnConfig  = Table::DSN_DEFAULT) {
        $dsn = $this->dsn;
        return Adapter::getInstance($dsn::DRIVER, $dsn);
    }

    static public function makeTableName($tableName) {
        return (static::$tableSchema?static::$tableSchema.'.':'').(static::$tablePrefix?static::$tablePrefix.'_':'').$tableName;
    }

    static public function setTableSchema($name) {
        return static::$tableSchema = $name;
    }

    static public function getTableSchema() {
        return static::$tableSchema;
    }

    static public function setTablePrefix($prefix) {
        return static::$tablePrefix = $prefix;
    }
}