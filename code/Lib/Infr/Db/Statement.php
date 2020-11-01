<?php

namespace Lib\Infr\Db;
use \Lib\Infr\Db;
use \Lib\Infr\Db\Adapter;
use \Lib\Infr\Db\Statement\Exception;

/**
 * Statement
 *
 * @package LSF2\Infr\Db
 */
abstract class Statement implements \Iterator {

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var resource ресурс результата запроса
     */
    protected $result;

    /**
     * @var int смещение указателя
     */
    protected $offset = 0;

    /**
     * @var int количество строк
     */
    protected $count = 0;

    /**
     * @var int количество затронутых запросом записей
     */
    protected $affected = 0;

    /**
     * @var int количество столбцов
     */
    protected $countFields = 0;

    /**
     * @var int тип результата по умолчанию
     */
    protected $defaultFetch = Db::FETCH_ASSOC;

    /**
     * @var array соответствие типов результата
     */
    protected static $fetch = array();

    /**
     * Выборка строчки результата запроса в виде массива.
     *
     * @abstract
     * @param int $fetch тип представления результата
     * @param null|int $offset смещение
     * @param array|null $types типы полей
     * @return array|null
     * @access public
     */
    abstract public function fetchRow($fetch = Db::FETCH_ASSOC, $offset = null, array $types = null);

    /**
     * Выборка строчки результата запроса в виде объекта.
     *
     * @abstract
     * @param null|int $offset смещение
     * @param string|null $className название класса, если не указано, то \stdClass
     * @param array|null $params параметры, передаваемые в конструктор класса
     * @param array|null $types типы полей
     * @return \stdClass|object|null
     * @access public
     */
    abstract public function fetchObject($offset = null, $className = null, array $params = null, array $types = null);

    /**
     * Выборка первого значения строки результата запроса.
     *
     * @abstract
     * @param null|int $type тип результата
     * @return array|null
     * @access public
     */
    abstract public function fetchOne($type = null);

    /**
     * Выборка всех строк результата запроса.
     *
     * @abstract
     * @param int $fetch тип представления результата
     * @param array|null $types типы столбцов
     * @return array
     * @access public
     */
    abstract public function fetchAll($fetch = Db::FETCH_ASSOC, array $types = null);

    /**
     * Выборка всех строк результата запроса.
     *
     * @abstract
     * @param string|null $className название класса, если не указано, то \stdClass
     * @param array|null $params параметры, передаваемые в конструктор класса
     * @param array|null $types типы столбцов
     * @return array
     * @access public
     */
    abstract public function fetchAllObject($className = null, array $params = null, array $types = null);

    /**
     * Выборка всех значений столбца.
     *
     * @abstract
     * @param int|string $column индекс или название столбца
     * @param null|int $type тип столбца
     * @return array
     * @access public
     */
    abstract public function fetchAllColumns($column, $type = null);

    /**
     * Выборка всех значений столбца $value, с ключами значениями столбца $key.
     *
     * @abstract
     * @param int|string $key индекс или название ключевого столбца
     * @param int|string $value индекс или название столбца значения
     * @param null|int $typeKey тип данных ключевого столбца
     * @param null|int $typeValue тип данных столбца значений
     * @return array
     * @access public
     */
    abstract public function fetchAllPairs($key, $value, $typeKey = null, $typeValue = null);

    /**
     * Выборка всех значений результата запроса с ключами значениями столбца $column.
     *
     * @abstract
     * @param int|string $column индекс или название ключвого столбца
     * @param int $fetch тип представления данных
     * @param null|int $typeColumn тип данных ключевого столбца
     * @param array|null $types типы столбцов данных
     * @return array
     * @access public
     */
    abstract public function fetchAllAssoc($column, $fetch = Db::FETCH_ASSOC, $typeColumn = null, array $types = null);

    /**
     * Установка типа результата по умолчанию.
     *
     * @final
     * @param int $fetch тип представления данных
     * @return Statement
     * @throws Exception
     * @access public
     */
    final public function setFetchDefault($fetch) {
        if (!isset(static::$fetch[$fetch])) {
            throw new Exception(Exception::STMT_FETCH_TYPE);
        }

        $this->defaultFetch = $fetch;
        return $this;
    }

    /**
     * Количество записей.
     *
     * @final
     * @return int
     * @access public
     */
    final public function rowCount() {
        return $this->count;
    }

    /**
     * Количество затронутых запросом записей.
     *
     * @final
     * @return int
     * @access public
     */
    final public function rowsAffected() {
        return $this->affected;
    }

    /**
     * Обработчик событий.
     *
     * @final
     * @return Event
     * @access public
     */
    final public function event() {
        static $event;
        if ($event === null) {
            $event = new Event();
        }

        return $event;
    }

    /**
     * Преобразование результата запроса к указанным типам.
     *
     * @final
     * @param mixed $data
     * @param int|array|null $types
     * @return mixed
     * @access protected
     */
    final protected function _unescape($data, $types) {
        if (!$types) {
            return $data;
        }

        if (is_array($data)) {
            if (is_array($types)) {
                foreach ($types as $field => $type) {
                    if (isset($data[$field])) {
                        $data[$field] = $this->adapter->unescape($data[$field], $type);
                    }
                }

            } else {
                foreach ($data as $k => $v) {
                    $data[$k] = $this->adapter->unescape($v, $types);
                }
            }

        } elseif (is_object($data)) {
            if (is_array($types)) {
                foreach ($types as $field => $type) {
                    if (isset($data->$field)) {
                        $data->$field = $this->adapter->unescape($data->$field, $type);
                    }
                }

            } else {
                foreach ($data as $k => $v) {
                    $data->$k = $this->adapter->unescape($v, $types);
                }
            }

        } else {
            $data = $this->adapter->unescape($data, $types);
        }

        return $data;
    }

    /**
     * Соответствие типа адаптера.
     *
     * @final
     * @static
     * @param int $fetch тип представления результата
     * @return int
     * @throws Exception
     * @access public
     */
    final protected static function _adapterFetch($fetch) {
        if (!isset(static::$fetch[$fetch])) {
            throw new Exception(Exception::STMT_FETCH_TYPE);
        }

        return static::$fetch[$fetch];
    }
}