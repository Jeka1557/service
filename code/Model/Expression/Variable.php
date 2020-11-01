<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 26.08.2016
 * Time: 08:39
 */


namespace Model\Expression;
use Infr;


class Variable extends \Model\Expression {

    protected $_answerId = 1;

    protected $_value = 0;
    protected $_textValue;

    /**
     * @var Answer;
     */
    protected $_answer;


    static public function newFromArray($data = []) {
        /* @var Variable $entity */
        $entity = parent::newFromArray($data);

        $entity->_answer = Answer::newFromArray([
            'id' => 1,
            'header' => 'Answer',
            'condition' => 'true',
            'formula' => '0',
            'expressionId' => $entity->_id,
        ]);

        return $entity;
    }


    public function getNodeAnswers() {
        return array(
            1 => 'Результат выражения',
        );
    }

    public function getNodeAnswerId() {
        return 1;
    }

    public function setFormula($formula) {
        $this->_answer->formula = $formula;
    }

    public function calculate() {
        /** @var \Model\Expression\Answer $answer */

        $this->checkAllBlockValuesSet();

        $this->_value = $this->_answer->calculate($this->blockValues, $this->_value);

        $this->_textValue = $this->getTextValue($this->_value);
    }

}

