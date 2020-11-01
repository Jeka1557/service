<?php

namespace Model\Info;
use Infr;

class URL extends \Model\Info {

    protected $_jsMask = 'js-mask-url';

    protected $_value;
    protected $_url;

    protected $_errorMessage = 'Укажите корректный URL (пример: https://domain.zone/path?param=value#fragment)';



    protected function applyValue($data) {

        $url = parse_url($data);

        if (extension_loaded('intl'))
            $url['host'] = idn_to_ascii($url['host'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        $this->_url  = (isset($url['scheme'])?$url['scheme'].'://':'//').$url['host'].(isset($url['path'])?$url['path']:'/').(isset($url['query'])?'?'.$url['query']:'').(isset($url['fragment'])?'#'.$url['fragment']:'');
        $this->_value = $data;

    }


    protected function validate($data) {

        $url = parse_url($data);

        if (!isset($url['host']) or !isset($url['host']))
            return false;

        if (extension_loaded('intl'))
            $url['host'] = idn_to_ascii($url['host'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        $url = $url['scheme'].'://'.$url['host'].(isset($url['path'])?$url['path']:'/').(isset($url['query'])?'?'.$url['query']:'').(isset($url['fragment'])?'#'.$url['fragment']:'');

        $result = filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);

        if ($result===false)
            return false;
        else
            return true;

    }

}