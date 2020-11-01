<?php

namespace Lib\Infr\Db;


class Exception extends \Lib\Exception {

    const PART_UNDEF = 1;
    const ORDER_UNDEF = 3;
    const GROUP_UNDEF = 6;
    const COND_VARS = 7;
    const COND_KEY = 8;
    const PARAM_UNDEF = 9;
    const TYPE_UNDEF = 10;
    const COND_TYPES = 11;
    const PGSQL_UNDEF = 20;
    const PGSQL_CONNECT = 21;
    const PGSQL_QUERY = 22;

    /**
     * Тип ресурса не соответствует адаптеру.
     */
    const STMT_RESOURCE_TYPE = 50;

    /**
     * Ошибка при получении количества строк результата.
     */
    const STMT_RESULT_COUNT = 51;

    /**
     * Ошибка при получении количества столбцов.
     */
    const STMT_FIELDS_COUNT = 52;

    /**
     * Тип результата запроса не определен.
     */
    const STMT_FETCH_TYPE = 53;

    /**
     * Столбец не найден.
     */
    const STMT_COLUMN_NOT_FOUND = 54;

    /**
     * Количество столбцов не соответствует типу выборки.
     */
    const STMT_COLUMN_FETCH_PAIRS = 55;
    const TABLE_NAME = 80;
    /**
     * Не найдено имя таблицы переводов 
     */
    const TABLE_TRANS_NAME = 81;
    /**
     * Не найдено столбцы для таблицы переводов 
     */
    const TABLE_TRANS_COLUMNS = 82;

    /**
     * Объект не является исполняемым.
     */
    const NOT_CALLABLE = 90;

    /**
     * Неправильное название параметра.
     */
    const PARAM_NAME = 91;
    const JOIN_UNDEF = 100;

    /**
     * Указаны не все настройки конфига подключения.
     */
    const DSN_CONFIG = 200;

    /**
     * Настройки подключения заданы не верно.
     */
    const DSN_UNDEF = 201;

    protected $_messages = array(
        self::PART_UNDEF => 'Part of the request could not be found',
        
        self::ORDER_UNDEF => 'Undefined sort list',
        self::GROUP_UNDEF => 'Undefined group list',
        
        self::COND_VARS => 'Number of variables does not match the number of substitutions',
        self::COND_TYPES => 'Number of types does not match the number of substitutions',
        self::COND_KEY => 'Not found the key variable',
        
        self::PARAM_UNDEF => 'Request parameter "%s" is not found',
        
        self::TYPE_UNDEF => 'Undefined data type',
        
        self::PGSQL_UNDEF => 'The PGSQL extension is required but the extension is not loaded',
        self::PGSQL_CONNECT => 'Connection to PostgreSQL %s:%s failed',
        self::PGSQL_QUERY => '%s',
        
        self::STMT_RESOURCE_TYPE => 'Type of resource adapter does not match',
        self::STMT_RESULT_COUNT => 'Error getting number of rows result',
        self::STMT_FIELDS_COUNT => 'Error getting the number of columns',
        self::STMT_FETCH_TYPE => 'The result type of query is not defined',
        self::STMT_COLUMN_NOT_FOUND => 'Column %s not found',
        self::STMT_COLUMN_FETCH_PAIRS => 'Column count does not match the type of sample',
        
        self::TABLE_NAME => 'No table name specified',
        self::TABLE_TRANS_NAME => 'No translation table name specified',
        self::TABLE_TRANS_COLUMNS => 'No columns of translation table specified',
        
        self::NOT_CALLABLE => 'Object is not callable',
        self::PARAM_NAME => 'Parameter "%s" is not named correctly',
        self::JOIN_UNDEF => 'No join with table %s %s found',
        
        self::DSN_CONFIG => 'No connection settings: %s',
        self::DSN_UNDEF => 'Connection settings are not specified correctly'
    );

}