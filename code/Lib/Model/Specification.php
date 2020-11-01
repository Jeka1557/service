<?php

namespace Lib\Model;

use Lib\LSObject;
use Lib\Model\Exception as Except;


class Specification extends LSObject {
    protected static $properties;
    protected $values = array();
    protected $invalidProperties = array();

    public function isValid() {
        if ($this->invalidProperties) {
            return false;
        }

        return true;
    }

    public static function getProperties() {
        return static::$properties;
    }

    public function __construct($data = array()) {
        foreach (static::$properties as $name => $typeClass) {
            if (array_key_exists($name, $data)) {
                $this->values[$name] = $this->cast($name, $data[$name]);

            } elseif ($typeClass[0] == '#') {
                $this->invalidProperties[$name] = 1;
            }
        }
    }

    public static function getPropertyType($name) {
        if (!isset(static::$properties[$name])) {
            throw new Except('Invalid class property ' . get_called_class() . '->' . $name);
        }

        return static::$properties[$name];
    }

    public static function cast($name, $value) {
        $typeClass = static::getPropertyType($name);
        $nullAllow = true;

        if ($typeClass[0] == '#') {
            $typeClass = ltrim($typeClass, '#');
            $nullAllow = false;
        }

        if ($value === null) {
            if ($nullAllow) {
                return null;

            } else {
                throw new Except('Null values are not allowed for propery ' . $name);
            }

        } else {
            if (is_object($value)) {
                if (!($value instanceof $typeClass)) {
                    throw new Except("The property $name is not instance of $typeClass");

                } else {
                    return $value;
                }

            } else {
                return new $typeClass($value);
            }
        }

    }

    public function __set($name, $value) {
         $this->values[$name] = $this->cast($name, $value);
         unset($this->invalidProperties[$name]);
    }

    public function __get($name) {
        if (!$this->isValid()) {
            throw new Except('Specification is invalid ' . $this->values[$name]);
        }

        if (!isset(static::$properties[$name])) {
            throw new Except('Invalid class property ' . get_called_class() . '->' . $name);
        }

        // ���-�� ���� ��������� ���� ������, �������� ��������� � ������������
        if (!isset($this->values[$name])) {
            return null;
        }

        return $this->values[$name];
    }

    public function isNull($name) {
        if (!$this->isValid()) {
            throw new Except('Specification is invalid');
        }

        if (!isset(static::$properties[$name])) {
            throw new Except('Invalid class property ' . get_called_class() . '->' . $name);
        }

        // ���-�� ���� ��������� ���� ������, �������� ��������� � ������������
        if (!isset($this->values[$name]) || $this->values[$name] === null) {
            return true;

        } else {
            return false;
        }
    }

    /**
     * �������������� ������� � �������.
     *
     * @param array|null $varsArray ������ ����� ������������, ������� ������������������������� � ������
     * @return array
     * @access public
     */
    public function toArray(array $varsArray = null) {
        if (!$this->isValid()) {
            return array();
        }

        $data = array();
        foreach ($this->values as $name => $value) {
            if ($varsArray && !in_array($name, $varsArray)) {
                continue;
            }

            $data[$name] = null;
            if ($value !== null) {
                if (method_exists($value, 'toArray')) {
                    /* @var \TP\Arr\Arr|\Lib\Model\Value|\Lib\Model\Collection $value */
                    $data[$name] = $value->toArray();

                } elseif ($value instanceof \TP\Type) {
                    /* @var \TP\Type $value */
                    $data[$name] = $value->val();

                } else {
                    $data[$name] = strval($value);
                }
            }
        }

        return $data;
    }

    /**
     * �������������� ������� � ������ JSON.
     *
     * @return string
     * @access public
     */
    public function toJSON() {
        return json_encode($this->toArray());
    }
}