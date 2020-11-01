<?php

namespace TP\Arr;
use TP;



/**
 * Arr
 *
 * Базовый класс типов массивов.
 *
 * @package TP\Arr
 */
abstract class Arr implements \ArrayAccess, \Iterator, \Countable {

    private $IS_LT_ARRAY_TYPE_CLASS = true;

    /**
     * @var int текущая позиция курсора
     */
    protected $position = 0;

    /**
     * @var array массив значений
     */
    protected $array = array();

    /**
     * @var string название класса типа элемента массива
     */
    protected static $typeClass;

    /**
     * Конструктор объекта массива. Преобразование элементов к объектному типу.
     *
     * @param array $array
     * @access public
     */
    public function __construct(array $array = array()) {
        foreach ($array as $value) {
            $this->array[] = new static::$typeClass($value);
        }                   
    }

    /**
     * Преобразование элементов из объектного типа.
     *
     * @return array
     * @access public
     */
    public function toArray() {
        $result = array();

        foreach ($this->array as $k => $v) {
            /* @var $v \TP\Type */
            $result[$k] = $v->val();
        }

        return $result;
    }

    /**
     * Проверка наличия элемента в массиве.
     *
     * @param \TP\Type $value
     * @return bool
     * @throws \TP\Exception\Arr
     */
    public function inArray($value) {
        /* @var \TP\Type $value */
        if (!is_a($value, static::$typeClass)) {
            throw new TP\Exception\Arr(TP\Exception\Arr::TYPE, array(
                get_called_class()
            ));
        }

        $value = $value->val();
        foreach ($this->array as $v) {
            /* @var \TP\Type $v */
            if ($value == $v->val()) {
                return true;
            }
        }

        return false;
    }


    // ArrayAccess

    /**
     * @param $offset
     * @param $value
     * @throws \TP\Exception\Arr
     * @access public
     */
    public function offsetSet($offset, $value) {
        if (!is_a($value, static::$typeClass)) {
            throw new TP\Exception\Arr(TP\Exception\Arr::TYPE, array(
                get_called_class()
            ));
        }

        if (is_null($offset)) {
            $this->array[] = $value;

        } else {
            $this->array[$offset] = $value;
        }
    }

    /**
     * @param $offset
     * @return bool
     * @access public
     */
    public function offsetExists($offset) {
        return isset($this->array[$offset]);
    }

    /**
     * @param $offset
     * @access public
     */
    public function offsetUnset($offset) {
        unset($this->array[$offset]);
    }

    /**
     * @param $offset
     * @return null|mixed
     * @access public
     */
    public function offsetGet($offset) {
        return ((isset($this->array[$offset])) ? $this->array[$offset] : null);
    }

    // Iterator

    /**
     * @access public
     */
    public function rewind() {
        return reset($this->array);
    }

    /**
     * @return mixed
     * @access public
     */
    public function current() {
        return current($this->array);
    }

    /**
     * @return int
     * @access public
     */
    public function key() {
        return key($this->array);
    }

    /**
     * @access public
     */
    public function next() {
        return next($this->array);
    }

    /**
     * @return bool
     * @access public
     */
    public function valid() {
        return ((current($this->array) === false) ? false : true);
    }

    /**
     * @static
     * @param $className
     * @return bool
     * @access public
     */
    public static function isAnArray($className) {
        $vars = get_class_vars($className);

        if (isset($vars['IS_LT_ARRAY_TYPE_CLASS']) && $vars['IS_LT_ARRAY_TYPE_CLASS'] === true) {
            return true;

        } else {
            return false;
        }
    }

    /**
     * @return int
     * @access public
     */
    public function count() {
        return count($this->array);
    }

    /**
     * @return array
     */
    public function getArrayCopy() {
        return $this->array;
    }


    public function merge(Arr $arrObject) {
        if (!is_a($arrObject, get_called_class())) {
            throw new TP\Exception\Arr(TP\Exception\Arr::TYPE, array(
                get_called_class()
            ));
        }

        $this->array = array_merge($this->array, $arrObject->getArrayCopy());
    }

}

class Negativeable extends Arr {
    protected $positionExc = 0;
    protected $arrayExc = array();

    protected $excludeSet = false;

    public function __construct($array = array()) {
        foreach ($array as $value) {
            if ($value<0)
                $this->arrayExc[] = new static::$typeClass(-$value);
            else
                $this->array[] = new static::$typeClass($value);
        }
    }

    public function excludeSet($on = true) {
        $this->excludeSet = $on;
    }

    public function toArray() {
        $result = array();

        if (count($this->array)>0 and count($this->arrayExc)>0) {

            foreach ($this->array as $v) {
                /** @var $v \TP\Type */
                $result[$v->val()] = 1;
            }

            foreach ($this->arrayExc as $v) {
                /** @var $v \TP\Type */
                unset($result[$v->val()]);
            }

            $result = array_keys($result);

        } elseif (count($this->array)>0) {
            foreach ($this->array as $v) {
                /** @var $v \TP\Type */
                $result[] = $v->val();
            }

        } elseif (count($this->arrayExc)>0) {
            foreach ($this->arrayExc as $v) {
                /** @var $v \TP\Type */
                $result[] = -$v->val();
            }
        }

        return $result;
    }

