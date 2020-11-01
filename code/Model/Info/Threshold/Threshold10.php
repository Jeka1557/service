<?php

namespace Model\Info\Threshold;

use Model\Info\Threshold;


class Threshold10 extends Threshold {

    static public function getAnswers() {
        return array(
            1 => '<10%',
            2 => '>=10%',
        );
    }

    public function getAnswerId() {
        if ($this->_dealPercent=='')
            return $this->_defaultAnswerId;

        if ($this->_dealPercent<10)
            return 1;
        else
            return 2;
    }
}