<?php

namespace Lib\Infr;
use \Lib\Infr\DSN\Exception as Except;
use \Lib\Model\Specification;



abstract class DSN extends Specification {

    /**
     * �������� ��������.
     */
    const DRIVER = null;

    /**
     * @param array $data ��������� DSN
     * @throws Except
     * @access public
     */
    public function __construct(array $data) {
        parent::__construct($data);

        if (!$this->isValid()) {
            throw new Except(Except::DSN_INVALID);
        }
    }

    /**
     * �������������� ���������.
     *
     * @param $name
     * @param $value
     * @throws Except
     * @access public
     */
    public function __set($name, $value) {
        throw new Except(Except::SET_BAN);
    }

    /**
     * @param string $name
     * @return object|null
     * @throws Except
     */
    public function __get($name) {
        if (!isset(static::$properties[$name])) {
            throw new Except(Except::DSN_PROP_UNDEF, array($name));
        }

        return ((isset($this->values[$name])) ? $this->values[$name] : null);
    }

    /**
     * ����������� DSN.
     *
     * @final
     * @static
     * @param string $driver
     * @param array $options
     * @return mixed
     * @throws Except
     * @access public
     */
    final public static function create($driver, array $options = array()) {
        $class = __CLASS__ . '\\' . ucfirst(strtolower($driver));

        /*if (!class_exists($class)) {
            throw new Except(Except::DRIVER_UNDEF, array($driver));
        }*/

        return new $class($options);
    }

    /**
     * ��������� DSN ������ ����������� ��� PDO.
     *
     * ���������� �������������� � ��������.
     *
     * @return string
     * @throws Except
     * @access public
     */
    public function pdo() {
        throw new Except(Except::METHOD_UNDEF);
    }
}