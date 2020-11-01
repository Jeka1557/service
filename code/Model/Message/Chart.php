<?php

namespace Model\Message;

use Model\Info;
use PT;
use TP;


/**
 * Class Chart
 */

class Chart extends \Model\Message {

    protected $TMPL_DEFAULT = 'Chart';
    protected $TMPL_WC = 'wc/Chart';
    protected $TMPL_VTB = 'wc/Chart';
    protected $TMPL_BST4 = 'wc/Chart';

    const ST_LINE = 'line';
    const ST_BAR = 'bar';
    const ST_PIE = 'pie';
    const ST_DOUGHNUT = 'doughnut';
    const ST_RADAR = 'radar';

    protected $_subtype;

    /**
     * @var TP\TColor
     */
    protected $_lineColor;

    protected $_items = [];

    protected $infoBlockIds = [];
    protected $expressionBlockIds = [];


    static public function newFromArray($data = [])
    {
        /* @var Chart $entity */
        $entity = parent::newFromArray($data);

        if (!isset($data['items'])) {
            throw new \Exception('Items is not set');
        }

        $entity->_subtype = static::castVar($data['settings']['subtype'], 'TP\Text\Plain');

        foreach ($data['items'] as $item) {
            if ($item['entityType']==PT\EntityType::INFO) {
                $entity->infoBlockIds[$item['entityId']] = $item['id'];

            } elseif ($item['entityType']==PT\EntityType::EXPRESSION) {
                $entity->expressionBlockIds[$item['entityId']] = $item['id'];
            }

            $entity->_items[$item['id']] = [
                'value' => 0,
                'label' => isset($item['settings']['label'])?$item['settings']['label']:'',
                'color' =>  new TP\TColor($item['settings']['color']),
            ];
        }

        switch ($entity->_subtype) {
            case self::ST_LINE:
            case self::ST_RADAR:
                $entity->_lineColor = new TP\TColor($data['settings']['line_color']);
            break;
        }


        return $entity;
    }


    public function setInfoCollection(\Model\Collection\Info $collection) {

        foreach ($collection as $item) {
            /** @var Info $item */
            if (isset($this->infoBlockIds[$item->id])) {
                $itemId = $this->infoBlockIds[$item->id];

                if (is_a($item, '\Model\Info\Text') or
                    is_a($item, '\Model\Info\LongText')) {

                    if (!$item->isEmpty('dataText')) {
                        $iValue = (integer)$item->dataText;
                        $fValue = (float)$item->dataText;

                        $this->_items[$itemId]['value'] = ($iValue == $fValue) ? $iValue : $fValue;
                    }

                } elseif (is_a($item, '\Model\Info\Number')) {
                    if (!$item->isEmpty('value'))
                        $this->_items[$itemId]['value'] = $item->value;

                } elseif (is_a($item, '\Model\Info\Money')) {
                    if (!$item->isEmpty('amount'))
                        $this->_items[$itemId]['value'] = $item->amount;

                } else {
                    throw new \Exception("Unsupported info type");
                }
            }
        }
    }


    public function setExpressionCollection(\Model\Collection\Expression $collection) {

        foreach ($collection as $item) {
            /** @var \Model\Expression $item */
            if (isset($this->expressionBlockIds[$item->id])) {
                $itemId = (int)$this->expressionBlockIds[$item->id];

                if (!is_null($item->value))
                    if (is_object($item->value))
                        $this->_items[$itemId]['value'] = clone $item->value;
                    else
                        $this->_items[$itemId]['value'] = $item->value;
            }
        }
    }
}