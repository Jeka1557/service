<?php

namespace Lib\Infr\DSN;
use TP;

/**
 * LSF2_Infr_DSN_Sqlite
 *
 * Спецификация параметров подключения к sqlite.
 *
 * @property \TP\Str\StrSafe path
 * @property \TP\Str\StrSafe file
 * @package LSF2\Infr\DSN
 */
class Sqlite extends \Lib\Infr\DSN {

    /**
     * Название драйвера.
     */
    const DRIVER = 'sqlite';

    /**
     * @var array
     */
    protected static $properties = array(
        'path' => '#TP\Str\StrSafe',
        'file' => '#TP\Str\StrSafe',
        'readOnly' => 'TP\TBool'
    );

    public function __construct(array $data) {
        parent::__construct($data);

        if (!$this->isValid()) {
            throw new Exception(Exception::DSN_INVALID);
        }
    }

    /**
     * Преобразование к строке.
     *
     * Результат кэшируется, т.к. редактирвоание запрещено.
     *
     * @return string
     * @access public
     */
    public function __toString() {
        return vsprintf("%s%s", array(
            strval($this->path),
            strval($this->file)
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
        return vsprintf("sqlite:%s%s", array(
            strval($this->path),
            strval($this->file)
        ));
    }
}