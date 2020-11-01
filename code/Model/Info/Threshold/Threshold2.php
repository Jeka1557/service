<?php

namespace Model\Info\Threshold;

use Model\Info\Threshold;


class Threshold2 extends Threshold {

    static public function getAnswers() {
        return array(
            1 => '<2%',
            2 => '>=2%',
        );
    }

    public function getAnswerId() {
        if ($this->_dealPercent=='')
            return $this->_defaultAnswerId;

        if ($this->_dealPercent<2)
            return 1;
        else
            return 2;
    }
}