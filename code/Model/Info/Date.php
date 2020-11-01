<?php

namespace Model\Info;
use Infr;
use Model\Exception;


class Date extends \Model\Info {

    const ST_DATE = 'date';
    const ST_DATE_TIME = 'date_time';

    protected $TMPL_DEFAULT = 'Date';
    protected $TMPL_WC = 'wc/Date';
    protected $TMPL_VTB = 'vtb/Date';
    protected $TMPL_BST4 = 'Date2';


    /**
     * @var \DateTime
     */
    public $date;

    protected $_subtype = self::ST_DATE;

    protected $_jsMask = 'js-mask-date';

    protected $_errorMessage = 'Укажите корректную дату (пример: 12.04.2020)';

    protected $_pastDatesOnly = false;
    // protected $_currentCenturyOnly = false;


    protected $_useGreaterLimit = false;

    protected $_greaterInterval = 0;
    protected $_greaterPeriod = 'D';
    protected $_greaterDirection = 'G';


    protected $_useLessLimit = false;

    protected $_lessInterval = 0;
    protected $_lessPeriod = 'D';
    protected $_lessDirection = 'G';



    static public function newFromArray($data = []) {
        /* @var Date $entity */
        $entity = parent::newInfo($data);

        if (isset($data['settings']['subtype'])) {
            switch ($data['settings']['subtype']) {
                case self::ST_DATE_TIME:
                    $entity->_subtype = self::ST_DATE_TIME;
                    $entity->_jsMask = 'js-mask-date-time';
                    $entity->_errorMessage = 'Укажите корректную дату и время (пример: 12.04.2020 14:30)';
                    break;
            }
        }

        if (isset($data['settings']['past_dates_only']))
            $entity->_pastDatesOnly = static::castVar($data['settings']['past_dates_only'],'TP\TBool');

        //if (isset($data['settings']['current_century_only']))
        //    $entity->_currentCenturyOnly = static::castVar($data['settings']['current_century_only'],'TP\TBool');


        if (isset($data['settings']['use_greater_limit']))
            $entity->_useGreaterLimit = static::castVar($data['settings']['use_greater_limit'], 'TP\TBool', false);

        if (isset($data['settings']['greater_interval']))
            $entity->_greaterInterval = (int)$data['settings']['greater_interval'];

        if (isset($data['settings']['greater_period']))
            $entity->_greaterPeriod = $data['settings']['greater_period'];

        if (isset($data['settings']['greater_direction']))
            $entity->_greaterDirection = $data['settings']['greater_direction'];



        if (isset($data['settings']['use_less_limit']))
            $entity->_useLessLimit = static::castVar($data['settings']['use_less_limit'], 'TP\TBool', false);

        if (isset($data['settings']['less_interval']))
            $entity->_lessInterval = (int)$data['settings']['less_interval'];

        if (isset($data['settings']['less_period']))
            $entity->_lessPeriod = $data['settings']['less_period'];

        if (isset($data['settings']['less_direction']))
            $entity->_lessDirection = $data['settings']['less_direction'];



        $entity->initDefault(static::castVar($data['defaultValue'],'TP\Text\Plain'));

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        $entity->_answers = static::getAnswers();

        return $entity;
    }



    protected function validate($data)
    {
        $m = [];

        if ($this->_subtype==self::ST_DATE_TIME) {
            $pattern = '~(\d{2})\.(\d{2}).(\d{4})\s+(\d{2})\:(\d{2})~';
        } else {
            $pattern = '~(\d{2})\.(\d{2}).(\d{4})~';
        }

        if (!preg_match($pattern, $data, $m))
            return false;

        if (!checkdate($m[2], $m[1], $m[3]))
            return false;

        if ($this->_subtype==self::ST_DATE_TIME) {
            if (!((int)$m[4]>=0 and (int)$m[4]<24))
                return false;

            if (!((int)$m[5]>=0 and (int)$m[5]<60))
                return false;


            $date = "{$m[3]}-{$m[2]}-{$m[1]}-{$m[4]}-{$m[5]}";
        } else {
            $date = date("{$m[3]}-{$m[2]}-{$m[1]}-H-i");
        }



        if ($this->_pastDatesOnly and !($date <= date('Y-m-d-H-i'))) {
            $this->_errorMessage = 'Укажите корректную дату (не позднее текущей)';
            return false;
        }

        /*
        if ($this->currentCenturyOnly and !($date >= date('2000-01-01'))) {
            $this->_errorMessage = 'Укажите корректную дату (не меньше 01.01.2000)';
            return false;
        }
        */

        if ($this->_useGreaterLimit) {
            $dateMin = new \DateTime();
            $interval = new \DateInterval("P{$this->_greaterInterval}{$this->_greaterPeriod}");

            if ($this->_greaterDirection=='L')
                $dateMin->sub($interval);
            elseif ($this->_greaterDirection=='G')
                $dateMin->add($interval);

            if (!($date >= $dateMin->format('Y-m-d-H-i'))) {
                $this->_errorMessage = 'Укажите корректную дату (не ранее '.$dateMin->format('d.m.Y').')';
                return false;
            }
        }


        if ($this->_useLessLimit) {
            $dateMax = new \DateTime();
            $interval = new \DateInterval("P{$this->_lessInterval}{$this->_lessPeriod}");

            if ($this->_lessDirection=='L')
                $dateMax->sub($interval);
            elseif ($this->_lessDirection=='G')
                $dateMax->add($interval);

            if (!($date <= $dateMax->format('Y-m-d-H-i'))) {
                $this->_errorMessage = 'Укажите корректную дату (не позднее '.$dateMax->format('d.m.Y').')';
                return false;
            }
        }


        return true;
    }

    protected function applyValue($data) {
        $this->date = $this->convertDate($data, ($this->_subtype==self::ST_DATE_TIME));
    }

    protected function format() {
        if (is_null($this->date))
            $this->_dataText = '';

        if ($this->_subtype==self::ST_DATE_TIME)
            $this->_dataText = $this->date->format('d.m.Y H:i');
        else
            $this->_dataText = $this->date->format('d.m.Y');
    }


}