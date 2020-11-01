<?php

namespace Lib\Model;
use Lib\LSObject;
use Lib\Infr\Utility\Encoding;
use Lib\Model\Collection;




abstract class Value extends LSObject {

    /**
     * Конструктор объекта.
     *
     * @static
     * @return self
     * @access public
     */
    public static function newFromArray($data = []) {
        $class = get_called_class();
        /* @var self $entity */
        $entity = new $class();
        return $entity;
    }

    /**
     * Проверка наличия значения поля.
     *
     * Значения нет, если NULL.
     *
     * @param $name - название поля
     * @return bool true, если значение не установлено
     * @access public
     */
    public function isNotSet($name) {
        $name = '_' . $name;
        return is_null($this->$name);
    }

    /**
     * Проверка установки значения поля.
     *
     * Поле считается не заполненым если NULL или пустая строка.
     *
     * @param $name
     * @return bool
     * @access public
     */
    public function isEmpty($name) {
        $name = '_' . $name;
        return (is_null($this->$name) || $this->$name === '');
    }

    /**
     * Установка значения свойства.
     *
     * Неприменимо.
     *
     * @param $name
     * @param $value
     * @throws \Exception
     * @access public
     */
    public function __set($name, $value) {
        throw new \Exception('Set not implemented');
    }

    /**
     * Получение значения свойства.
     *
     * Если поле содержит пустую строку, то выполняется попытка "ленивой" установки значения.
     * Если в результате значение содержит пустую строку, то результат NULL.
     *
     * Изначально установленное значение NULL вызывает исключение.
     *
     * @param $name - название поля
     * @return mixed
     * @throws \Exception
     * @access public
     */
    public function __get($name) {
        $pName = '_' . $name;

        if (!isset($this->$pName)) {
            throw new \Exception("Object property ".get_called_class()."->{$name} is not initialized.");
        }

        if ($this->$pName === '') {
            $this->_lazyLoad($name);
            return (($this->$pName === '') ? null : $this->$pName);
        }

        return $this->$pName;
    }

    /**
     * Преобразование объекта к строке JSON.
     *
     * @final
     * @return string
     * @access public
     */
    final public function toJSON() {
        return json_encode($this->toArray());
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
        $vars = get_object_vars($this);

        foreach ($vars as $m => $v) {
            if ($m[0] != '_') {
                continue;
            }

            if (!isset($this->$m)) {
                continue;
            }

            $key = substr($m, 1);

            if ($varsArray && !in_array($key, $varsArray)) {
                continue;
            }

            $v = $this->$key;

            if (is_object($v)) {
                if (method_exists($v, 'toArray')) {
                    /* @var \TP\Arr\Arr|Value|Collection $v */
                    $v = $v->toArray();

                } elseif ($v instanceof \TP\Type) {
                    /* @var \TP\Type $v */
                    $v = $v->val();

                } else {
                    $v = strval($v);
                }
            }

            $data[$key] = $v;
        }

        return $data;
    }

    /**
     * Проверка значения на соответствие типу.
     *
     * @static
     * @param mixed $value значение
     * @param string|object $type обхект или название класса типа
     * @param bool $notNull значение NULL не допустимо
     * @param array $args массив значений, передаваемых в аргументы метода cast() или val() у типа
     * @return string
     * @throws \Exception
     * @access protected
     */
    protected static function castVar($value, $type, $notNull = true, array $args = array()) {
        if (!$notNull && (is_null($value) || $value === '')) {
            return '';

        } else {
            if (is_object($value)) {
                if (!($value instanceof $type)) {
                    throw new \Exception("Invalid value type");
                }

                /* @var \TP\Type $value */

                if ($value instanceof \TP\Type) {
                    return (($args) ? call_user_func_array(array($value, 'val'), $args) : $value->val());

                } else {
                    return $value;
                }

            } else {
                if (is_subclass_of($type, '\TP\Type')) {
                    /* @var \TP\Type $type */

                    if ($args) {
                        array_unshift($args, $value);
                        return call_user_func_array(array($type, 'cast'), $args);
                    }

                    return $type::cast($value);

                } else {
                    throw new \Exception("Value '" . var_export($value, true) . "' is invalid for {$type}");
                }
            }
        }
    }

    /**
     * Функция предназначена для "ленивой" загрузки свойств.
     * Вызывается при обращении к не инициализированному свойству (указано значение пустая строка),
     * в параметр передается его название.
     *
     * @param string $name
     */
    protected function _lazyLoad($name) {

    }
}
