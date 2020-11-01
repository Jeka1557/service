<?php

namespace Model\Info;
use Infr;
use Model\Exception;


class Text extends \Model\Info {

    const ST_TEXT = 'text';
    const ST_WORD = 'word';
    const ST_RU_TEXT = 'ru_text';
    const ST_PASSPORT = 'passport';
    const ST_RF_SUBJECT = 'rf_subject';

    protected $_jsMask = 'js-mask-text';

    protected $_subtype = self::ST_TEXT;

    protected $_value;

    static public function newFromArray($data = []) {
        /* @var Text $entity */
        $entity = self::newInfo($data);

        if (isset($data['settings']['subtype'])) {
            switch ($data['settings']['subtype']) {
                case self::ST_WORD:
                    $entity->_subtype = self::ST_WORD;
                    $entity->_jsMask = 'js-mask-word';
                    $entity->_errorMessage = 'Укажите корректное значение (одно слово).';
                break;
                case self::ST_RU_TEXT:
                    $entity->_subtype = self::ST_RU_TEXT;
                    $entity->_jsMask = 'js-mask-ru-text';
                    $entity->_errorMessage = 'Укажите корректное значение (только русский текст, без латиницы).';
                break;
                case self::ST_PASSPORT:
                    $entity->_subtype = self::ST_PASSPORT;
                    $entity->_jsMask = 'js-mask-passport';
                    $entity->_errorMessage = 'Укажите корректное значение (только цифры, пробел, тире и №).';
                    break;
                case self::ST_RF_SUBJECT:
                    $entity->_subtype = self::ST_RF_SUBJECT;
                    $entity->_jsMask = 'js-mask-rf-subject';
                    $entity->_errorMessage = 'Укажите корректный субъект РФ (русские буквы, точка, запятая, тире, скобки).';
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

    protected function validate($data) {
        switch ($this->_subtype) {
            case self::ST_WORD:
                return $this->validateWord($data);
            case self::ST_RU_TEXT:
                return $this->validateRuText($data);
            case self::ST_PASSPORT:
                return $this->validatePassport($data);
            case self::ST_RF_SUBJECT:
                return $this->validateRfSubject($data);
            default:
                return true;
        }
    }


    protected function validateWord($data) {
        if (preg_match('~^[A-Za-zА-Яа-яЁё\-]+$~u', $data))
            return true;
        else
            return false;
    }

    protected function validateRuText($data) {
        if (preg_match('~^[^A-Za-z]+$~u', $data))
            return true;
        else
            return false;
    }

    protected function validatePassport($data) {
        if (preg_match('~^[0-9A-Za-zА-Яа-яЁё\-№\s]+$~u', $data))
            return true;
        else
            return false;
    }

    protected function validateRfSubject($data) {
        if (preg_match('~^[А-Яа-яЁё\-\.\,\(\)\s]+$~u', $data))
            return true;
        else
            return false;
    }

}