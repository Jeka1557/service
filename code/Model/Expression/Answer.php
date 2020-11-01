<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 26.08.2016
 * Time: 09:05
 */

/**
 * @property-read $id
 * @property-read $header
 * @property-read $idx
 * @property-read $condition
 */

namespace Model\Expression;
use Lib\Model\Value;
use Infr;
use Model\Exception;


class Answer extends Value {

    protected $_id;
    protected $_header;
    protected $_condition;
    protected $_formula;
    protected $_expressionId;


    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var Answer $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'TP\UInt2');
        $entity->_header = static::castVar($data['header'],'TP\Text\Plain');
        $entity->_condition = static::castVar($data['condition'],'TP\Text\Plain');
        $entity->_formula = static::castVar($data['formula'],'TP\Text\Plain');
        $entity->_expressionId = static::castVar($data['expressionId'],'PT\ExpressionId');


        return $entity;
    }


    public function __set($name, $value) {
        switch ($name) {
            case 'formula':
                $this->_formula = $value;
                break;
            default:
                parent::__set($name, $value);
                break;
        }
    }


    public function checkCondition($blockValues) {
        global $glbBlockValues;
        $glbBlockValues = $blockValues;

        $formula = $this->_condition;

        $matches = array();
        preg_match_all('~\$(\d+)~', $formula, $matches);
        $params = array_unique($matches[1]);

        $formula = "return (" . preg_replace('~\$(\d+)~', '\$p$1', $formula) . ")?1:0;";

        $expression = 'global $glbBlockValues; ';
        $expression .= 'include_once "'.__DIR__.'/tools.php"; ';

        foreach ($params as $num) {
            $num = (int)$num;

            if (!isset($blockValues[$num]))
                throw new Exception\ExpressionAnswer(Exception\ExpressionAnswer::CONDITION_ARGUMENT_NOT_DEFINED, $this->_expressionId, $this->_id, $this->_condition, $blockValues, $num);

            $expression .= "\$p{$num} = \$glbBlockValues[{$num}];";
        }

        $expression .= $formula;


        $result = @eval($expression);

        if ($result === false)
            throw new Exception\ExpressionAnswer(Exception\ExpressionAnswer::CONDITION_EXPRESSION_IS_INVALID, $this->_expressionId, $this->_id, $this->_condition, $blockValues);


        return $result==1?true:false;
    }

    public function calculate($blockValues, $selfValue = 0) {
        global $glbBlockValues;
        $glbBlockValues = $blockValues;
        $glbBlockValues[0] = $selfValue;

        $matches = array();
        preg_match_all('~\$(\d+)~', $this->_formula, $matches);
        $params = array_unique($matches[1]);

        $formula = "return " . preg_replace('~\$(\d+)~', '\$p$1', $this->_formula) . ";";

        $expression = 'global $glbBlockValues; ';
        $expression .= 'include_once "'.__DIR__.'/tools.php"; ';

        foreach ($params as $num) {
            $num = (int)$num;

            if (!isset($blockValues[$num]))
                throw new Exception\ExpressionAnswer(Exception\ExpressionAnswer::FORMULA_ARGUMENT_NOT_DEFINED, $this->_expressionId, $this->_id, $this->_formula, $blockValues, $num);

            $expression .= "\$p{$num} = \$glbBlockValues[{$num}];";
        }

        $expression .= "\$self = \$glbBlockValues[0];";
        $expression .= $formula;


        $result = eval($expression);

        if ($result === false)
            throw new Exception\ExpressionAnswer(Exception\ExpressionAnswer::FORMULA_EXPRESSION_IS_INVALID, $this->_expressionId, $this->_id, $this->_formula, $blockValues);

        return $result;

    }
}