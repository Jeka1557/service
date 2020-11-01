<?php

namespace Model\Info;
use Infr;

class Payments extends \Model\Info {

    protected $_payments = [];
    protected $_defaultPayments = [];


    protected function prepareData($data) {
        $result = [];
        $items = [
            'dates' => [],
            'payments' => [],
        ];

        foreach ($data as $index => $value) {
            $pair = explode('_', $index);

            if (count($pair)!=2)
                continue;

            switch ($pair[0]) {
                case 'date':
                    $items['dates'][(int)$pair[1]] = $value;
                break;
                case 'payment':
                    $items['payments'][(int)$pair[1]] = $value;
                break;
            }
        }


        foreach ($items['dates'] as $k=>$date) {
            $date = $this->convertDate($date);

            if (is_null($date))
                continue;

            $payment = isset($items['payments'][$k])?$this->convertNumber($items['payments'][$k]):null;

            if (is_null($payment))
                continue;

            $result[] = ['date' => $date,  'payment' => $payment];
        }

        return $result;
    }

    protected function prepareDefault($value) {
        $result = [];

        $pairs = explode(',',$value);

        foreach ($pairs as $k=>$pair) {
            $values = explode('|', $pair);

            $date = isset($values[0])?$this->convertDate($values[0]):null;
            $payment = isset($values[1])?$this->convertNumber($values[1]):null;

            if (is_null($date) or is_null($payment))
                continue;

            $result[] = ['date' => $date,  'payment' => $payment];
        }

        return $result;
    }

    protected function checkValue($value) {
        return count($value)>0?true:false;
    }

    protected function applyValue($value) {
        $this->_payments = $value;
    }

    protected function applyDefaultValue($value) {
        $this->_defaultPayments = $value;
    }

    protected function formatValue($value) {
        $text = '';

        foreach ($value as $k=>$item) {
            $text .= ($k+1).'-я выплата: '.$item['date']->format('d.m.Y').' '.is_float($item['payment'])?number_format($item['payment'], 3, ".", " "):number_format($item['payment'], 0, ".", " ").' руб.\n';
        }

        return $text;
    }


    public function render($inGroup = false) {
        $data = [
            'info' => $this,
            'default' => false,
            'items' => [],
        ];

        if ($this->_hasValue)
            $data['items'] = json_encode($this->getPaymentItems($this->_payments));

        elseif ($this->_hasDefault) {
            $data['items'] = json_encode($this->getPaymentItems($this->_defaultPayments));
            $data['default'] = true;
        } else {
            $data['items'] = json_encode([0 =>[]]);
        }

        return $this->renderTemplate('Info', 'Payments', $data);
    }

    protected function getPaymentItems($items) {
        $result = [];

        foreach ($items as $k=>$item) {
            $date = $item['date']->format('d.m.Y');
            $payment = is_float($item['payment'])?number_format($item['payment'], 3, ".", " "):number_format($item['payment']);

            $result[] = [$date, $payment];
        }

        return $result;
    }
}