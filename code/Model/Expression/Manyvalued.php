<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 26.08.2016
 * Time: 00:16
 */

namespace Model\Expression;
use Infr;


class Manyvalued extends Formula {

    protected $_value = null;
    protected $_textValue;

    public function getNodeAnswers() {
        return array(
            1 => 'Результат выражения'
        );
    }

    public function getNodeAnswerId() {
        return 1;
    }

    public function calculate()
    {
        $this->checkAllBlockValuesSet();

        foreach ($this->_answers as $answer) {
            /** @var \Model\Expression\Answer $answer  */

            if ($answer->checkCondition($this->blockValues)) {
                $this->_answerId = $answer->id;
                $this->_value = $answer->calculate($this->blockValues);

                break;
            }
        }

        if (is_null($this->_answerId))
            throw new \Exception("No matched condition");

        $this->_textValue = $this->getTextValue($this->_value);
    }

}