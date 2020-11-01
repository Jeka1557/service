<?php

namespace Model\Info\Threshold;

use Model\Info\Threshold;


class Threshold25v50 extends Threshold {

    static public function getAnswers() {
        return array(
            1 => '<25%',
            2 => '25-50%',
            3 => '>50%',
        );
    }

    public function getAnswerId() {
        if ($this->_dealPercent=='')
            return $this->_defaultAnswerId;

        if ($this->_dealPercent<25)
            return 1;
        elseif ($this->_dealPercent<=50)
            return 2;
        else
            return 3;
    }
}