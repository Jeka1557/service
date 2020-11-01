<?php
/**
 * Date: 14.08.12 22:28
 * @author anton
 * @package LSF2\Infr
 * @copyright LightSoft 2012
 */

namespace Lib\Infr;

/**
 * Db
 *
 * @package LSF2\Infr
 */
final class Db {

    const PARAM_NULL = 1;
    const PARAM_SMALLINT = 4096;
    const PARAM_INT = 2;
    const PARAM_BIGINT = 8192;
    const PARAM_STR = 4;
    //const PARAM_LOB = 8;
    //const PARAM_STMT = 16;
    const PARAM_BOOL = 32;
    const PARAM_FLOAT = 64;
    const PARAM_BYTEA = 128;
    const PARAM_TIMESTAMP = 16384;
    const PARAM_DATE = 32768;
    const PARAM_TSVECTOR = 65536;
    const PARAM_TSQUERY = 131072;
    const PARAM_NUMERIC = 524288;

    /**
     * Массив данных.
     * Для postrgesql в результате будет {...}.
     */
    const PARAM_DATAARR = 262144;

    /**
     * Бинарные данные.
     * Для postrgesql в результате будет E'...'::bytea.
     */
    const PARAM_BIN = 128;

    /**
     * Псевдо тип, объект Select
     */
    const PARAM_SELECT = 256;

    /**
     * Псевдо тип, объект \Closure
     */
    const PARAM_EXPR = 512;

    /**
     * Псевдо тип, массив условий
     */
    const PARAM_ARR = 1024;

    const PARAM_AUTO = 2048;










    /**
     * PARAM_SELECT + PARAM_EXPR + PARAM_ARR
     */
    const MASK_STRUCT = 1792;



    /**
     * Выборка результата как ассоциативного массива.
     */
    const FETCH_ASSOC = 2;

    /**
     * Выборка результата как пронумерованного массива.
     */
    const FETCH_NUM = 3;

    /**
     * Выборка результата как пронумерованного и ассоциативного массива одновременно.
     */
    const FETCH_BOTH = 4;

    // для объекта Select и Table
    const EVENT_BEFORE_SELECT = 'beforeSelect';
    const EVENT_AFTER_SELECT = 'afterSelect';
    const EVENT_BEFORE_INSERT = 'beforeInsert';
    const EVENT_AFTER_INSERT = 'afterInsert';
    const EVENT_BEFORE_UPDATE = 'beforeUpdate';
    const EVENT_AFTER_UPDATE = 'afterUpdate';
    const EVENT_BEFORE_DELETE = 'beforeDelete';
    const EVENT_AFTER_DELETE = 'afterDelete';

    // для объекта Statement
    const EVENT_AFTER_FETCH_ROW = 'afterFetchRow';
    const EVENT_AFTER_FETCH_OBJECT = 'afterFetchObject';
    const EVENT_AFTER_FETCH_ONE = 'afterFetchOne';
    const EVENT_AFTER_FETCH_ALL = 'afterFetchAll';
    const EVENT_AFTER_FETCH_ALL_OBJECT = 'afterFetchAllObject';
    const EVENT_AFTER_FETCH_ALL_COLUMNS = 'afterFetchAllColumns';
    const EVENT_AFTER_FETCH_ALL_PAIRS = 'afterFetchAllPairs';
    const EVENT_AFTER_FETCH_ALL_ASSOC = 'afterFetchAllAssoc';

    const EVENT_AFTER_CONNECT = 'afterConnect';
}