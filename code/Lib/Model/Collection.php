<?php

namespace Lib\Model;
use Lib\LSObject;
use Lib\Infr\Utility\Encoding;
use Lib\Model\Collection\Exception as Except;



class Collection extends LSObject implements \ArrayAccess, \Iterator, \Countable {

    /**
     * @var int текущая позиция курсора
     */
    protected $position = 0;

    /**
     * @var array массив элементов коллекции
     */
    protected $array = array();

    /**
     * @var string название классаэлемента коллекции
     */
    protected $entityClass;

    /**
     * @var int смещение
     */
    protected $dataOffset = 0;

    /**
     * @var int предел
     */
    protected $dataLimit;

    /**
     * @var int количество элементов коллекции
     */
    protected $dataCount;

    /**
     * @var bool признак произвольной сортировки
     */
    protected $orderRandom;

    /**
     * @var array параметры сортировки
     */
    protected $orderProperties = array();

    /**
     * @var array параметры группировки
     */
    protected $groupProperties = array();


    public function export() {
        $data = array();

        if (!method_exists($this->entityClass, 'export')) {
            throw new Except(Except::METHOD_UNDEF, array('export', $this->entityClass));
        }

        foreach ($this->array as $k => $v) {
            $data[$k] = $v->export();
        }

        return $data;
    }


    /**
     * Непонятно назначение метода.
     * Из массива сущностей делать коллекцию,
     * почему бы сразу не использовать коллекцию вместо массива?
     *
     * @static
     * @param array $array
     * @return array|Collection
     * @deprecated
     */
    public static function newFromArray(array $array) {
        $collection = new static;

        foreach ($array as $v) {
            $collection[] = $v;
        }

        return $collection;
    }

    /**
     * Конструктор коллекции.
     *
     * @param \TP\UInt2|null $dataLimit предел
     * @param \TP\UInt4|null $dataOffset смещение
     * @param null|string $entityClass название класс эелемента коллекции
     * @access public
     */
    public function __construct(\TP\UInt2 $dataLimit = null, \TP\UInt4 $dataOffset = null, $entityClass = null) {
        if ($dataOffset !== null) {
            $this->dataOffset = $dataOffset->val();
        }

        if ($dataLimit !== null) {
            $this->dataLimit = $dataLimit->val();
        }

        if ($entityClass !== null) {
            if (!is_a($entityClass, $this->entityClass, true)) {
                throw new Except(Except::OBJECT_PARENT);
            }

            $this->entityClass = $entityClass;
        }
    }

    /**
     * Преобразование объекта к массиву.
     *
     * @param array|null $varsArray список свойств сущности, которые необходимо преобразовывать в массив
     * @return array
     * @access public
     */
    public function toArray(array $varsArray = null) {
        $data = array();

        if (!method_exists($this->entityClass, 'toArray')) {
            throw new Except(Except::METHOD_UNDEF, array('toArray', $this->entityClass));
        }

        foreach ($this->array as $k => $v) {
            /* @var Value $v */
            $data[$k] = $v->toArray($varsArray);
        }

        return $data;
    }

    /**
     * Преобразование объекта к строке JSON.
     *
     * @final
     * @param array|null $varsJson список свойств сущности, которые необходимо преобразовывать в JSON
     * @return string
     * @access public
     */
    final public function toJSON(array $varsJson = null, $jsonOptions = 0) {
        return json_encode($this->toArray($varsJson), $jsonOptions);
    }

    /**
     * Название класса элемента коллекции.
     *
     * @final
     * @return mixed
     * @access public
     */
    final public function getEntityClassName() {
        return $this->entityClass;
    }

    /**
     * Предел.
     *
     * @final
     * @return int|null
     * @access public
     */
    final public function getDataLimit() {
        return $this->dataLimit;
    }

    /**
     * Смещение.
     *
     * @final
     * @return int
     * @access public
     */
    final public function getDataOffset() {
        return $this->dataOffset;
    }

