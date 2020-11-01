<?php

namespace Model\Info;
use Infr;

class Threshold extends \Model\Info {

    protected $_dataAssetsCost = '';
    protected $_dataRealtyCost = '';

    protected $_dealPercent = '';

    protected $_assetsCostText = '';
    protected $_realtyCostText = '';

    protected $TMPL_DEFAULT = 'Threshold';
    protected $TMPL_WC = 'wc/Threshold';
    protected $TMPL_VTB = 'Threshold';
    protected $TMPL_BST4 = 'Threshold';


    static public function newFromArray($data = []) {
        /* @var \Model\Info $entity */
        $entity = parent::newFromArray($data);

        if (!is_null($entity))
            $entity->_answers = static::getAnswers();

        return $entity;
    }


    protected function calcPercent($assetsCost, $realtyCost) {
        return $assetsCost>0?round($realtyCost*100/$assetsCost, 2):0;
    }


    public function export() {
        $data = parent::export();

        $data['assetsCost'] = $this->_dataAssetsCost;
        $data['dealAmount'] = $this->_dataRealtyCost;
        $data['percent'] = $this->_dealPercent;

        return $data;
    }


    protected function clearData(&$data)
    {
        if (isset($data['assets_cost']))
            $data['assets_cost'] = trim($data['assets_cost']);

        if (isset($data['realty_cost']))
            $data['realty_cost'] = trim($data['realty_cost']);
    }

    protected function isEmptyData($data) {

        if (!isset($data['assets_cost']) or !strlen($data['assets_cost']))
            return true;

        if (!isset($data['realty_cost']) or !strlen($data['realty_cost']))
            return true;

        return false;
    }

    protected function applyData($data) {
        $this->_assetsCostText = isset($data['assets_cost'])?$data['assets_cost']:'';
        $this->_realtyCostText = isset($data['realty_cost'])?$data['realty_cost']:'';
    }

    protected function applyValue($value) {
        $this->_dataAssetsCost = (float)str_replace([' ', ','],['', '.'], trim($value['assets_cost']));
        $this->_dataRealtyCost = (float)str_replace([' ', ','],['', '.'], trim($value['realty_cost']));

        $this->_dataAssetsCost = ((int)$this->_dataAssetsCost==$this->_dataAssetsCost)?(int)$this->_dataAssetsCost:$this->_dataAssetsCost;
        $this->_dataRealtyCost = ((int)$this->_dataRealtyCost==$this->_dataRealtyCost)?(int)$this->_dataRealtyCost:$this->_dataRealtyCost;

        $this->_dealPercent = $this->calcPercent($this->_dataAssetsCost, $this->_dataRealtyCost);
    }

    protected function parseDefault($value) {
        $parts = explode('|',$value);

        return array(
            'assets_cost' => isset($parts[0])?$parts[0]:'',
            'realty_cost' => isset($parts[1])?$parts[1]:'',
        );
    }

    protected function format() {
        $this->_assetsCostText = is_float($this->_dataAssetsCost)?number_format($this->_dataAssetsCost, 2, ".", " "):number_format($this->_dataAssetsCost, 0, ".", " ");
        $this->_realtyCostText = is_float($this->_dataRealtyCost)?number_format($this->_dataRealtyCost, 2, ".", " "):number_format($this->_dataRealtyCost, 0, ".", " ");

        $this->_dataText =  "Балансовая стоимость активов юр. лица: {$this->_assetsCostText}\nСумма обязательств по сделке: {$this->_realtyCostText}\nСоотношение размера сделки к стоимости активов юр. лица: {$this->_dealPercent}%";
    }


    protected function validate($data)
    {
        $assetsCost = str_replace([' ', ','],['', '.'], trim($data['assets_cost']));

        if (!preg_match('~^\-?[0-9]+(\.[0-9]+)?$~', $assetsCost))
            return false;

        $realtyCost = str_replace([' ', ','],['', '.'], trim($data['realty_cost']));

        if (!preg_match('~^\-?[0-9]+(\.[0-9]+)?$~', $realtyCost))
            return false;

        return true;
    }

}