<?php

namespace Lib;

class Exception extends \Exception {

    /**
     * @var array текст ошибки
     */
    protected $_messages = array();

    /**
     * Конструктор исключения.
     *
     * @param int $code код ошибки
     * @param string|array $message текст ошибки, либо массив переменных для подстановки в текст ошибки
     */
    public function __construct($code, $message = '') {
        // заглушка пока не изменим работу везде
        if (is_string($code)) {
            parent::__construct($code, (is_int($message)) ? $message : null);
            return;
        }


        $str = false;

        if (isset($this->_messages[$code])) {
            $str = $this->_messages[$code];
        }

        if ($message && !$str) {
            $str = (is_array($message)) ? array_shift($message) : (string) $message;
        }

        if (!$str) {
            $str = 'Undefined exception';
        }

        if ($message && is_array($message)) {
            $str = vsprintf($str, $message);
        }

        parent::__construct($str, $code);
    }
}