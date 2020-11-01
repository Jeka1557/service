<?php

namespace Lib\Infr\DSN;


class Exception extends \Lib\Exception {
    /**
     * Не определен драйвер DSN
     */

    const DRIVER_UNDEF = 1;

    /**
     * Метод не определен
     */
    const METHOD_UNDEF = 2;

    /**
     * Редактирование запрещено
     */
    const SET_BAN = 3;

    /**
     * DSN задан не верно
     */
    const DSN_INVALID = 4;

    /**
     * Не найден параметр пподключения
     */
    const DSN_PROP_UNDEF = 5;
    
    /**
     * Расширение файла должно быть .properties
     */
    const PROPERTIES_WRONG = 6;

    protected $_messages = array(
        self::DRIVER_UNDEF => 'Driver \'%s\' is not defined',
        self::METHOD_UNDEF => 'Undefined method',
        self::SET_BAN => 'Set not implemented',
        self::DSN_INVALID => 'DSN options are not specified correctly',
        self::DSN_PROP_UNDEF => 'Connection parameter "%s" is not found',
        self::PROPERTIES_WRONG => 'The file extension must be .properties'
    );

}