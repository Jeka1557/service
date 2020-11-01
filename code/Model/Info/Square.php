<?php

namespace Model\Info;
use Infr;

class Square extends \Model\Info {

    protected $TMPL_DEFAULT = 'Square';
    protected $TMPL_WC = 'wc/Square';
    protected $TMPL_VTB = 'Square';
    protected $TMPL_BST4 = 'Square';

    protected $_dataAmount = '';
    protected $_dataUnit = 0;

    protected $_amountText = '';

    protected $_units = [
        1 => ['name' => 'га'],
        2 => ['name' => 'кв.м'],
    ];


    protected function clearData(&$data)
    {
        if (isset($data['amount']))
            $data['amount'] = trim($data['amount']);

        if (isset($data['period']))
            $data['unit'] = trim($data['unit']);
    }

    protected function isEmptyData($data) {

        if (!isset($data['amount']) or !strlen($data['amount']))
            return true;

        if (!isset($data['unit']) or !strlen($data['unit']))
            return true;

        return false;
    }

    protected function applyData($data) {
        $this->_amountText = $data['amount'];
        $this->_dataUnit = (int)$data['unit'];
    }

    protected function applyValue($value) {
        $this->_dataAmount = $this->convertNumber($value['amount']);
        $this->_dataUnit = (int)$value['unit'];
    }

    protected function parseDefault($value) {
        $parts = explode('|',$value);

        return array(
            'amount' => isset($parts[0])?$parts[0]:'',
            'unit' => isset($parts[1])?$parts[1]:'',
        );
    }

    protected function format() {
        $this->_amountText = is_float($this->_dataAmount)?number_format($this->_dataAmount, 2, ".", " "):number_format($this->_dataAmount, 0, ".", " ");

        $this->_dataText =  $this->_amountText.' '.$this->_units[$this->_dataUnit]['name'];
    }


    protected function validate($data)
    {
        if (!isset($data['amount']) or !isset($data['unit']))
            return false;

        if (!(int)$data['unit']>0)
            return false;

        $amount = str_replace([' ', ','],['', '.'], trim($data['amount']));

        if (preg_match('~^\-?[0-9]+(\.[0-9]+)?$~', $amount))
            return true;
        else
            return false;
    }

}