<?php

namespace Model;

use Model\Exception;

/**
 * @property-read $id
 * @property-read $header
 *
 * @property-read $dataText
 * @property-read $hasValue
 */


class Info extends DictEntity {

    protected $_id;
    protected $_header;
    protected $_defaultAnswerId;

    protected $_answers;

    protected $_dataText = '';
    protected $_value;


    protected $_hasData = false;
    protected $_hasValue = false;

    protected $_hasDefault = false;
    protected $_hasError = false;

    protected $_placeholder;
    protected $_required;

    protected $_jsMask = 'js-mask-text';

    protected $_toLocalStorage = false;

    protected $_errorMessage = 'Необходимо ввести данные';

    protected $TMPL_DEFAULT = 'Text';
    protected $TMPL_WC = 'wc/Text';
    protected $TMPL_VTB = 'vtb/Text';
    protected $TMPL_BST4 = 'Text';

    /**
     * @var \PT\InfoType
     */
    protected $_infoType;

    protected function __construct() { }


    /**
     * @param array $data
     * @return Info
     * @throws \Exception
     */

    static protected function newInfo($data = []) {
        /* @var Info $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'PT\InfoId');
        $entity->_header = static::castVar($data['header'],'TP\Text\Plain');
        $entity->_defaultAnswerId = static::castVar($data['defaultAnswerId'],'TP\UInt1');
        $entity->_infoType = static::castVar($data['infoType'],'PT\InfoType');

        $entity->_placeholder = static::castVar($data['placeholder'],'TP\Text\Plain');
        $entity->_required = static::castVar($data['required'],'TP\TBool');

        $entity->_extId =  "{$entity->_id}".($data['copyId']>0?"_{$data['copyId']}":'');

        $entity->_entityType = \PT\EntityType::INFO();

        if (isset($data['settings']['to_local_storage']) and $data['settings']['to_local_storage'])
            $entity->_toLocalStorage = true;

        return $entity;
    }

    /**
     * @param array $data
     * @return Info|null
     * @throws \Exception
     */

    static public function newFromArray($data = []) {
        /* @var Info $entity */
        $entity = self::newInfo($data);

        $entity->initDefault(static::castVar($data['defaultValue'],'TP\Text\Plain'));

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        $entity->_answers = static::getAnswers();

        return $entity;
    }

    static public function getAnswers() {
        return array(1 => 'Введены данные');
    }


    public function getAnswerId() {
        return $this->_defaultAnswerId;
    }


    public function render($inGroup = false) {
        if (self::$renderMode==self::RENDER_MODE_ARRAY) {
            $result = [
                'text' => $this->text,
                'type' => $this->_infoType,
            ];

            return $result;

        } elseif (self::$renderMode==self::RENDER_MODE_BST4) {
            return $this->renderTemplate('Info', $this->TMPL_BST4, [
                'info' => $this,
                'inGroup' => $inGroup,
            ]);

        } elseif (self::$renderMode==self::RENDER_MODE_WC) {
            return $this->renderTemplate('Info', $this->TMPL_WC, [
                'info' => $this,
                'inGroup' => $inGroup,
            ]);

        } elseif (self::$renderMode==self::RENDER_MODE_VTB) {
            return $this->renderTemplate('Info', $this->TMPL_VTB, [
                'info' => $this,
                'inGroup' => $inGroup,
            ]);

        } else {
            return $this->renderTemplate('Info', $this->TMPL_DEFAULT, [
                'info' => $this,
                'inGroup' => $inGroup,
            ]);
        }
    }

    /**
     * @param \PT\InfoType $type
     * @param $data
     * @return Info|null
     * @throws \Exception
     */

    static public function newEntity(\PT\InfoType $type, $data) {

        $data['infoType'] = $type;

        switch ($type->val()) {
            case \PT\InfoType::SQUARE:
                return \Model\Info\Square::newFromArray($data);

            case \PT\InfoType::PERIOD:
                return \Model\Info\Period::newFromArray($data);

            case \PT\InfoType::MONEY:
                return \Model\Info\Money::newFromArray($data);

            case \PT\InfoType::NUMBER:
                return \Model\Info\Number::newFromArray($data);

            case \PT\InfoType::DATE:
                return \Model\Info\Date::newFromArray($data);

            case \PT\InfoType::FIO:
                return \Model\Info\FIO::newFromArray($data);

            case \PT\InfoType::THRESHOLD01:
                return \Model\Info\Threshold\Threshold01::newFromArray($data);

            case \PT\InfoType::THRESHOLD2:
                return \Model\Info\Threshold\Threshold2::newFromArray($data);

            case \PT\InfoType::THRESHOLD10:
                return \Model\Info\Threshold\Threshold10::newFromArray($data);

            case \PT\InfoType::THRESHOLD25_50:
                return \Model\Info\Threshold\Threshold25v50::newFromArray($data);

            case \PT\InfoType::LONG_TEXT:
                return \Model\Info\LongText::newFromArray($data);

            case \PT\InfoType::TEXT:
                return \Model\Info\Text::newFromArray($data);

            case \PT\InfoType::PAYMENTS:
                return \Model\Info\Payments::newFromArray($data);

            case \PT\InfoType::EGRUL:
                return \Model\Info\Egrul::newFromArray($data);

            case \PT\InfoType::EXCEL:
                return \Model\Info\Excel::newFromArray($data);

            case \PT\InfoType::FILE:
                return \Model\Info\File::newFromArray($data);

            case \PT\InfoType::FILE_EXL:
                return \Model\Info\File\Exl::newFromArray($data);

            case \PT\InfoType::HIDDEN:
                return \Model\Info\Hidden::newFromArray($data);

            case \PT\InfoType::PHONE:
                return \Model\Info\Phone::newFromArray($data);

            case \PT\InfoType::EMAIL:
                return \Model\Info\Email::newFromArray($data);

            case \PT\InfoType::URL:
                return \Model\Info\URL::newFromArray($data);

            default:
                throw new \Exception("Storage: unknown info type");
        }
    }

    public function dataReady() {
        if (!$this->_hasData)
            return false;

        if ($this->_hasValue)
            return true;

        if (!$this->_required)
            return true;

        return false;
    }

    public function setData($data) {
        $this->clearData($data);

        $this->applyData($data);
        $this->_hasData = true;

        if ($this->isEmptyData($data)) {
            if ($this->_required)
                $this->_hasError = true;

            return;
        }

        if (!$this->validate($data)) {
            $this->_hasError = true;
            return;
        }

        $this->applyValue($data);
        $this->_hasValue = true;

        $this->format();
    }


    protected function initDefault($default) {
        $default = trim($default);

        if (!strlen($default))
            return;

        $data = $this->parseDefault($default);

        if ($this->isEmptyData($default))
            return;

        if (!$this->validate($data))
            return;

        $this->applyValue($data);
        $this->_hasDefault = true;

        $this->format();
    }


    protected function clearData(&$data) {
        $data = trim($data);
    }

    protected function parseDefault($default) {
        return $default;
    }

    protected function isEmptyData($data) {
        return strlen($data)>0?false:true;
    }

    protected function validate($data) {
        return true;
    }

    protected function applyData($data) {
        $this->_dataText = $data;
    }

    protected function applyValue($value) {
        $this->_value = $value;
    }

    protected function format() {
        $this->_dataText =  $this->_value;
    }



    protected function convertNumber($value) {
        $value = str_replace([' ', ','],['', '.'], trim($value));

        if (is_numeric($value)) {
            $iValue = (integer)$value;
            $fValue = (float)$value;

            return ($iValue == $fValue) ? $iValue : $fValue;
        }

        return null;
    }


    /**
     * @param $value
     * @return \DateTime|null
     */

    protected function convertDate($value, $time = false) {
        $value = trim($value);
        $m = array();

        if ($time) {
            if (preg_match('~(\d{2})\.(\d{2}).(\d{4})\s+(\d{2})\:(\d{2})~', $value, $m)) {
                $date = new \DateTime();
                $date->setDate($m[3], $m[2], $m[1]);
                $date->setTime($m[4], $m[5]);

                return $date;
            }
        } else {
            if (preg_match('~(\d{2})\.(\d{2}).(\d{4})~', $value, $m)) {
                $date = new \DateTime();
                $date->setDate($m[3], $m[2], $m[1]);

                return $date;
            }
        }

        return null;
    }


    public function export() {
        $data = parent::export();

        $data['dataText'] = $this->_dataText;

        return $data;
    }

}