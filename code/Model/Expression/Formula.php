<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 26.08.2016
 * Time: 08:39
 */


namespace Model\Expression;
use Infr;


class Formula extends \Model\Expression {

    protected $_answerId = 1;

    protected $_value = null;
    protected $_textValue;

    public function getNodeAnswers() {
        return array(
            1 => 'Результат выражения',
        );
    }

    public function getNodeAnswerId() {
        return 1;
    }

    public function calculate() {
        /** @var \Model\Expression\Answer $answer */

        $this->checkAllBlockValuesSet();

        $answer = reset($this->_answers);
        $this->_value = $answer->calculate($this->blockValues);

        $this->_textValue = $this->getTextValue($this->_value);
    }

}

