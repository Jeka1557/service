<?php

namespace Model\Info\Threshold;

use Model\Info\Threshold;


class Threshold01 extends Threshold {

    static public function getAnswers() {
        return array(
            1 => '<0.1%',
            2 => '>=0.1%',
        );
    }

    public function getAnswerId() {
        if ($this->_dealPercent=='')
            return $this->_defaultAnswerId;

        if ($this->_dealPercent<0.1)
            return 1;
        else
            return 2;
    }
}