    /**
     * Возравщает массив значений поля $property всех элементов
     *
     * @param string $property имя свойства элемента коллекции
     * @param bool $unique учитывать уникальность значений
     * @return array
     * @access public
     */
    public function extract($property, $unique = true) {
        $items = array();

        foreach ($this as $item) {
            $items[] = $item->$property;
        }

        if ($unique) {
            $items = array_unique($items, SORT_REGULAR);
        }

        return $items;
    }

    /**
     * Добавление сортировки по свойству.
     *
     * @param \TP\Str\WordEn $property
     * @param bool $asc
     * @access public
     */
    public function setSortOrder(\TP\Str\WordEn $property, $asc = true) {
        $this->orderProperties = array();
        $this->_addSortOrder($property, $asc);
    }

    /**
     * Получение параметров сортировки.
     *
     * @return array
     * @access public
     */
    public function getSortOrder() {
        // Это кривота, не нужно это использовать.
        if ($this->orderRandom) {
            return array('random()');
        }

        return $this->orderProperties;
    }

    /**
     * @depricated  Если кому-то понадобиться такая логика нужно ее проработать со мной (Jeka)
     * Иначе нельзя использовать этот метод.
     *
     * @return void
     * @access public
     */
    public function setRandomSortOrder() {
        $this->orderRandom = true;
    }

    /**
     * Функция множественной сортировки.
     *
     * @param $order
     * @return void
     * @access public
     */
    public function setMultipleSortOrder($order) {
        $this->orderProperties = array();

        foreach ($order as $_k => $_v) {
            if (is_string($_k)) {
                $this->_addSortOrder(new \TP\Str\WordEn($_k), (bool) $_v);

            } else {
                $this->_addSortOrder(new \TP\Str\WordEn($_v));
            }
        }
    }

    /**
     * Установка количества элементов коллекции.
     *
     * @param $count
     * @return void
     * @access public
     */
    public function setDataCount($count) {
        $this->dataCount = (int) $count;
    }

    /**
     * Получение количества элементов коллекции.
     *
     * @return int
     * @throws Except
     * @access public
     */
    public function getDataCount() {
        if ($this->dataCount === null) {
            throw new Except(Except::COUNT_UNDEF);
        }

        return $this->dataCount;
    }




    /**
     * @param $name
     * @throws Except
     */
    public function addGroupProperty($name) {
        if (!property_exists($this->entityClass, '_' . $name)) {
            throw new Except(Except::PROP_UNDEF, array($name, $this->entityClass));
        }

        if (!isset($this->groupProperties[$name])) {
            if ($this->array) {
                throw new Except(Except::GROUP_NOT_IMPLEMENTED);

            } else {
                $this->groupProperties[$name] = array();
            }
        }
    }

    /**
     * @param $name
     * @return array
     * @throws Except
     */
    public function getGroupProperty($name) {
        if (!isset($this->groupProperties[$name])) {
            throw new Except(Except::GROUP_UNDEF);
        }

        return array_keys($this->groupProperties[$name]);
    }

