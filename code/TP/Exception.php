<?php

namespace TP\Exception;
use TP;



/**
 * Set
 *
 * Исключение типа Set
 *
 * @package TP\Exception
 */
class Set extends TP\Exception {

    /**
     * Значение не содержится в списке допустимых значений
     */
    const TYPE = 1;

    protected $_messages = array(
        self::TYPE => '%s: value: \'%s\' is not fit for set type: %s'
    );
}



/**
 * TInt
 *
 * Исключение типа TInt
 *
 * @package TP\Exception
 */
class TInt extends TP\Exception {

    /**
     * Значение меньше минимально допустимого
     */
    const MIN_BOUND = 1;

    /**
     * Превышено максимальное значение
     */
    const MAX_BOUND = 2;

    protected $_messages = array(
        self::MIN_BOUND => '%s: value \'%s\' is less than min bound: %s',
        self::MAX_BOUND => '%s: value \'%s\' is greatest than max bound: %s'
    );
}



/**
 * TBool
 *
 * Исключение типа TBool
 *
 * @package TP\Exception
 */
class TBool extends TP\Exception {

    /**
     * Значение не соответствует типу
     */
    const TYPE = 1;

    protected $_messages = array(
        self::TYPE => '%s: value is not of boolean type'
    );
}



/**
 * Currency
 *
 * Исключение типа Currency
 *
 * @package TP\Exception
 */
class Currency extends TP\Exception {

    /**
     * Значение не соответствует типу
     */
    const TYPE = 1;

    /**
     * Недопустимый формат
     */
    const FORMAT = 2;

    /**
     * Значение валюты не найдено.
     */
    const VALUE_NOT_FOUND = 3;

    /**
     * Формат не определен.
     */
    const FORMAT_NOT_FOUND = 4;

    /**
     * Переданно неправильное количество аргументов
     */
    const ARGUMENTS = 5;

    protected $_messages = array(
        self::TYPE => '%s: invalid currency format: %s',
        self::FORMAT => 'Format \'%s\' is not valid',
        self::VALUE_NOT_FOUND => '%s: currency %s not found',
        self::FORMAT_NOT_FOUND => '%s: format is not defined',
        self::ARGUMENTS => 'Passed the wrong number of arguments'
    );
}



/**
 * Sex
 *
 * Исключение типа Sex
 *
 * @package TP\Exception
 */
class Sex extends TP\Exception {

    /**
     * Значение не соответствует типу
     */
    const TYPE = 1;

    /**
     * Недопустимый формат
     */
    const FORMAT = 2;

    /**
     * Значение валюты не найдено.
     */
    const VALUE_NOT_FOUND = 3;

    /**
     * Формат не определен.
     */
    const FORMAT_NOT_FOUND = 4;

    /**
     * Переданно неправильное количество аргументов
     */
    const ARGUMENTS = 5;

    protected $_messages = array(
        self::TYPE => '%s: invalid sex format: %s',
        self::FORMAT => 'Format \'%s\' is not valid',
        self::VALUE_NOT_FOUND => '%s: sex %s not found',
        self::FORMAT_NOT_FOUND => '%s: format is not defined',
        self::ARGUMENTS => 'Passed the wrong number of arguments'
    );
}



/**
 * Date
 *
 * Исключение типа Date
 *
 * @package TP\Exception
 */
class Date extends TP\Exception {

    /**
     * Значение не соответствует типу
     */
    const TYPE = 1;

    /**
     * Недопустимый формат
     */
    const FORMAT = 2;

    protected $_messages = array(
        self::TYPE => '%s: invalid date format: %s',
        self::FORMAT => 'Format \'%s\' is not valid'
    );
}



/**
 * DateTime
 *
 * Исключение типа DateTime
 *
 * @package TP\Exception
 */
class DateTime extends TP\Exception {

    /**
     * Значение не соответствует типу
     */
    const TYPE = 1;

    /**
     * Недопустимый формат
     */
    const FORMAT = 2;

    protected $_messages = array(
        self::TYPE => '%s: invalid datetime format: %s',
        self::FORMAT => 'Format \'%s\' is not valid'
    );
}



/**
 * IP
 *
 * Исключение типа IP
 *
 * @package TP\Exception
 */
class IP extends TP\Exception {

    /**
     * Значение не соответствует типу
     */
    const TYPE = 1;

    protected $_messages = array(
        self::TYPE => '%s: malformed IP'
    );
}



/**
 * Str
 *
 * Исключение типа Str
 *
 * @package TP\Exception
 */
class Str extends TP\Exception {

    /**
     * Строка содержит недопустимые символы
     */
    const WRONG_CHAR = 1;

    protected $_messages = array(
        self::WRONG_CHAR => '%s: wrong char found'
    );
}



/**
 * Email
 *
 * Исключение типа Email
 *
 * @package TP\Exception
 */
class Email extends TP\Exception {

    /**
     * Неправильный формат адреса
     */
    const NOT_VALID_EMAIL = 1;

    /**
     * Неправильный формат локальной части адреса
     */
    const NOT_VALID_LOCALPART = 2;

    /**
     * Неправильный формат хоста адреса
     */
    const NOT_VALID_HOSTNAME = 3;

    /**
     * Превышена максимально допустимая длина адреса
     */
    const MAX_LENGHT_EXCEED = 4;

    /**
     * Адрес содержит запрещенный хост
     */
    const EXCLUDED_HOSTNAME = 5;

    /**
     * Непревильный формат выводимого имени
     */
    const NOT_VALID_DISPLAYNAME = 6;

    protected $_messages = array(
        self::NOT_VALID_EMAIL => '%s: \'%s\' is no valid email address in the basic format local-part@hostname',
        self::NOT_VALID_LOCALPART => '%s: \'%s\' is no valid local part for email address \'%s\'',
        self::NOT_VALID_HOSTNAME => '%s: \'%s\' is no valid hostname part for email address \'%s\'',
        self::MAX_LENGHT_EXCEED => '%s: \'%s\' exceeds the allowed length',
        self::EXCLUDED_HOSTNAME => '%s: \'%s\' is not allowed hostname part for email address \'%s\'',
        self::NOT_VALID_DISPLAYNAME => '%s: \'%s\' is no valid displayname of email address \'%s\''
    );
}



/**
 * Arr
 *
 * Исключение типа Arr
 *
 * @package TP\Exception
 */
class Arr extends TP\Exception {

    /**
     * Значение не соответствует типу
     */
    const TYPE = 1;

    protected $_messages = array(
        self::TYPE => '%s: value is invalid type'
    );
}


/**
 * TColor
 *
 * Исключение типа TColor
 *
 * @package TP\Exception
 */
class TColor extends TP\Exception {

    const FORMAT = 1;

    protected $_messages = [
        self::FORMAT => '%s: unknonwn color format'
    ];
}