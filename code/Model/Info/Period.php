<?php

namespace Model\Info;
use Infr;

class Period extends \Model\Info {

    protected $TMPL_DEFAULT = 'Period';
    protected $TMPL_WC = 'wc/Period';
    protected $TMPL_VTB = 'Period';
    protected $TMPL_BST4 = 'Period';

    protected $_dataAmount = '';
    protected $_dataPeriod = '';

    protected $_amountText = '';


    protected $_periods = [
        1 => ['name' => 'дней'],
        2 => ['name' => 'месяцев'],
        3 => ['name' => 'лет'],
    ];


    protected function clearData(&$data)
    {
        if (isset($data['amount']))
            $data['amount'] = trim($data['amount']);

        if (isset($data['period']))
            $data['period'] = trim($data['period']);
    }

    protected function isEmptyData($data) {

        if (!isset($data['amount']) or !strlen($data['amount']))
            return true;

        if (!isset($data['period']) or !strlen($data['period']))
            return true;

        return false;
    }

    protected function applyData($data) {
        $this->_amountText = $data['amount'];
        $this->_dataPeriod = (int)$data['period'];
    }

    protected function applyValue($value) {
        $this->_dataAmount = $this->convertNumber($value['amount']);
        $this->_dataPeriod = (int)$value['period'];
    }

    protected function parseDefault($value) {
        $parts = explode('|',$value);

        return array(
            'amount' => isset($parts[0])?$parts[0]:'',
            'period' => isset($parts[1])?$parts[1]:'',
        );
    }

    protected function format() {
        $this->_amountText = is_float($this->_dataAmount)?number_format($this->_dataAmount, 2, ".", " "):number_format($this->_dataAmount, 0, ".", " ");

        $this->_dataText =  $this->_amountText.' '.$this->_periods[$this->_dataPeriod]['name'];
    }


    protected function validate($data)
    {
        if (!isset($data['amount']) or !isset($data['period']))
            return false;

        if (!(int)$data['period']>0)
            return false;

        $amount = str_replace([' ', ','],['', '.'], trim($data['amount']));

        if (preg_match('~^\-?[0-9]+(\.[0-9]+)?$~', $amount))
            return true;
        else
            return false;
    }

}