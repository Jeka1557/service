<?php

namespace Model\Info;
use Infr;
use Model\Exception;

class Number extends \Model\Info {

    const ST_NUMBER = 'number';
    const ST_INT = 'int';
    const ST_FLOAT = 'float';
    const ST_CARD = 'card';
    const ST_CARD_LD = 'card_ld';
    const ST_ACCOUNT = 'account';
    const ST_INN = 'inn';
    const ST_MONEY = 'money';

    const ST_INT6 = 'int6';
    const ST_INT8 = 'int8';
    const ST_INT9 = 'int9';

    protected $_value;
    protected $_subtype = self::ST_NUMBER;

    protected $_jsMask = 'js-mask-text';



    static public function newFromArray($data = []) {
        /* @var Number $entity */
        $entity = self::newInfo($data);

        if (isset($data['settings']['subtype'])) {
            switch ($data['settings']['subtype']) {
                case self::ST_INT:
                    $entity->_subtype = self::ST_INT;
                    $entity->_jsMask = 'js-mask-int';
                    $entity->_errorMessage = 'Укажите корректное значение (целое число)';
                    break;
                case self::ST_INT6:
                    $entity->_subtype = self::ST_INT6;
                    $entity->_jsMask = 'js-mask-int-6';
                    $entity->_errorMessage = 'Укажите корректное значение (6-ти значное число)';
                    break;
                case self::ST_INT8:
                    $entity->_subtype = self::ST_INT8;
                    $entity->_jsMask = 'js-mask-int-8';
                    $entity->_errorMessage = 'Укажите корректное значение (8-ми значное число)';
                    break;
                case self::ST_INT9:
                    $entity->_subtype = self::ST_INT9;
                    $entity->_jsMask = 'js-mask-int-9';
                    $entity->_errorMessage = 'Укажите корректное значение (9-ти значное число)';
                    break;
                case self::ST_FLOAT:
                    $entity->_subtype = self::ST_FLOAT;
                    $entity->_jsMask = 'js-mask-float';
                    $entity->_errorMessage = 'Укажите корректное значение (дробное число)';
                    break;
                case self::ST_CARD:
                    $entity->_subtype = self::ST_CARD;
                    $entity->_jsMask = 'js-mask-card';
                    $entity->_errorMessage = 'Укажите корректное значение (от 15 до 19 цифр)';
                    break;
                case self::ST_CARD_LD:
                    $entity->_subtype = self::ST_CARD_LD;
                    $entity->_jsMask = 'js-mask-card-ld';
                    $entity->_errorMessage = 'Укажите корректное значение (4 цифры)';
                    break;
                case self::ST_ACCOUNT:
                    $entity->_subtype = self::ST_ACCOUNT;
                    $entity->_jsMask = 'js-mask-account';
                    $entity->_errorMessage = 'Укажите корректное значение (20 цифр)';
                    break;
                case self::ST_INN:
                    $entity->_subtype = self::ST_INN;
                    $entity->_jsMask = 'js-mask-inn';
                    $entity->_errorMessage = 'Укажите корректное значение (12 цифр)';
                    break;
                case self::ST_MONEY:
                    $entity->_subtype = self::ST_MONEY;
                    $entity->_jsMask = 'js-mask-money';
                    $entity->_errorMessage = 'Укажите корректное значение (число)';
                    break;
            }
        }


        $entity->initDefault(static::castVar($data['defaultValue'],'TP\Text\Plain'));

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        $entity->_answers = static::getAnswers();

        return $entity;
    }



    protected function applyValue($data) {
        $value = str_replace([' ', ','],['', '.'], trim($data));

        if (!is_numeric($value))
            return ;

        switch ($this->_subtype) {
            case self::ST_INT:
                $this->_value = (integer)$value;
            break;

            case self::ST_FLOAT:
                $this->_value = (float)$value;
            break;

            case self::ST_CARD:
                $this->_value = $value;
            break;

            case self::ST_CARD_LD:
                $this->_value = $value;
            break;

            case self::ST_ACCOUNT:
                $this->_value = $value;
            break;

            case self::ST_INN:
                $this->_value = $value;
            break;

            default:
                $iValue = (integer)$value;
                $fValue = (float)$value;

                $this->_value = ($iValue == $fValue) ? $iValue : $fValue;
            break;
        }
    }


    protected function format() {
        switch ($this->_subtype) {
            case self::ST_INT:
                $this->_dataText = number_format($this->value, 0, ".", " ");
                break;

            case self::ST_FLOAT:
                $this->_dataText = number_format($this->value, 3, ".", " ");
                break;

            case self::ST_CARD:
                $this->_dataText = implode(' ', str_split($this->value, 4));
                break;

            case self::ST_CARD_LD:
                $this->_dataText = $this->value;
                break;

            case self::ST_ACCOUNT:
                $this->_dataText = $this->value;// implode(' ', str_split($this->value, 4));
                break;

            case self::ST_INN:
                $this->_dataText = implode(' ', str_split($this->value, 3));
                break;

            case self::ST_MONEY:
                $this->_dataText = is_float($this->value)?number_format($this->value, 2, ".", " "):number_format($this->value, 0, ".", " ");
                break;

            default:
                $this->_dataText = is_float($this->value)?number_format($this->value, 3, ".", " "):number_format($this->value, 0, ".", " ");
        }


    }



    protected function validate($data) {
        $data = str_replace([' ', ','],['', '.'], trim($data));

        if (!strlen($data))
            return false;

        switch ($this->_subtype) {
            case self::ST_INT:
                return $this->validateInt($data);
            case self::ST_INT6:
                return $this->validateIntDigits($data,6,6);
            case self::ST_INT8:
                return $this->validateIntDigits($data,8,8);
            case self::ST_INT9:
                return $this->validateIntDigits($data,9,9);
            case self::ST_CARD:
                return $this->validateIntDigits($data,15,19);
            case self::ST_CARD_LD:
                return $this->validateIntDigits($data,4,4);
            case self::ST_ACCOUNT:
                return $this->validateIntDigits($data,20,20);
            case self::ST_INN:
                return $this->validateIntDigits($data,12,12);
            case self::ST_MONEY:
                return $this->validateMoney($data);
            case self::ST_FLOAT:
            default:
                return $this->validateFloat($data);
        }
    }


    protected function validateInt($data) {
        if (preg_match('~^\-?[0-9]+$~', $data))
            return true;
        else
            return false;
    }

    protected function validateFloat($data) {
        if (preg_match('~^\-?[0-9]+(\.[0-9]+)?$~', $data))
            return true;
        else
            return false;
    }

    protected function validateIntDigits($data, $min, $max) {
        if (preg_match('~^[0-9]{'.$min.','.$max.'}$~', $data))
            return true;
        else
            return false;
    }


    protected function validateMoney($data) {
        if (preg_match('~^[0-9\s]+(\.[0-9]+)?$~', $data))
            return true;
        else
            return false;
    }

}