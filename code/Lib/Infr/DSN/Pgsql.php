<?php

namespace Lib\Infr\DSN;
use TP;

/**
 * LSF2_Infr_DSN_Pgsql
 *
 * Спецификация параметров подключения к postgresql.
 *
 * @property \TP\Str\StrSafe host
 * @property \TP\UInt2 port
 * @property \TP\Str\WordEn|null dbname
 * @property \TP\Str\WordEn|null user
 * @property \TP\Str\WordEn|null password
 * @property \TP\UInt2|null connectTimeout
 * @property \TP\Str\StrSafe|null options
 * @package LSF2\Infr\DSN
 */

class Pgsql extends \Lib\Infr\DSN {

    /**
     * Название драйвера.
     */
    const DRIVER = 'pgsql';

    /**
     * @var array
     */
    protected static $properties = array(
        'host' => '#TP\Str\StrSafe',
        'port' => '#TP\UInt2',
        'dbname' => 'TP\Str\WordEn',
        'user' => 'TP\Str\WordEn',
        'password' => 'TP\Str\WordEn',
        'connectTimeout' => 'TP\UInt2',
        'options' => 'TP\Str\StrSafe'       //--client_encoding=UTF8
    );

    /**
     * Преобразование к строке.
     *
     * Результат кэшируется, т.к. редактирвоание запрещено.
     *
     * @return string
     * @access public
     */
    public function __toString() {
        return vsprintf("host='%s' port=%d dbname='%s' user='%s' password='%s' connect_timeout=%d options='%s'", array(
            strval($this->host),
            strval($this->port),
            strval($this->dbname),
            strval($this->user),
            strval($this->password),
            strval($this->connectTimeout),
            strval($this->options)
        ));
    }

    /**
     * Получение DSN строки подключения для PDO.
     *
     * Результат кэшируется, т.к. редактирвоание запрещено.
     *
     * @return string
     * @access public
     */
    public function pdo() {
        return vsprintf('pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s', array(
            strval($this->host),
            strval($this->port),
            strval($this->dbname),
            strval($this->user),
            strval($this->password)
        ));
    }
}