    /**
     * Функция возвращает первую найденую сущность, удовлетворяющую условие.
     *
     * @param $propertyName
     * @param $value
     * @return null
     * @deprecated использовалась для получения элемента коллекции по id, но правильнее иметь коллекцию с ключем по id и работать как с массивом
     */
    public function findFirst($propertyName, $value) {
        $result = null;

        foreach ($this->array as $entity) {
            if ($entity->$propertyName == $value) {
                $result = $entity;
                break;
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws Except
     */
    public function newEntityFromArray(array $data) {
        if (method_exists($this->entityClass, 'newFromArray')) {
            return call_user_func_array(array($this->entityClass, 'newFromArray'), array($data));

        } else {
            throw new Except(Except::METHOD_UNDEF, array('newFromArray', $this->entityClass));
        }
    }

    /**
     * Добавление значения в конец списка.
     *
     * @param mixed $value значение
     * @throws Except
     * @access public
     */
    public function append($value) {
        $this->offsetSet(null, $value);
    }

    /**
     * Добавление значения в начало списка.
     *
     * @param mixed $value значение
     * @throws Except
     * @access public
     */
    public function prepend($value) {
        if (!is_object($value)) {
            throw new Except(Except::NOT_OBJECT);
        }

        if (!($value instanceof $this->entityClass)) {
            throw new Except(Except::OBJECT_PARENT);
        }

        foreach ($this->groupProperties as $k => $v) {
            if (!is_null($value->$k) && $value->$k != '') {
                $this->groupProperties[$k][$value->$k] = 1;
            }
        }

        array_unshift($this->array, $value);
    }




    /**
     * Функции интерфейса ArrayAccess
     */

    /**
     * Присваивание значения указанному смещению (ключу).
     *
     * @param mixed $offset смещение
     * @param mixed $value значение
     * @throws Except
     * @access public
     */
    public function offsetSet($offset, $value) {
        if (!is_object($value)) {
            throw new Except(Except::NOT_OBJECT);
        }

        if (!($value instanceof $this->entityClass)) {
            throw new Except(Except::OBJECT_PARENT);
        }

        foreach ($this->groupProperties as $k => $v) {
            if (!is_null($value->$k) && $value->$k != '') {
                $this->groupProperties[$k][$value->$k] = 1;
            }
        }

        if ($offset === null) {
            $this->array[] = $value;

        } else {
            $this->array[$offset] = $value;
        }
    }

    /**
     * Проверка наличия смещения (ключа).
     *
     * @param mixed $offset смещение
     * @return bool
     * @access public
     */
    public function offsetExists($offset) {
        return (isset($this->array[$offset]));
    }

    /**
     * Удаление значения по заданному смещению (ключу).
     *
     * @param mixed $offset смещение
     * @return void
     * @access public
     */
    public function offsetUnset($offset) {
        unset($this->array[$offset]);
    }

    /**
     * Получение значения по смещению (ключу).
     *
     * @param mixed $offset смещение
     * @return null|mixed
     */
    public function offsetGet($offset) {
        return ((isset($this->array[$offset])) ? $this->array[$offset] : null);
    }







    /**
     * Функции интерфейса Iterator
     */

    /**
     * Возврат итератора на первый элемент.
     *
     * @return mixed
     * @access public
     */
    public function rewind() {
        return reset($this->array);
    }

    /**
     * Текущий элемент.
     *
     * @return mixed
     * @access public
     */
    public function current() {
        return current($this->array);
    }

    /**
     * Ключ текущего элемента.
     *
     * @return mixed
     * @access public
     */
    public function key() {
        return key($this->array);
    }

    /**
     * Переход к следующему элементу.
     *
     * @return void
     * @access public
     */
    public function next() {
        return next($this->array);
    }

    /**
     * Проверка корректности позиции.
     *
     * @return bool
     * @access public
     */
    public function valid() {
        return ((current($this->array) === false) ? false : true);
    }

    /**
     * Количество записей в коллекции.
     *
     * @return int
     * @access public
     */
    public function count() {
        return count($this->array);
    }

    /**
     * @param \TP\Str\WordEn $property
     * @return \TP\Str\WordEn
     */
    protected function _mapSortOrder(\TP\Str\WordEn $property) {
        return $property;
    }

    /**
     * Добавление сортировки по свойству.
     *
     * @param \TP\Str\WordEn $property
     * @param bool $asc
     * @throws Except
     */
    protected function _addSortOrder(\TP\Str\WordEn $property, $asc = true) {
        if (!property_exists($this->entityClass, '_' . $this->_mapSortOrder($property))) {
            throw new Except(Except::PROP_UNDEF, array($property, $this->entityClass));
        }

        $this->orderProperties[(string) $property] = ($asc ? 'ASC' : 'DESC');
    }
}