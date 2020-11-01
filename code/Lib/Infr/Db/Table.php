<?php

namespace Lib\Infr\Db;
use Lib\Infr\Db as Db;
use Lib\Infr\Db\Exception;
use Lib\Infr\DSN;


class Table extends Select {

    const DSN_DEFAULT = 'dsn.default';

    /**
     * @var string aлиас таблицы
     */
    protected $_tableAlias = 'self';

    /**
     * @var string название таблицы
     */
    protected $_tableName;

    /**
     * @var string название поля первичного ключа
     */
    protected $_pkField = 'id';

    /**
     * Создает таблицу указывая ее имя.
     *
     * @param string|null $tableName название таблицы
     * @param string $config ключ конфигурации подключения к БД
     * @access public
     */
    public function __construct($tableName = null, $config = Table::DSN_DEFAULT) {
        if ($tableName) {
            $this->_tableName = $tableName;
        }

        if (!$this->_tableName) {
            throw new Exception(Exception::TABLE_NAME);
        }

        parent::__construct($this->_setupAdapter($config));

        if ($this->_tableAlias) {
            $this->from(array($this->_tableAlias => $this->_tableName), $this->_tableAlias . '.*');

        } else {
            $this->from($this->_tableName);
        }
    }

    /**
     * Установка адаптера СУБД.
     *
     * @param string $dsnConfig ключ конфигурации подключения к БД
     * @return Adapter
     * @throws Exception
     * @access protected
     */
    protected function _setupAdapter($dsnConfig = Table::DSN_DEFAULT) {
        throw new \Exception("Not implemented");
    }

    /**
     * Установка названия поля первичного ключа.
     *
     * @param string $pkField
     * @return Table
     * @access public
     */
    public function setPkField($pkField) {
        $this->_pkField = $pkField;
        return $this;
    }

    /**
     * Добавление записи.
     *
     * @param array $data данные array(filed1 => значение1, ...)
     * @param array $types типы данных array(filed1 => тип1, ...)
     * @return int|null значение PK добавленной записи
     * @access public
     */
    public function insert(array $data, array $types = array()) {
        $cols = array();
        $vals = array();
        foreach ($data as $col => $val) {
            $key = ':' . $col;
            $cols[$key] = $this->escapeIdentifier($col);
            $vals[$key] = $val;
            $type = (isset($types[$col]) ? $types[$col] : Db::PARAM_STR);
            $types[$key] = self::combineType($val, $type);
            unset($types[$col]);
        }

        $sql = 'INSERT INTO '
            . $this->escapeIdentifier($this->_tableName)
            . "\n(" . implode(', ', $cols) . ') '
            . "\nVALUES (" . implode(', ', array_keys($cols)) . ')';

        if ($this->_pkField) {
            $sql .= "\nRETURNING "
                . $this->escapeIdentifier($this->_tableName)
                . '.' . $this->escapeIdentifier($this->_pkField);
        }

        $sql = $this->prepareParams($sql, $vals, $types, Db::MASK_STRUCT);

        $this->event()
            ->trigger(Db::EVENT_BEFORE_INSERT, array(&$sql, &$vals, &$types));

        $rez = (float) $this->adapter()
            ->execute($sql, $vals, $types)
            ->fetchOne();

        $this->event()
            ->trigger(Db::EVENT_AFTER_INSERT, array($rez));

        return $rez;
    }

    /**
     * Обновление записи.
     *
     * @param array $data данные array(filed1 => значение1, ...)
     * @param array $where условия
     * @param array $types типы данных array(filed1 => тип1, ...)
     * @return int количество обновленных записей
     * @access public
     */
    public function update(array $data, array $where, array $types = array()) {
        $cols = array();
        $vals = array();
        foreach ($data as $col => $val) {
            $key = ':' . $col;
            $cols[$key] = $this->escapeIdentifier($col) . ' = ' . $key;
            $vals[$key] = $val;
            $type = (isset($types[$col]) ? $types[$col] : Db::PARAM_STR);
            $types[$key] = self::combineType($val, $type);
            unset($types[$col]);
        }

        $params = array();
        $ptypes = array();
        $conditions = array();
        foreach ($where as $cond => $data) {
            if (is_int($cond)) {
                $cond = $data;
                $data = array();
            }

            $conditions[] = '(' . $this->_parseCondition($cond, $data, Db::PARAM_STR, $params, $ptypes) . ')';
        }

        $vals = array_merge($vals, $params);
        $types = array_merge($types, $ptypes);

        $sql = 'UPDATE '
            . $this->escapeIdentifier($this->_tableName)
            . "\nSET\n\t" . implode(",\n\t", $cols)
            . "\nWHERE\n\t" . implode("\n\tAND ", $conditions);

        $sql = $this->prepareParams($sql, $vals, $types, Db::MASK_STRUCT);

        $this->event()
            ->trigger(Db::EVENT_BEFORE_UPDATE, array(&$sql, &$vals, &$types));

        $rez = $this->adapter()
            ->execute($sql, $vals, $types)
            ->rowsAffected();

        $this->event()
            ->trigger(Db::EVENT_AFTER_UPDATE, array($rez));

        return $rez;
    }

    /**
     * Удаление записей.
     *
     * @param array $where
     * @return int
     * @access public
     */
    public function delete(array $where = array()) {
        $params = array();
        $types = array();
        $conditions = array();
        foreach ($where as $cond => $data) {
            if (is_int($cond)) {
                $cond = $data;
                $data = array();
            }

            $conditions[] = '(' . $this->_parseCondition($cond, $data, Db::PARAM_STR, $params, $types) . ')';
        }

        $sql = 'DELETE FROM '
            . $this->escapeIdentifier($this->_tableName)
            . (($conditions) ? "\nWHERE\n\t" . implode("\n\tAND ", $conditions) : '');

        $sql = $this->prepareParams($sql, $params, $types, Db::MASK_STRUCT);

        $this->event()
            ->trigger(Db::EVENT_BEFORE_DELETE, array(&$sql, &$params, &$types));

        $rez = $this->adapter()
            ->execute($sql, $params, $types)
            ->rowsAffected();

        $this->event()
            ->trigger(Db::EVENT_AFTER_DELETE, array($rez));

        return $rez;
    }
}