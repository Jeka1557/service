<?php
/**
 * Date 16.08.12 18:47
 * @author anton
 * @package LSF2\Infr\Db\Statement
 * @copyright Lightsoft 2012
 */

namespace Lib\Infr\Db\Statement;
use \Lib\Infr\Db;
use \Lib\Infr\Db\Statement;
use \Lib\Infr\Db\Adapter;

/**
 * Pgsql
 *
 * @package LSF2\Infr\Db\Statement
 */
final class Pgsql extends Statement {

    /**
     * @var array соответствие типов результата
     */
    protected static $fetch = array(
        Db::FETCH_BOTH => PGSQL_BOTH,
        Db::FETCH_ASSOC => PGSQL_ASSOC,
        Db::FETCH_NUM => PGSQL_NUM
    );

    /**
     * @param Adapter $adapter
     * @param resource $result
     * @throws Exception
     * @access public
     */
    public function __construct(Adapter $adapter, $result) {
        if (get_resource_type($result) != 'pgsql result') {
            throw new Exception(Exception::STMT_RESOURCE_TYPE);
        }

        $this->adapter = $adapter;
        $this->result = $result;
        $this->count = pg_num_rows($result);
        $this->countFields = pg_num_fields($result);
        $this->affected = pg_affected_rows($result);

        if ($this->count == -1) {
            throw new Exception(Exception::STMT_RESULT_COUNT);
        }

        // вернет даже если пустой результат
        if ($this->countFields == -1) {
            throw new Exception(Exception::STMT_FIELDS_COUNT);
        }
    }

    /**
     * Освобождение результата запроса.
     *
     * @access public
     */
    public function __destruct() {
        pg_free_result($this->result);
    }

    /**
     * Выборка строчки результата запроса в виде массива.
     *
     * @param int $fetch тип представления результата
     * @param null|int $offset смещение
     * @param array|null $types типы полей
     * @return array|null
     * @throws Exception
     * @access public
     */
    public function fetchRow($fetch = Db::FETCH_ASSOC, $offset = null, array $types = null) {
        $data = pg_fetch_array($this->result, $offset, static::_adapterFetch($fetch));

        if ($data && $types) {
            $data = $this->_unescape($data, $types);

        } elseif (!$data) {
            $data = null;
        }

        $this->event()
            ->trigger(Db::EVENT_AFTER_FETCH_ROW, array(&$data, $fetch, $offset));

        return $data;
    }

    /**
     * Выборка строчки результата запроса в виде объекта.
     *
     * pg_fetch_object:
     * подстановка null в название класса вызывает ошибку
     * если указан $params, обязательно требуется конструктор, подстановка null не помогает
     *
     * @param null|int $offset смещение
     * @param string|null $className название класса, если не указано, то \stdClass
     * @param array|null $params параметры, передаваемые в конструктор класса
     * @param array|null $types типы полей
     * @return \stdClass|object|null
     * @access public
     */
    public function fetchObject($offset = null, $className = null, array $params = null, array $types = null) {
        if ($className && $params) {
            $data = pg_fetch_object($this->result, $offset, $className, $params);

        } elseif ($className) {
            $data = pg_fetch_object($this->result, $offset, $className);

        } else {
            $data = pg_fetch_object($this->result, $offset);
        }

        if ($data && $types) {
            $data = $this->_unescape($data, $types);

        } elseif (!$data) {
            $data = null;
        }

        $this->event()
            ->trigger(Db::EVENT_AFTER_FETCH_OBJECT, array(&$data, $offset, $className, $params));

        return $data;
    }

    /**
     * Выборка первого значения строки результата запроса.
     *
     * @param null|int $type тип результата
     * @return array|null
     * @throws Exception
     * @access public
     */
    public function fetchOne($type = null) {
        if (!$this->count) {
            return null;
        }

        $data = pg_fetch_array($this->result, 0, PGSQL_NUM);
        $data = ($data) ? $data[0] : null;

        if ($data && $type) {
            $data = $this->_unescape($data, $type);

        } elseif (!$data) {
            $data = null;
        }

        $this->event()
            ->trigger(Db::EVENT_AFTER_FETCH_ONE, array(&$data));

        return $data;
    }

