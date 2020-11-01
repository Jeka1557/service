<?php

namespace Model\Info;
use Infr;

class FIO extends \Model\Info {

    protected $TMPL_DEFAULT = 'FIO';
    protected $TMPL_WC = 'wc/FIO';
    protected $TMPL_VTB = 'vtb/FIO';
    protected $TMPL_BST4 = 'FIO';

    protected $_dataName = '';
    protected $_dataSurname = '';
    protected $_dataPatronymic = '';

    protected $_hasErrorName = false;
    protected $_hasErrorSurname = false;
    protected $_hasErrorPatronymic = false;

    protected $_errorMessageName = 'Введите корректное имя';
    protected $_errorMessageSurname = 'Введите корректную фамилию';
    protected $_errorMessagePatronymic = 'Введите корректное отчество';


    static public function newFromArray($data = []) {
        /* @var FIO $entity */
        $entity = self::newInfo($data);
        $entity->_placeholder = $entity->parseDefault($entity->_placeholder);

        $entity->initDefault(static::castVar($data['defaultValue'],'TP\Text\Plain'));

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        $entity->_answers = static::getAnswers();

        return $entity;
    }


    protected function clearData(&$data)
    {
        if (isset($data['name']))
            $data['name'] = trim($data['name']);

        if (isset($data['surname']))
            $data['surname'] = trim($data['surname']);

        if (isset($data['patronymic']))
            $data['patronymic'] = trim($data['patronymic']);
    }

    protected function isEmptyData($data) {

        if (!isset($data['name']) or !isset($data['surname']) or !isset($data['patronymic'])) {
            $this->_hasErrorName = true;
            $this->_hasErrorSurname = true;
            $this->_hasErrorPatronymic = true;

            return true;
        }

        $empty = 0;

        if (!strlen($data['name'])) {
            if ($this->_required)
                $this->_hasErrorName = true;

            $empty +=1;
        }

        if (!strlen($data['surname'])) {
            if ($this->_required)
                $this->_hasErrorSurname = true;

            $empty +=1;
        }

        if (!strlen($data['patronymic'])) {
            if ($this->_required)
                $this->_hasErrorPatronymic = true;

            $empty +=1;
        }

        return ($empty==3)?true:false;
    }


    protected function applyData($data) {
        $this->_dataName = $data['name'];
        $this->_dataSurname = $data['surname'];
        $this->_dataPatronymic = $data['patronymic'];
    }

    protected function applyValue($value) {
        $this->_dataName = $value['name'];
        $this->_dataSurname = $value['surname'];
        $this->_dataPatronymic = $value['patronymic'];
    }

    protected function parseDefault($value) {
        $parts = explode('|',$value);

        return array(
            'surname' => isset($parts[0])?$parts[0]:'',
            'name' => isset($parts[1])?$parts[1]:'',
            'patronymic' => isset($parts[2])?$parts[2]:''
        );
    }


    protected function format() {
        $this->_dataText = "{$this->_dataSurname} {$this->_dataName} {$this->_dataPatronymic}";
    }


    protected function validate($data)
    {
        if (strlen($data['surname']) and !$this->validateWord($data['surname'])) {
            $this->_hasErrorSurname = true;
        }

        if (strlen($data['name']) and !$this->validateWord($data['name'])) {
            $this->_hasErrorName = true;
        }

        if (strlen($data['patronymic']) and !$this->validateWord($data['patronymic'])) {
            $this->_hasErrorPatronymic = true;
        }

        if ($this->_hasErrorSurname or $this->_hasErrorName or $this->_hasErrorPatronymic)
            return false;
        else
            return true;
    }

    protected function validateWord($data) {
        if (preg_match('~^[A-Za-zА-Яа-яЁё]+\-?[A-Za-zА-Яа-яЁё]+$~u', $data))
            return true;
        else
            return false;
    }

}