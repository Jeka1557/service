<?php

namespace Model\Info;
use Infr;
use Model\Exception;

class LongText extends \Model\Info {

    const ST_TEXT = 'text';
    const ST_RU_TEXT = 'ru_text';

    protected $TMPL_DEFAULT = 'LongText';
    protected $TMPL_WC = 'wc/LongText';
    protected $TMPL_VTB = 'vtb/LongText';
    protected $TMPL_BST4 = 'LongText';


    protected $_jsMask = 'js-mask-text';

    protected $_subtype = self::ST_TEXT;


    static public function newFromArray($data = []) {
        /* @var LongText $entity */
        $entity = self::newInfo($data);

        if (isset($data['settings']['subtype'])) {
            switch ($data['settings']['subtype']) {
                case self::ST_RU_TEXT:
                    $entity->_subtype = self::ST_RU_TEXT;
                    $entity->_jsMask = 'js-mask-ru-text';
                    $entity->_errorMessage = 'Укажите корректное значение (только русский текст, без латиницы).';
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
            case self::ST_RU_TEXT:
                return $this->validateRuText($data);
            default:
                return true;
        }
    }


    protected function validateRuText($data) {
        if (preg_match('~^[^A-Za-z]+$~u', $data))
            return true;
        else
            return false;
    }


}