    /**
     * Выборка всех строк результата запроса.
     *
     * @param int $fetch тип представления результата
     * @param array|null $types типы столбцов
     * @return array
     * @throws Exception
     * @access public
     */
    public function fetchAll($fetch = Db::FETCH_ASSOC, array $types = null) {
        $this->rewind();

        $pgfetch = static::_adapterFetch($fetch);
        $data = array();

        if ($fetch == Db::FETCH_ASSOC) {
            $data = pg_fetch_all($this->result);

        } else {
            while ($row = pg_fetch_array($this->result, null, $pgfetch)) {
                $data[] = $row;
            }
        }

        if ($data && $types) {
            foreach ($data as &$row) {
                $row = $this->_unescape($row, $types);
            }
            unset($row);

        } elseif (!$data) {
            $data = array();
        }

        $this->event()
            ->trigger(Db::EVENT_AFTER_FETCH_ALL, array(&$data, $fetch));

        return $data;
    }

    /**
     * Выборка всех строк результата запроса.
     *
     * @param string|null $className название класса, если не указано, то \stdClass
     * @param array|null $params параметры, передаваемые в конструктор класса
     * @param array|null $types типы столбцов
     * @return array
     * @access public
     */
    public function fetchAllObject($className = null, array $params = null, array $types = null) {
        $this->rewind();

        $data = array();

        if ($className && $params) {
            while ($row = pg_fetch_object($this->result, null, $className, $params)) {
                $data[] = $row;
            }

        } elseif ($className) {
            while ($row = pg_fetch_object($this->result, null, $className)) {
                $data[] = $row;
            }

        } else {
            while ($row = pg_fetch_object($this->result, null)) {
                $data[] = $row;
            }
        }

        if ($data && $types) {
            foreach ($data as &$row) {
                $row = $this->_unescape($row, $types);
            }
            unset($row);

        } elseif (!$data) {
            $data = array();
        }

        $this->event()
            ->trigger(Db::EVENT_AFTER_FETCH_ALL_OBJECT, array(&$data, $className, $params));

        return $data;
    }

    /**
     * Выборка всех значений столбца.
     *
     * @param int|string $column индекс или название столбца
     * @param null|int $type тип столбца
     * @return array
     * @throws Exception
     * @access public
     */
    public function fetchAllColumns($column, $type = null) {
        if (is_string($column)) {
            $column = pg_field_num($this->result, $column);
        }

        if ($column < 0 || $column >= $this->countFields) {
            throw new Exception(Exception::STMT_COLUMN_NOT_FOUND, array($column));
        }

        $this->rewind();
        $data = pg_fetch_all_columns($this->result, $column);

        if ($data && $type) {
            $data = $this->_unescape($data, $type);

        } elseif (!$data) {
            $data = array();
        }

        $this->event()
            ->trigger(Db::EVENT_AFTER_FETCH_ALL_COLUMNS, array(&$data, $column));

        return $data;
    }

    /**
     * Выборка всех значений столбца $value с ключами значениями столбца $key.
     *
     * @param int|string $key индекс или название ключевого столбца
     * @param int|string $value индекс или название столбца значения
     * @param null|int $typeKey тип данных ключевого столбца
     * @param null|int $typeValue тип данных столбца значений
     * @return array
     * @throws Exception
     * @access public
     */
    public function fetchAllPairs($key, $value, $typeKey = null, $typeValue = null) {
        if ($this->countFields < 2) {
            throw new Exception(Exception::STMT_COLUMN_FETCH_PAIRS);
        }

        if (is_string($key)) {
            $key = pg_field_num($this->result, $key);
        }

        if ($key < 0 || $key >= $this->countFields) {
            throw new Exception(Exception::STMT_COLUMN_NOT_FOUND, array($key));
        }

        if (is_string($value)) {
            $value = pg_field_num($this->result, $value);
        }

        if ($value < 0 || $value >= $this->countFields) {
            throw new Exception(Exception::STMT_COLUMN_NOT_FOUND, array($value));
        }

        $this->rewind();

        $data = array();

        if ($typeKey && $typeValue) {
            while ($row = pg_fetch_array($this->result, null, PGSQL_NUM)) {
                $data[$this->_unescape($row[$key], $typeKey)] = $this->_unescape($row[$value], $typeValue);
            }

        } elseif ($typeKey) {
            while ($row = pg_fetch_array($this->result, null, PGSQL_NUM)) {
                $data[$this->_unescape($row[$key], $typeKey)] = $row[$value];
            }

        } elseif ($typeValue) {
            while ($row = pg_fetch_array($this->result, null, PGSQL_NUM)) {
                $data[$row[$key]] = $this->_unescape($row[$value], $typeValue);
            }

        } else {
            while ($row = pg_fetch_array($this->result, null, PGSQL_NUM)) {
                $data[$row[$key]] = $row[$value];
            }
        }

        $this->event()
            ->trigger(Db::EVENT_AFTER_FETCH_ALL_PAIRS, array(&$data, $key, $value));

        return $data;
    }

