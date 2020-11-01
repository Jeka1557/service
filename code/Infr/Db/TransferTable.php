<?php

namespace Infr\Db;


class TransferTable {

    public $srcTable;
    public $dstTable;
    public $columns;
    public $columnsString;

    public $bindString;
    public $assignString;


    public function __construct($definer)
    {
        $this->srcTable = 'export.'.$definer['table'];
        $this->dstTable = $definer['table'];

        $this->columns = $definer['columns'];
        $this->columnsString = implode(',', $definer['columns']);

        $this->bindString = ':'.implode(',:',$definer['columns']);


        $assign = [];

        foreach ($definer['columns'] as $name) {
            $assign[] = "{$name} = :{$name}";
        }

        $this->assignString = implode(',', $assign);
    }


    public function getValues($row) {
        $result = [];

        foreach ($this->columns as $column) {
            if (!strlen($row[$column]))
                $result[] = "null";
            else
                $result[] = "'".pg_escape_string($row[$column])."'";
        }

        return implode(',', $result);
    }


    public function getAssign($row) {
        $result = [];

        foreach ($this->columns as $column) {
            if (!strlen($row[$column]))
                $result[] = "{$column} = null";
            else
                $result[] = "{$column} = '".pg_escape_string($row[$column])."'";
        }

        return implode(',', $result);
    }
}