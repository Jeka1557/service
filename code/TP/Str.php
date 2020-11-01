<?php


namespace TP\Str;

use TP;

/**
 * Str
 *
 * Базовый класс для строк.
 *
 * @package TP\Str
 */
abstract class Str extends TP\Type {

    /**
     * @var string регулярное выражение для проверки строк
     */
    protected static $reg;

    /**
     * Преобразование входного параметра к объектному типу Str.
     *
     * @static
     * @param mixed $value значение, преобразуемое к Str
     * @return string
     * @throws \TP\Exception\Str
     * @access public
     */
    public static function cast($value) {
        if (!preg_match(static::$reg, $value)) {
            throw new TP\Exception\Str(TP\Exception\Str::WRONG_CHAR, array(
                get_called_class()
            ));
        }

        return $value;
    }

}

/*
class En extends Str {
    static protected $reg = '~^[A-Za-z]+$~';
}

class AlnEn extends Str {
    static protected $reg = '~^[A-Za-z0-9]+$~';
}
*/


/**
 * WordEn
 *
 * Тип одно английское слово. Может содержать цифры, символы - и _.
 * Предназначен для передачи строковых идентификаторов.
 *
 * @package TP\Str
 */
class WordEn extends Str {

    /**
     * @var string регулярное выражение для проверки одного английского слова
     */
    protected static $reg = '~^[A-Za-z0-9\-_]+$~';
}



/**
 * NameEn
 *
 * Тип допустимых имен объектов на английском языке.
 * Содержит буквы, цифры, символы - и _. Может содержать пробел.
 *
 * @package TP\Str
 */
class NameEn extends Str {

    /**
     * @var string регулярное выражение для проверки имен объектов на английском языке
     */
    protected static $reg = '~^[A-Za-z0-9\-_\s]+$~';
}



/**
 * NameRu
 *
 * Тип допустимых имен объектов на русском языке.
 * Содержит буквы, цифры, символы - и _. Может содержать пробел.
 *
 * @package TP\Str
 */
class NameRu extends Str {

    /**
     * @var string регулярное выражение для проверки имен объектов на русском языке
     */
    protected static $reg = '~^[А-Яа-я0-9\-_\s]+$~u';
}



/**
 * Email
 *
 * Тип адреса электронной почты
 *
 * @package TP\Str
 */
class Email extends TP\Type {

    /**
     * @var string домен
     */
    protected static $hostname;

    /**
     * @var string первая часть адреса
     */
    protected static $localPart;

    /**
     *  @var string имя адресата
     */
    protected $displayName;

    /**
     * Добавляем в конструктор имя адресата
     *
     * @param string $value
     * @param string|null $displayName
     * @access public
     */
    public function __construct($value, $displayName = null) {
        $this->value = static::cast($value, $displayName);
        $this->displayName = $displayName;
    }

    /**
     * Проверка типа с добавление имени адресата
     *
     * @param string $value
     * @param string|null $displayName
     * @return bool
     * @access public
     */
    public static function check($value, $displayName = null) {
        try {
            static::cast($value, $displayName);

        } catch (TP\Exception\Email $e) {
            return false;
        }

        return true;
    }

    /**
     * Преобразование входного параметра к объектному типу Email
     *
     * Проверка на соответствие срандарту RFC 2822.
     *
     * @static
     * @param string $value значение, преобразуемое к Email
     * @param string|null $displayName имя адресата
     * @return string
     * @throws \TP\Exception\Email
     * @access public
     * @see http://www.regular-expressions.info/email.html
     */
    public static function cast($value, $displayName = null) {
        if ((strpos($value, '..') !== false) || (!preg_match('/^(.+)@(([^@\.]+)\.([^@\.]+\.)*([\w\d\-]+))$/u', $value, $matches))) {
            throw new TP\Exception\Email(TP\Exception\Email::NOT_VALID_EMAIL, array(
                get_called_class(),
                $value
            ));
        }

        static::$localPart = $matches[1];
        static::$hostname = $matches[2];

        if ((strlen(static::$localPart) > 64) || (strlen(static::$hostname) > 255)) {
            throw new TP\Exception\Email(TP\Exception\Email::MAX_LENGHT_EXCEED, array(
                get_called_class(),
                $value
            ));
        }

        if (!self::_validateHostnamePart(static::$hostname)) {
            throw new TP\Exception\Email(TP\Exception\Email::NOT_VALID_HOSTNAME, array(
                get_called_class(),
                static::$hostname,
                $value
            ));
        }

        if (!self::_validateLocalPart(static::$localPart)) {
            throw new TP\Exception\Email(TP\Exception\Email::NOT_VALID_LOCALPART, array(
                get_called_class(),
                static::$localPart,
                $value
            ));
        }

        if ($displayName && !self::_checkDisplayName($displayName)) {
            throw new TP\Exception\Email(TP\Exception\Email::NOT_VALID_DISPLAYNAME, array(
                get_called_class(),
                $displayName,
                $value
            ));
        }

        return $value;
    }