    /**
     * Выборка всех значений результата запроса с ключами значениями столбца $column.
     *
     * @param int|string $column индекс или название ключвого столбца
     * @param int $fetch тип представления данных
     * @param null|int $typeColumn тип данных ключевого столбца
     * @param array|null $types типы столбцов данных
     * @return array
     * @throws Exception
     * @access public
     */
    public function fetchAllAssoc($column, $fetch = Db::FETCH_ASSOC, $typeColumn = null, array $types = null) {
        if ($fetch == Db::FETCH_NUM && is_string($column)) {
            $column = pg_field_num($this->result, $column);
            if ($column == -1) {
                throw new Exception(Exception::STMT_COLUMN_NOT_FOUND, array($column));
            }

        } elseif ($fetch == Db::FETCH_ASSOC && is_int($column)) {
            $column = pg_field_name($this->result, $column);
            if (!$column) {
                throw new Exception(Exception::STMT_COLUMN_NOT_FOUND, array($column));
            }
        }

        $this->rewind();

        $pgfetch = static::_adapterFetch($fetch);
        $data = array();

        if ($typeColumn && $types) {
            while ($row = pg_fetch_array($this->result, null, $pgfetch)) {
                if (!$data && !array_key_exists($column, $row)) {
                    throw new Exception(Exception::STMT_COLUMN_NOT_FOUND, array($column));
                }

                $data[$this->_unescape($row[$column], $typeColumn)] = $this->_unescape($row, $types);
            }

        } elseif ($typeColumn) {
            while ($row = pg_fetch_array($this->result, null, $pgfetch)) {
                if (!$data && !array_key_exists($column, $row)) {
                    throw new Exception(Exception::STMT_COLUMN_NOT_FOUND, array($column));
                }

                $data[$this->_unescape($row[$column], $typeColumn)] = $row;
            }

        } elseif ($types) {
            while ($row = pg_fetch_array($this->result, null, $pgfetch)) {
                if (!$data && !array_key_exists($column, $row)) {
                    throw new Exception(Exception::STMT_COLUMN_NOT_FOUND, array($column));
                }

                $data[$row[$column]] = $this->_unescape($row, $types);
            }

        } else {
            while ($row = pg_fetch_array($this->result, null, $pgfetch)) {
                if (!$data && !array_key_exists($column, $row)) {
                    throw new Exception(Exception::STMT_COLUMN_NOT_FOUND, array($column));
                }

                $data[$row[$column]] = $row;
            }
        }

        $this->event()
            ->trigger(Db::EVENT_AFTER_FETCH_ALL_ASSOC, array(&$data, $column, $fetch));

        return $data;
    }

    /**
     * Текущее значение результата выборки.
     *
     * @return array
     * @access public
     */
    public function current() {
        $data = pg_fetch_array($this->result, $this->offset, static::_adapterFetch($this->defaultFetch));
        return $data;
    }

    /**
     * Текущий ключ.
     *
     * @return int
     * @access public
     */
    public function key() {
        return $this->offset;
    }

    /**
     * Увеличение ключа выборки.
     *
     * @access public
     */
    public function next() {
        ++$this->offset;
    }

    /**
     * Сброс указателя строки результата.
     *
     * @access public
     */
    public function rewind() {
        $this->offset = 0;
        pg_result_seek($this->result, 0);
    }

    /**
     * Проверка смещения.
     *
     * @return bool
     * @access public
     */
    public function valid() {
        return ($this->offset < $this->count);
    }
}