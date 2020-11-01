<?php


namespace Model;

/**
 * Class Expression
 *
 * @property-read $id
 * @property-read $header
 * @property-read $formula
 * @property-read $text
 * @property-read $value
 *
 */

abstract class Expression extends DictEntity {

    protected $_id;
    protected $_header;


    protected $blockValues = array();

    protected $infoBlockIds = array();
    protected $expressionBlockIds = array();

    public $_answers = array();
    protected $_answerId = 0;


    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var Expression $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'PT\ExpressionId');
        $entity->_header = static::castVar($data['header'],'TP\Text\Plain');

        $entity->_extId =  self::extId($entity->_id, $data['copyId']);

        $entity->_entityType = \PT\EntityType::EXPRESSION();

        if (!isset($data['items'])) {
            throw new \Exception('Items is not set');
        }

        foreach ($data['items'] as $item) {
            if ($item['entityType']==\PT\EntityType::INFO) {
                $entity->infoBlockIds[$item['entityId']] = $item['key'];

            } elseif ($item['entityType']==\PT\EntityType::EXPRESSION) {
                $entity->expressionBlockIds[$item['entityId']] = $item['key'];
            }

            $entity->blockValues[$item['key']] = $entity->getDefaultValue($item['defaultValue']);
        }

        return $entity;
    }

    public function setAnswers($answers) {
        $this->_answers = $answers;
    }

    public function getNodeAnswers() {
        $result = array();

        foreach ($this->answers as $answer) {
            $result[$answer->id] = (string)$answer->header; // json не правильно воспринимает null, надо пустую строку
        }

        return $result;
    }

    public function getNodeAnswerId() {
        return $this->_answerId;
    }


    public function setInfoCollection(\Model\Collection\Info $collection) {

        foreach ($collection as $item) {
            /** @var Info $item */
            if (isset($this->infoBlockIds[$item->id])) {
                $key = $this->infoBlockIds[$item->id];

                if (is_a($item, '\Model\Info\Date')) {
                    //if (!$item->isEmpty('date'))
                    if (!is_null($item->date))
                        $this->blockValues[$key] = clone $item->date;

                } elseif (is_a($item, '\Model\Info\Number')) {
                    if (!$item->isEmpty('value'))
                        $this->blockValues[$key] = $item->value;

                } elseif (is_a($item, '\Model\Info\Money')) {
                    if (!$item->isEmpty('amount'))
                        $this->blockValues[$key] = $item->amount;

                } elseif (is_a($item, '\Model\Info\LongText')) {
                    if (!$item->isEmpty('dataText'))
                        $this->blockValues[$key] = $item->dataText;

                } else {
                    throw new \Exception("Unsupported info type");
                }
            }
        }
    }


    public function setExpressionCollection(\Model\Collection\Expression $collection) {

        foreach ($collection as $item) {
            /** @var \Model\Expression $item */
            if (isset($this->expressionBlockIds[$item->id])) {
                $key = (int)$this->expressionBlockIds[$item->id];

                if (!is_null($item->value))
                    if (is_object($item->value))
                        $this->blockValues[$key] = clone $item->value;
                    else
                        $this->blockValues[$key] = $item->value;
            }
        }
    }


    protected function getDefaultValue($value) {
        if (!strlen($value))
            return null;

        $m = array();

        if (is_numeric($value)) {
            $iValue = (integer)$value;
            $fValue = (float)$value;
            return ($iValue==$fValue)?$iValue:$fValue;

        } elseif (preg_match('~(\d{2})\.(\d{2}).(\d{4})~',$value,$m)) {
            $dt = new \DateTime();
            $dt->setDate($m[3], $m[2], $m[1]);
            return $dt;
        } else {
            throw new \Exception("Unknown default value {$value}");
        }
    }



    protected function checkAllBlockValuesSet() {

        foreach ($this->blockValues as $k=>$value) {
            if (is_null($value))
               throw new \Model\Exception\Expression("expression: {$this->_id} argument: \${$k}", \Model\Exception\Expression::ARGUMENT_IS_NOT_SET);
        }
    }


    public abstract function calculate();


    static public function newEntity(\PT\ExpressionType $type, $data) {
        switch ($type->val()) {
            case \PT\ExpressionType::FORMULA:
                return \Model\Expression\Formula::newFromArray($data);

            case \PT\ExpressionType::CONDITION:
                return \Model\Expression\Condition::newFromArray($data);

            case \PT\ExpressionType::MANYVALUED:
                return \Model\Expression\Manyvalued::newFromArray($data);

            case \PT\ExpressionType::VARIABLE:
                return \Model\Expression\Variable::newFromArray($data);

            default:
                throw new \Exception("Storage: unknown info type");
        }
    }


    protected function getTextValue($value) {
        if (is_null($value))
            return 'ОШИБКА ПРИ ВЫЧИСЛЕНИИ ВЫРАЖЕНИЯ';

        elseif (is_int($value))
            return number_format($this->value, 0, ".", " ");

        elseif (is_float($value))
            return number_format($this->value, 2, ".", " ");

        elseif (is_object($value)) {
            if (is_a($value, 'DateTime'))
                /** @var \DateTime $value */
                return $value->format('d.m.Y');
            elseif (is_a($value, 'DateInterval'))
                /** @var \DateInterval $this->value */
                return $value->days;
            else
                return (string)$value;
        } else
            return (string)$value;
    }


    public function toArray(array $varsArray = null) {
        $data = array();
        $vars = get_object_vars($this);

        foreach ($vars as $m => $v) {
            if ($m[0] != '_') {
                continue;
            }

            if (!isset($this->$m)) {
                continue;
            }

            $key = substr($m, 1);

            if ($varsArray && !in_array($key, $varsArray)) {
                continue;
            }

            $v = $this->$key;

            if (is_object($v)) {
                if (method_exists($v, 'toArray')) {
                    /* @var \TP\Arr\Arr|Value|Collection $v */
                    $v = $v->toArray();

                } elseif ($v instanceof \TP\Type) {
                    /* @var \TP\Type $v */
                    $v = $v->val();

                } elseif ($v instanceof \DateTime) {
                    /* @var \DateTime $v */
                    $v = $v->format('d.m.Y');

                } else {
                    $v = strval($v);
                }
            }

            $data[$key] = $v;
        }

        return $data;
    }

}

/**
 * @deprecated
 */

function interval_days($days) {
    return new \DateInterval('P'.$days.'D');
}

/**
 * @deprecated
 */

function days_in_year($date) {

}
