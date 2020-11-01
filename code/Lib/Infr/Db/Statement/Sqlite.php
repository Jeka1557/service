<?php


namespace Lib\Infr\Db\Statement;

use \Lib\Infr\Db;
use \Lib\Infr\Db\Statement;
use \Lib\Infr\Db\Adapter;


/**
 * Sqlite
 *
 * @package LSF2\Infr\Db\Statement
 */
final class Sqlite extends Statement {

    static protected $useCP1251 = false;

    /**
     * @var array соответствие типов результата
     */
    protected static $fetch = array(
        Db::FETCH_BOTH => SQLITE3_BOTH,
        Db::FETCH_ASSOC => SQLITE3_ASSOC,
        Db::FETCH_NUM => SQLITE3_NUM
    );

    /**
     * @param Adapter $adapter
     * @param resource $result
     * @throws Exception
     * @access public
     */
    public function __construct(Adapter $adapter, $result) {
        if ( !($result instanceof \SQLite3Result)) {
            throw new Exception(Exception::STMT_RESOURCE_TYPE);
        }

        $this->adapter = $adapter;
        $this->result = $result;
        $this->countFields = $result->numColumns();
        $this->affected = $adapter->affectedRows();


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
        $this->result->finalize();
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

        if ($offset) {
            while($offset != $this->offset) {
                $this->result->fetchArray();
                $this->next();
            }
        }

        $data = $this->result->fetchArray(static::_adapterFetch($fetch));
        $this->next();

        if ($data && $types) {
            $data = $this->_unescape($data, $types);
        } elseif (!$data) {
            $data = null;
        }

        if (self::$useCP1251 and $data)
            $this->convert1251($data);

        $this->event()
            ->trigger(Db::EVENT_AFTER_FETCH_ROW, array(&$data, $fetch, $offset));

        return $data;
    }

    /**
     * Выборка строчки результата запроса в виде объекта.
     *
     * @param null|int $offset смещение
     * @param string|null $className название класса, если не указано, то \stdClass
     * @param array|null $params параметры, передаваемые в конструктор класса
     * @param array|null $types типы полей
     * @return \stdClass|object|null
     * @access public
     */
    public function fetchObject($offset = null, $className = null, array $params = null, array $types = null) {
        throw new Exception('Method '.__METHOD__.' is not defined for SQLite statement');
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
        $this->rewind();

        $data = $this->result->fetchArray(SQLITE3_NUM);

        if ($data && $type) {
            $data = $this->_unescape($data, $type);

        } elseif (!$data) {
            $data = null;
        }

        if (self::$useCP1251 and $data)
            $this->convert1251($data);

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
        $data = array();

        while ($row = $this->result->fetchArray(static::_adapterFetch($fetch))) {
            $data[] = $row;
        }

        if ($data && $types) {
            foreach ($data as &$row) {
                $row = $this->_unescape($row, $types);
            }
            unset($row);

        } elseif (!$data) {
            $data = array();
        }

        if (self::$useCP1251 and $data)
            foreach ($data as &$row) {
                $this->convert1251($row);
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
        return (object)$this->fetchAll();
    }

    /**
     * Выборка всех значений столбца.
     *
     * @param int|string $column индекс или название столбца
     * @param null|int $type тип столбца
     * @throws Exception
     * @access public
     */
    public function fetchAllColumns($column, $type = null) {
        throw new Exception('Method '.__METHOD__.' is not defined for SQLite statement');
    }

    /**
     * Выборка всех значений столбца $value с ключами значениями столбца $key.
     *
     * @param int|string $key индекс или название ключевого столбца
     * @param int|string $value индекс или название столбца значения
     * @param null|int $typeKey тип данных ключевого столбца
     * @param null|int $typeValue тип данных столбца значений
     * @throws Exception
     * @access public
     */
    public function fetchAllPairs($key, $value, $typeKey = null, $typeValue = null) {
        throw new Exception('Method '.__METHOD__.' is not defined for SQLite statement');
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
        throw new Exception('Method '.__METHOD__.' is not defined for SQLite statement');
    }

    /**
     * Текущее значение результата выборки.
     *
     * @return array
     * @access public
     */
    public function current() {
        $data = $this->result->fetchArray(static::_adapterFetch($this->defaultFetch));
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
        $this->result->reset();
    }

    /**
     * Проверка смещения.
     *
     * @return bool
     * @access public
     */
    public function valid() {
        return ($this->offset>0);
    }


    protected function convert1251(&$row) {

        foreach ($row as $name => $value) {
            // Хак конечно, номальное решение перевести все на utf и вообще не использовать перекодировку
            if (is_null($value) or $name == 'file_body')
                continue;

            $row[$name] = iconv('UTF-8', 'WINDOWS-1251//TRANSLIT', $value);
        }
    }

    static public function setCP1251() {
        self::$useCP1251 = true;
    }
}