    /**
     * Проверка домена
     *
     * @static
     * @param string $hostname
     * @return bool
     * @access private
     *
     * /^([a-zA-Z0-9\.][\w\-_]*)+\.[a-zA-Z0-9]{2,6}$/
     *
     */
    private static function _validateHostnamePart($hostname) {
        return (bool) preg_match('/^([a-zA-Z0-9\.][\w\-_]*)+\.[a-zA-Z0-9\-_]/', $hostname);
    }

    /**
     * Проверка первой части адреса
     *
     * @static
     * @param string $localPart
     * @return bool
     * @access private
     */
    private static function _validateLocalPart($localPart) {
        // First try to match the local part on the common dot-atom format
        // Dot-atom characters are: 1*atext *("." 1*atext)
        // atext: ALPHA / DIGIT / and "!", "#", "$", "%", "&", "'", "*",
        //        "+", "-", "/", "=", "?", "^", "_", "`", "{", "|", "}", "~"
        $atext = 'a-zA-Z0-9\x21\x23\x24\x25\x26\x27\x2a\x2b\x2d\x2f\x3d\x3f\x5e\x5f\x60\x7b\x7c\x7d\x7e';
        if (!preg_match('/^[' . $atext . ']+(\x2e+[' . $atext . ']+)*$/', $localPart)) {
            // Try quoted string format
            // Quoted-string characters are: DQUOTE *([FWS] qtext/quoted-pair) [FWS] DQUOTE
            // qtext: Non white space controls, and the rest of the US-ASCII characters not
            //   including "\" or the quote character
            $noWsCtl = '\x01-\x08\x0b\x0c\x0e-\x1f\x7f';
            $qtext = $noWsCtl . '\x21\x23-\x5b\x5d-\x7e';
            $ws = '\x20\x09';

            if (!preg_match('/^\x22([' . $ws . $qtext . '])*[$ws]?\x22$/', $localPart)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверка имени адреса
     *
     * @static
     * @param string $displayName имя адресата
     * @return bool
     * @access private
     */
    private static function _checkDisplayName($displayName) {
        return preg_match('~^[A-Za-zА-ЯЁа-яё0-9\-_\s\.]+$~u', $displayName);
    }

    /**
     * Имя адреса
     *
     * @return null|string
     */
    public function getDisplayName() {
        return $this->displayName;
    }

}

/**
 * EmailReg
 *
 * Тип адреса электронной почты, который нужно использовать для ретистрации пользователей
 *
 * @deprecated
 * @package TP\Str\Email
 * @todo убрать тип, проверку возможности регистрации перенести в метод TP\Str\Email
 */
class EmailReg extends Email {

    /**
     * @var array запрещенные домены
     */
    protected static $excludedDomains = array(
        '0815.ru', '0clickemail.com', '10minutemail.com', '2prong.com', '3d-painting.com', '4warding.com',
        '4warding.net', '4warding.org', 'amilegit.com', 'anonbox.net', 'anonymbox.com', 'antispam.de',
        'beefmilk.com', 'binkmail.com', 'bio-muesli.net', 'bobmail.info', 'bofthew.com', 'brefmail.com',
        'bsnow.net', 'bugmenot.com', 'bumpymail.com', 'cosmorph.com', 'courrieltemporaire.com', 'cubiclink.com',
        'curryworld.de', 'cust.in', 'dacoolest.com', 'dandikmail.com', 'deadaddress.com', 'despam.it',
        'discardmail.com', 'discardmail.de', 'disposemail.com', 'dispostable.com', 'dodgeit.com', 'dodgit.com',
        'dodgit.org', 'dontreg.com', 'dontsendmespam.de', 'dump-email.info', 'dumpyemail.com', 'e4ward.com',
        'email60.com', 'emailinfive.com', 'emailmiser.com', 'emailsensei.com', 'emailtemporario.com.br', 'emailwarden.com',
        'fakeinbox.com', 'fakeinformation.com', 'fastacura.com', 'filzmail.com', 'fizmail.com', 'fr33mail.info',
        'get1mail.com', 'get2mail.fr', 'getonemail.com', 'getonemail.net', 'gishpuppy.com', 'great-host.in',
        'guerillamail.com', 'guerrillamailblock.com', 'h.mintemail.com', 'haltospam.com', 'hochsitze.com', 'hotpop.com',
        'hulapla.de', 'ieatspam.eu', 'ieatspam.info', 'imails.info', 'incognitomail.com', 'incognitomail.net',
        'incognitomail.org', 'insorg-mail.info', 'ipoo.org', 'jetable.com', 'jetable.net', 'jetable.org',
        'jnxjn.com', 'junk1e.com', 'klzlk.com', 'kulturbetrieb.info', 'lhsdv.com', 'litedrop.com',
        'lookugly.com', 'lopl.co.cc', 'm4ilweb.info', 'mail-temporaire.fr', 'mail.by', 'mail4trash.com',
        'mailcatch.com', 'maileater.com', 'mailexpire.com', 'mailin8r.com', 'mailinator.com', 'mailinator.net',
        'mailinator2.com', 'mailme.ir', 'mailme.lv', 'mailmetrash.com', 'mailnator.com', 'mailnull.com',
        'mailzilla.org', 'mbx.cc', 'meltmail.com', 'mierdamail.com', 'mintemail.com', 'monemail.fr.nf',
        'mt2009.com', 'mypartyclip.de', 'myphantomemail.com', 'mytrashmail.com', 'nepwk.com', 'no-spam.ws',
        'nobulk.com', 'noclickemail.com', 'nogmailspam.info', 'nomail2me.com', 'nomorespamemails.com', 'nospam4.us',
        'nospamfor.us', 'nospamthanks.info', 'nowmymail.com', 'onewaymail.com', 'owlpic.com', 'pjjkp.com',
        'politikerclub.de', 'pookmail.com', 'prtnx.com', 'qq.com', 'quickinbox.com', 'recode.me',
        'regbypass.com', 's0ny.net', 'safe-mail.net', 'safetymail.info', 'sandelf.de', 'saynotospams.com',
        'selfdestructingmail.com', 'sharklasers.com', 'shitmail.me', 'skeefmail.com', 'slopsbox.com', 'smellfear.com',
        'snakemail.com', 'sofort-mail.de', 'sogetthis.com', 'spam.la', 'spamavert.com', 'spambob.net',
        'spambob.org', 'spambog.com', 'spambog.de', 'spambog.ru', 'spambox.info', 'spambox.irishspringrealty.com',
        'spambox.us', 'spamcero.com', 'spamday.com', 'spamfree24.com', 'spamfree24.de', 'spamfree24.eu',
        'spamfree24.info', 'spamfree24.net', 'spamfree24.org', 'spamgourmet.com', 'spamherelots.com', 'spamhole.com',
        'spamify.com', 'spaminator.de', 'spamkill.info', 'spaml.com', 'spaml.de', 'spammotel.com',
        'spamobox.com', 'spamspot.com', 'spamthis.co.uk', 'supermailer.jp', 'suremail.info', 'teewars.org',
        'tempalias.com', 'tempe-mail.com', 'tempemail.biz', 'tempemail.com', 'tempemail.net', 'tempinbox.co.uk',
        'tempinbox.com', 'tempmail.it', 'tempomail.fr', 'temporaryemail.net', 'temporaryinbox.com', 'thanksnospam.info',
        'thankyou2010.com', 'thisisnotmyrealemail.com', 'throwawayemailaddress.com', 'tmailinator.com', 'trash-amil.com', 'trash-mail.com',
        'trash-mail.de', 'trash2009.com', 'trashmail.at', 'trashmail.com', 'trashmail.net', 'trashmail.ws',
        'trashmailer.com', 'trashymail.com', 'trashymail.net', 'trillianpro.com', 'tyldd.com', 'uggsrock.com',
        'webm4il.info', 'wegwerfemail.de', 'wh4f.org', 'whyspam.me', 'willselfdestruct.com', 'wuzupmail.net',
        'yopmail.com', 'yuurok.com', 'zehnminutenmail.de', 'zippymail.info'
    );

    /**
     * Преобразование входного параметра к объектному типу EmailReg
     *
     * @static
     * @param string $value значение, преобразуемое к EmailReg
     * @return string
     * @throws \TP\Exception\Email
     * @access public
     */
    public static function cast($value, $displayName = NULL) {
        $value = parent::cast($value, $displayName);
        $adminallow = (isset($_SERVER['adminallow']) && $_SERVER['adminallow']);

        if (in_array(static::$hostname, self::$excludedDomains) && !$adminallow) {
            throw new TP\Exception\Email(TP\Exception\Email::EXCLUDED_HOSTNAME, array(
                get_called_class(),
                static::$hostname,
                $value
            ));
        }

        return $value;
    }

}



/**
 * Md5
 *
 * Строка md5
 *
 * @package TP\Str
 */
class Md5 extends Str {

    /**
     * @todo непонятно для чего сделана возможность указания пустой строки
     * @var string регулярное выражение для проверки md5
     */
    protected static $reg = '~^[A-Za-z0-9]{32}$|^$~';
}



/**
 * UUID
 *
 * Строка UUID
 *
 * @package TP\Str
 */
class UUID extends Str {

    /**
     * @var string регулярное выражение для проверки UUID
     */
    protected static $reg = '~^[0-9abcdef]{8}\-[0-9abcdef]{4}\-[0-9abcdef]{4}\-[0-9abcdef]{4}\-[0-9abcdef]{12}$~';
}



/**
 * Phone
 *
 * Строка номера телефона.
 * Допустимый формат:
 *   2342(9999)234-345-123
 *   +2342(9999)234-345-123
 *   (9999)234-345-123
 *   2342 (9999) 234-345-123
 *   +2342 (9999) 234-345-123
 *   (9999)    234-345-123
 *   2342(9999)  234 345-123
 *   +2342(9999)  234-345 123
 *   (9999)  234 345 123
 *   234 345 123
 *   +123 234 345 123
 *   +75431234454
 *   +7 925 195 44 90 x23423
 *
 * @package TP\Str
 */
class Phone extends Str {

    /**
     * @var string регулярное выражение для проверки номера телефона
     */
    protected static $reg = '/^(?<country>(?:([\+])\d+|([^\+]))\d*)?\s*(\((?<city>\d{1,7})\))?\s*(?<phone>\d+([\-\s]\d+)*)(\s+x(?<additional>\d+))?$/';
}



/**
 * StrSafe
 *
 * "Безопасная" строка
 *
 * @package TP\Str
 */
class StrSafe extends Str {

    /**
     * @todo непонятно для чего сделана возможность указания пустой строки
     * @var string регулярное выражение для проверки безопасной строки
     */
    protected static $reg = '/^[^<\'"]*$/';

    static function initLocale($locale) {
        switch ($locale) {
            case 'uk':
                self::$reg = '/^[^<"]*$/';
            break;
            default:
                self::$reg ='/^[^<\'"]*$/';
            break;
        }
    }
}



/**
 * Url
 *
 * Ссылка
 *
 * @package TP\Str
 */
class Url extends Str {

    /**
     * @var string регулярное выражение для проверки ссылки
     */
    protected static $reg = '~(^(http://|https://)?([a-zA-Z0-9][\w\-_]*\.)+[a-zA-Z0-9]{2,6})|(^(http://|https://)?([а-яА-Я\-_\w]*\.)+[а-яА-Я]{2,6})~u';
}



/**
 * Digit
 *
 * Строковое представление числа
 *
 * @package TP\Str
 */
class Digit extends Str {

    /**
     * @var string регулярное выражение для проверки строкового представления числа
     */
    static protected $reg = '/^\d+$/';
}

/**
 * DigitNP
 *
 * Строковое представление числа которое может быть отрицательным
 *
 * @package TP\Str
 */
class DigitNP extends Str {

    /**
     * @var string регулярное выражение для проверки строкового представления числа
     */
    static protected $reg = '/^\-?\d+$/';
}