    // ArrayAccess

    public function offsetSet($offset, $value) {
        if (!($value instanceof static::$typeClass))
            throw new \TP\Exception(\TP\Exception\Arr::TYPE, array(
                get_called_class()
            ));

        if ($this->excludeSet) {
            if (is_null($offset))
                $this->arrayExc[] = $value;
            else
                $this->arrayExc[$offset] = $value;
        } else {
            if (is_null($offset))
                $this->array[] = $value;
            else
                $this->array[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        if ($this->excludeSet)
            return isset($this->arrayExc[$offset]);
        else
            return isset($this->array[$offset]);
    }

    public function offsetUnset($offset) {
        if ($this->excludeSet)
            unset($this->arrayExc[$offset]);
        else
            unset($this->array[$offset]);
    }

    public function offsetGet($offset) {
        if ($this->excludeSet)
            return isset($this->arrayExc[$offset]) ? $this->arrayExc[$offset] : null;
        else
            return isset($this->array[$offset]) ? $this->array[$offset] : null;
    }

    // Iterator

    public function rewind() {
        if ($this->excludeSet)
            $this->positionExc = 0;
        else
            $this->position = 0;
    }

    public function current() {
        if ($this->excludeSet)
            return $this->arrayExc[$this->positionExc];
        else
            return $this->array[$this->position];
    }

    public function key() {
        if ($this->excludeSet)
            return $this->positionExc;
        else
            return $this->position;
    }

    public function next() {
        if ($this->excludeSet)
            ++$this->positionExc;
        else
            ++$this->position;
    }

    public function valid() {
        if ($this->excludeSet)
            return isset($this->arrayExc[$this->positionExc]);
        else
            return isset($this->array[$this->position]);
    }

    public static function isAnArray($className) {
        $vars = get_class_vars($className);

        if (isset($vars['IS_LT_ARRAY_TYPE_CLASS']) and $vars['IS_LT_ARRAY_TYPE_CLASS']===true)
            return true;
        else
            return false;
    }

    public function count() {
        if ($this->excludeSet)
            return count($this->arrayExc);
        else
            return count($this->array);
    }
}

/**
 * Int
 *
 * Массив с целочисленными элементами.
 *
 * @package TP\Arr
 */
class TInt extends Arr {

    /**
     * @var string тип элемента массива
     */
    protected static $typeClass = 'TP\TInt';
}



/**
 * UInt4Arr
 *
 * Массив с целочисленными положительными элементами размером 4 байта.
 *
 * @todo непонятно по какой причине названо TP\Arr\UInt4Arr а не TP\Arr\UInt4 (по аналогии с TP\Arr\TInt)
 * @package TP\Arr
 */
class UInt4Arr extends Arr {

    /**
     * @var string тип элемента массива
     */
    protected static $typeClass = 'TP\UInt4';
}



/**
 * UInt2Arr
 *
 * Массив с целочисленными положительными элементами размером 2 байта.
 *
 * @todo непонятно по какой причине названо TP\Arr\UInt2Arr а не TP\Arr\UInt2 (по аналогии с TP\Arr\TInt)
 * @package TP\Arr
 */
class UInt2Arr extends Arr {

    /**
     * @var string тип элемента массива
     */
    protected static $typeClass = 'TP\UInt2';
}



/**
 * WeekDay
 *
 * Массив дней недели.
 *
 * @package TP\Arr
 */
class WeekDay extends Arr {

    /**
     * @var string тип элемента массива
     */
    protected static $typeClass = 'TP\WeekDay';
}



/**
 * StrArr
 *
 * Массив строк.
 *
 * @package TP\Arr
 */
class StrArr extends Arr {

    /**
     * @var string тип элемента массива
     */
    protected static $typeClass = 'TP\Text\Plain';
}



/**
 * Email
 *
 * Массив email.
 *
 * @package TP\Arr
 */
class Email extends Arr {

    /**
     * @var string тип элемента массива
     */
    protected static $typeClass = 'TP\Str\Email';

    /**
     * Конструктор объекта массива. Преобразование элементов к объектному типу.
     *
     * @param array $array
     * @access public
     */
    public function __construct(array $array = array()) {
        foreach ($array as $name => $email) {
            if (is_string($name)) {
                $this->array[] = new static::$typeClass($email, $name);

            } else {
                if (preg_match('/^(.*)<(.+)>$/iu', $email, $m)) {
                    $this->array[] = new static::$typeClass($m[2], trim($m[1]));

                } else {
                    $this->array[] = new static::$typeClass($email);
                }
            }
        }
    }
}



/**
 * Url
 *
 * Массив ссылок.
 *
 * @package TP\Arr
 */
class Url extends Arr {

    /**
     * @var string тип элемента массива
     */
    protected static $typeClass = 'TP\Str\Url';
}



/**
 * Phone
 *
 * Массив телефонов.
 *
 * @package TP\Arr
 */
class Phone extends Arr {

    /**
     * @var string тип элемента массива
     */
    protected static $typeClass = 'TP\Str\Phone';
}