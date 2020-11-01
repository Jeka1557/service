<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 26.08.2016
 * Time: 00:16
 */

namespace Model\Expression;
use Infr;


class Condition extends \Model\Expression {

    protected $_answerId = null;


    public function calculate()
    {
        $this->checkAllBlockValuesSet();

        foreach ($this->_answers as $answer) {
            /** @var \Model\Expression\Answer $answer  */

            if ($answer->checkCondition($this->blockValues)) {
                $this->_answerId = $answer->id;
                break;
            }
        }

        if (is_null($this->_answerId))
            throw new \Exception("No matched condition");

    }

}