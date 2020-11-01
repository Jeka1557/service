<?php

namespace Model\Info;
use Infr;

class Email extends \Model\Info {

    protected $_jsMask = 'js-mask-email';

    protected $_errorMessage = 'Укажите корректный email (пример: pochta@mail.ru)';

    protected function validate($data) {
        $email = $data;

        if (mb_strpos($email, '@', 0)===false)
            return false;


        if (extension_loaded('intl')) {
            list($account, $domain) = explode('@', $email);

            $account = idn_to_ascii($account, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            $domain = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

            $email = $account.'@'.$domain;
        }


        $result = filter_var($email, FILTER_VALIDATE_EMAIL);

        if ($result===false)
            return false;
        else
            return true;
    }

}