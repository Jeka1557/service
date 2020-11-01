<?php

namespace Model\Info;
use Infr;

class Phone extends \Model\Info {

    protected $_jsMask = 'js-mask-phone';

    protected $_errorMessage = 'Укажите корректное значение (пример: +7 (123) 456-78-90)';

    protected function validate($data) {
        if (preg_match('~\+7\s\(\d{3}\)\s\d{3}\-\d{2}\-\d{2}~', $data))
            return true;
        else
            return false;
    }
}