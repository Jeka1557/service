<?php

namespace Model\Info;
use Infr;

class Money extends \Model\Info {

    protected $TMPL_DEFAULT = 'Money';
    protected $TMPL_WC = 'wc/Money';
    protected $TMPL_VTB = 'vtb/Money';
    protected $TMPL_BST4 = 'Money';

    protected $_amount = '';
    protected $_currency = '';

    protected $_amountText = '';

    protected $_jsMask = 'js-mask-money';
    protected $_errorMessage = 'Укажите корректную денежную сумму';

    protected $_currencies = array(
        1 => array('name' => 'рубли', 'genetive' => 'рублей', 'symbol' => '&#x20bd;'),
        2 => array('name' => 'доллары США', 'genetive' => 'долларов США', 'symbol' => '$'),
        3 => array('name' => 'евро', 'genetive' => 'евро', 'symbol' => '&euro;'),
    );



    protected function clearData(&$data)
    {
        if (isset($data['amount']))
            $data['amount'] = trim($data['amount']);

        if (isset($data['currency']))
            $data['currency'] = trim($data['currency']);
    }

    protected function isEmptyData($data) {

        if (!isset($data['amount']) or !strlen($data['amount']))
            return true;

        if (!isset($data['currency']) or !strlen($data['currency']))
            return true;

        return false;
    }

    protected function applyData($data) {
        $this->_amountText = $data['amount'];
        $this->_currency = (int)$data['currency'];
    }

    protected function applyValue($value) {
        $this->_amount = $this->convertNumber($value['amount']);
        $this->_currency = (int)$value['currency'];
    }

    protected function parseDefault($value) {
        $parts = explode('|',$value);

        return array(
            'amount' => isset($parts[0])?$parts[0]:'',
            'currency' => isset($parts[1])?$parts[1]:'',
        );
    }

    protected function format() {
        $this->_amountText = is_float($this->_amount)?number_format($this->_amount, 2, ".", " "):number_format($this->_amount, 0, ".", " ");

        $this->_dataText =  $this->_amountText.' '.$this->_currencies[$this->_currency]['genetive'];
    }


    protected function validate($data)
    {
        if (!isset($data['amount']) or !isset($data['currency']))
            return false;

        if (!(int)$data['currency']>0)
            return false;

        $amount = str_replace([' ', ','],['', '.'], trim($data['amount']));

        if (preg_match('~^\-?[0-9]+(\.[0-9]+)?$~', $amount))
            return true;
        else
            return false;
    }

}