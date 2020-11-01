<?php


namespace TP;



/**
 * Exception
 *
 * Исключение для типов
 *
 * @package TP
 */
class Exception extends \Exception {

    /**
     * @var array текст ошибки
     */
    protected $_messages = array();

    protected $_params = array();

    /**
     * Конструктор исключения.
     *
     * @param int $code код ошибки
     * @param string|array $message текст ошибки, либо массив переменных для подстановки в текст ошибки
     */
    public function __construct($code, $message = '') {
        $str = false;

        if (isset($this->_messages[$code])) {
            $str = $this->_messages[$code];
        }

        if ($message && !$str) {
            $str = (is_array($message)) ? array_shift($message) : (string) $message;
        }

        if (!$str) {
            $str = 'Undefined exception';
        }

        if ($message && is_array($message)) {
            $str = vsprintf($str, $message);
            $this->_params = $message;
        }

        parent::__construct($str, $code);
    }

    /**
     * Параметры подстановки в текст ошибки.
     *
     * @return array
     */
    public function getParams() {
        return $this->_params;
    }
}



/**
 * Type
 *
 * Базовый класс для объектного типа
 *
 * @package TP
 */
abstract class Type {

    /**
     * @var mixed значение
     */
    protected $value;

    /**
     * Конструктор типа.
     *
     * @param mixed $value значение, преобразуемое к выбранному типу
     * @access public
     */
    public function __construct($value) {
        $this->value = static::cast($value);
    }

    /**
     * Преобразование входного параметра к объектному типу.
     *
     * Кидает исключение если преобразование не удалось.
     *
     * @static
     * @param mixed $value значение, преобразуемое к выбранному типу
     * @return mixed
     * @throws Exception
     * @access public
     */
    public static function cast($value) {
        return null;
    }


    /*
    Эту функцию можно раскомментировать если кто-нибудь захочет вызывать статическую проверку для получения либо значения,либо null

    static public function nullCast($value) {
        try {
            return static::cast($value);
        } catch (Exception $e) {
            return null;
        }
    }
    */

    /**
     * Проверка соответствия выбранному типу.
     *
     * @static
     * @param mixed $value значение, проверяемое на соответствие выбранному типу
     * @return bool
     * @access public
     */
    public static function check($value) {
        try {
            static::cast($value);

        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Получение значения.
     *
     * @return mixed|void
     * @access public
     */
    public function val() {
        return $this->value;
    }

    /**
     * Преобразование значения к строке.
     *
     * @return string
     * @access public
     */
    public function __toString() {
        return $this->value . '';
    }

    /**
     * @static
     * @param null $locale
     * @return null|string
     * @access protected
     */
    protected static function locale($locale = null) {
        //if ($locale === null) {
        //$locale = \LSF2_App_Locale::getAlpha2();
        //}

        return $locale;
    }
}



/**
 * Set
 *
 * Значение из списка
 *
 * @package TP
 */
class Set extends Type {

    /**
     * @var array список допустимых значений
     */
    protected static $set = array();

    /**
     * Проверка присутствия в списке допустимых значений.
     *
     * @static
     * @param mixed $value
     * @return mixed
     * @throws Exception\Set
     */
    public static function cast($value) {
        if (!in_array($value, static::$set)) {
            throw new Exception\Set(Exception\Set::TYPE, array(
                get_called_class(),
                $value,
                var_export(static::$set, true)
            ));
        }

        return $value;
    }
}



/**
 * TInt
 *
 * Целое число
 *
 * @method int val()
 * @package TP
 */
class TInt extends Type {

    /**
     * @var int минимальное значение
     */
    protected static $min;

    /**
     * @var int максимальное значение
     */
    protected static $max;

    /**
     * Преобразование входного параметра к объектному типу TInt.
     *
     * @static
     * @param mixed $value значение, преобразуемое к TInt
     * @return int
     * @throws Exception\TInt
     * @access public
     */
    public static function cast($value) {
        $value = (int) $value;

        if (isset(static::$min) && $value < static::$min) {
            throw new Exception\TInt(Exception\TInt::MIN_BOUND, array(
                get_called_class(),
                $value,
                static::$min
            ));
        }


        if (isset(static::$max) && $value > static::$max) {
            throw new Exception\TInt(Exception\TInt::MAX_BOUND, array(
                get_called_class(),
                $value,
                static::$max
            ));
        }

        return $value;
    }
}



/**
 * TFloat
 *
 * Число с плавающей точкой
 *
 * @package TP
 */
class TFloat extends Type {

    /**
     * Преобразование входного параметра к объектному типу TFloat.
     *
     * @static
     * @param mixed $value значение, преобразуемое к TFloat
     * @return float
     * @access public
     */
    public static function cast($value) {
        $value = (float) $value;
        return $value;
    }

    public function __toString() {
        return sprintf('%.0f', $this->value);
    }
}



/**
 * TBool
 *
 * Булев тип
 *
 * @package TP
 */
class TBool extends Type {

    /**
     * Преобразование входного параметра к булеву типу.
     *
     * @static
     * @param mixed $value значение, преобразуемое к Bool
     * @return bool
     * @access public
     */
    public static function cast($value) {
        if (!in_array($value, array(true, false, 0, 1, '0', '1', 't', 'f', 'y', 'yes', 'no', 'n'), true)) {
            throw new Exception\TBool(Exception\TBool::TYPE, array(
                get_called_class()
            ));
        }

        if (in_array((string) $value, array('f', 'false', 'n', 'no'), true)) {
            $value = false;
        }

        return (boolean) $value;
    }

    /**
     * Преобразование значения к строке.
     *
     * @return string
     * @access public
     */
    public function __toString() {
        if ($this->value) {
            return '1';

        } else {
            return '0';
        }
    }

    /**
     * Преобразование значения к строке, понятной PostgreSQL.
     *
     * @return string
     * @access public
     */
    public function toPgBool() {
        if ($this->value) {
            return 't';

        } else {
            return 'f';
        }
    }
}



/**
 * Date
 *
 * Тип дата. По умолчанию принимает дату и возвращает в формате YYYY-MM-DD.
 *
 * @property int timestamp
 * @package TP
 */
class Date extends Type {

    /**
     * Формат даты по умолчанию
     */
    const FORMAT = 'Y-m-d';

    /**
     * @var array дополнительные и переопределенные флаги и значения по локалям
     */
    protected static $locales = array(
        'en' => array(
            'k' => array(1 => 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'),
            'D' => array(1 => 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'),
            'l' => array(1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),

            'q' => array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
            'Q' => array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
            'M' => array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
            'F' => array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'),
            'x' => array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'),
            'X' => array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'),
        ),

        'ru' => array(
            'k' => array(1 => 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'),
            'D' => array(1 => 'Пн.', 'Вт.', 'Ср.', 'Чт.', 'Пт.', 'Сб.', 'Вс.'),
            'l' => array(1 => 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'),

            'q' => array(1 => 'Янв', 'Фев', 'Мар', 'Апр', 'Мая', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'),
            'Q' => array(1 => 'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'),
            'M' => array(1 => 'Янв.', 'Февр.', 'Марта', 'Апр.', 'Мая', 'Июня', 'Июля', 'Авг.', 'Сент.', 'Oкт.', 'Нояб.', 'Дек.'),
            'F' => array(1 => 'Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'),
            'x' => array(1 => 'Янв.', 'Февр.', 'Март', 'Апр.', 'Май', 'Июнь', 'Июль', 'Авг.', 'Сент.', 'Oкт.', 'Нояб.', 'Дек.'),
            'X' => array(1 => 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'),
        )
    );

    /**
     * @var \DateTime
     */
    protected $value;

    /**
     * @var string формат даты/времени
     */
    protected $format;

    /**
     * @var string строка запрещенных флагов
     */
    protected static $illegalFlags = 'aABgGhHisucr';

    /**
     * Конструктор типа.
     *
     * @param string $value
     * @param string $format
     * @throws Exception\Date
     * @access public
     */
    public function __construct($value = null, $format = Date::FORMAT) {
        if (!static::checkFormat($format)) {
            throw new Exception\Date(Exception\Date::FORMAT, array($format));
        }

        $this->format = $format;

        if ($value === null) {
            $value = date($format);
        }

        $this->value = \DateTime::createFromFormat($format, static::cast($value, $format));
    }

    /**
     * Вызов различных геттеров.
     *
     * @param $string
     * @return mixed|string
     * @access public
     */
    public function __get($string) {
        $func = 'get_' . $string;
        if (method_exists($this, $func)) {
            return $this->$func();

        } else {
            return $this->val();
        }
    }

    /**
     * Преобразование значения к строке.
     *
     * @return string
     * @access public
     */
    public function __toString() {
        return $this->val();
    }

    /**
     * Проверка соответствия выбранному типу.
     *
     * @static
     * @param mixed $value значение, проверяемое на соответствие выбранному типу
     * @param string $format
     * @return bool
     * @access public
     */
    public static function check($value, $format = Date::FORMAT) {
        try {
            if (!static::checkFormat($format)) {
                throw new Exception\Date(Exception\Date::FORMAT, array($format));
            }

            static::cast($value, $format);

        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Проверка строкового представления даты на соответствие требуемому формату.
     *
     * @static
     * @param string $value строковое представление даты
     * @param string $format
     * @return string
     * @throws Exception\Date
     * @access public
     */
    public static function cast($value, $format = Date::FORMAT) {
        if (!static::checkFormat($format)) {
            throw new Exception\Date(Exception\Date::FORMAT, array($format));
        }

        $parse = date_parse_from_format($format, $value);
        if (!$parse) {
            throw new Exception\Date(Exception\Date::TYPE, array(get_called_class(), $value));
        }

        if ($parse['warnings']) {
            throw new Exception\Date(Exception\Date::TYPE, array(get_called_class(), implode(', ', $parse['warnings'])));
        }

        if ($parse['errors']) {
            throw new Exception\Date(Exception\Date::TYPE, array(get_called_class(), implode(', ', $parse['errors'])));
        }

        return $value;
    }

    /**
     * Получение значения.
     *
     * @param string $format
     * @param string|null $locale
     * @return string
     * @access public
     */
    public function val($format = null, $locale = null) {
        $locale = self::locale($locale);

        if ($format === null) {
            $format = $this->format;
        }

        if ($format === null) {
            $format = self::FORMAT;
        }

        if (!static::checkFormat($format)) {
            throw new Exception\Date(Exception\Date::FORMAT, array($format));
        }

        $value = &$this->value;
        $locales = &self::$locales;
        return preg_replace_callback('/\w/', function($matches) use (&$value, &$locales, $locale) {
            /* @var $value \DateTime */
            switch ($matches[0]) {
                case 'k':
                case 'D':
                case 'l':
                    return $locales[$locale][$matches[0]][(int) $value->format('N')];

                case 'q':
                case 'Q':
                case 'M':
                case 'F':
                case 'x':
                case 'X':
                    return $locales[$locale][$matches[0]][(int) $value->format('n')];

                default:
                    return $value->format($matches[0]);
            }
        }, $format);
    }

    /**
     * Получение соответствующего указанной дате дня недели от 1 (понедельник) до 7 (воскресенье).
     *
     * @return int
     * @access public
     */
    public function getDayOfWeek() {
        return (int) $this->value->format('N');
    }

    /**
     * Получение timestamp значения даты.
     *
     * @return int
     * @access private
     */
    private function get_timestamp() {
        return $this->value->getTimestamp();
    }

    /**
     * Проверка формата на наличие запрещенных флагов.
     *
     * @param string $format
     * @return bool true если формат допустим
     */
    protected static function checkFormat($format) {
        return ((!static::$illegalFlags || strpbrk($format, static::$illegalFlags) === false) ? true : false);
    }


    public static function setLocales($locales) {
        static::$locales = $locales;
    }
}



/**
 * DateTime
 *
 * Тип дата со временем.
 *
 * @property int timestamp
 * @package TP
 */
class DateTime extends Date {

    /**
     * Формат даты со временем по умолчанию
     */
    const FORMAT = 'Y-m-d H:i:s';

    /**
     * @var string строка запрещенных флагов
     */
    protected static $illegalFlags = '';

    /**
     * Конструктор типа.
     *
     * @param string $value
     * @param string $format
     * @throws Exception\DateTime
     * @access public
     */
    public function __construct($value, $format = DateTime::FORMAT) {
        try {
            parent::__construct($value, $format);

        } catch (Exception\Date $e) {
            throw new Exception\DateTime(Exception\DateTime::TYPE, $e->getParams());
        }
    }

    /**
     * Проверка строкового представления даты на соответствие требуемому формату.
     *
     * @static
     * @param string $value строковое представление даты
     * @param string $format
     * @return string
     * @throws Exception\DateTime
     * @access public
     */
    public static function cast($value, $format = DateTime::FORMAT) {
        try {
            return parent::cast($value, $format);

        } catch (Exception\Date $e) {
            throw new Exception\DateTime(Exception\DateTime::TYPE, $e->getParams());
        }
    }

    /**
     * Проверка соответствия выбранному типу.
     *
     * @static
     * @param mixed $value значение, проверяемое на соответствие выбранному типу
     * @param string $format
     * @return bool
     * @access public
     */
    public static function check($value, $format = DateTime::FORMAT) {
        return parent::check($value, $format);
    }

    /**
     * Получение значения.
     *
     * @param string $format
     * @param string|null $locale
     * @return string
     * @access public
     */
    public function val($format = null, $locale = null) {
        return parent::val($format, $locale);
    }
}



/**
 * UInt8
 *
 * Положительное целое 8 байт
 *
 * @method static bool check($value)
 * @method int val()
 * @package TP
 */
class UInt8 extends TInt {

    /**
     * @var int минимальное значение
     */
    protected static $min = 0;

    /**
     * @var int максимальное значение
     */
    protected static $max = 9223372036854775807;

    /**
     * Преобразование входного параметра к объектному типу PInt8.
     *
     * @static
     * @param mixed $value значение, преобразуемое к Int
     * @return float
     * @throws Exception\TInt
     * @access public
     */
    public static function cast($value) {
        $value = floor((float) $value);

        if (isset(static::$min) && $value < static::$min) {
            throw new Exception\TInt(Exception\TInt::MIN_BOUND, array(
                get_called_class(),
                $value,
                static::$min
            ));
        }

        if (isset(static::$max) && $value > static::$max) {
            throw new Exception\TInt(Exception\TInt::MAX_BOUND, array(
                get_called_class(),
                $value,
                static::$max
            ));
        }

        return $value;
    }

    public function __toString() {
        return sprintf('%.0f', $this->value);
    }
}



/**
 * UInt4
 *
 * Положительное целое 4 байта
 *
 * @method static int cast($value)
 * @method static bool check($value)
 * @method int val()
 * @package TP
 */
class UInt4 extends TInt {

    /**
     * @var int минимальное значение
     */
    protected static $min = 0;

    /**
     * @var int максимальное значение
     */
    protected static $max = 4294967295;
}



/**
 * UInt2
 *
 * Положительное целое 2 байта
 *
 * @method static int cast($value)
 * @method static bool check($value)
 * @method int val()
 * @package TP
 */
class UInt2 extends UInt4 {

    /**
     * @var int минимальное значение
     */
    protected static $min = 0;

    /**
     * @var int максимальное значение
     */
    protected static $max = 65535;
}



/**
 * UInt1
 *
 * Положительное целое 1 байт
 *
 * @method static int cast($value)
 * @method static bool check($value)
 * @method int val()
 * @package TP
 */
class UInt1 extends UInt2 {

    /**
     * @var int минимальное значение
     */
    protected static $min = 0;

    /**
     * @var int максимальное значение
     */
    protected static $max = 255;
}



/**
 * PInt8
 *
 * Положительное целое 8 байт, большее 0
 *
 * @method static bool check($value)
 * @method float val()
 * @package TP
 */
class PInt8 extends UInt8 {

    /**
     * @var int минимальное значение
     */
    protected static $min = 1;

    /**
     * @var int максимальное значение
     */
    protected static $max = 9223372036854775807;
}



/**
 * PInt4
 *
 * Положительное целое 4 байта, большее 0
 *
 * @method static int cast($value)
 * @method static bool check($value)
 * @method int val()
 * @package TP
 */
class PInt4 extends TInt {

    /**
     * @var int минимальное значение
     */
    protected static $min = 1;

    /**
     * @var int максимальное значение
     */
    protected static $max = 2147483647;
}



/**
 * PInt2
 *
 * Положительное целое 2 байта, большее 0
 *
 * @method static int cast($value)
 * @method static bool check($value)
 * @method int val()
 * @package TP
 */
class PInt2 extends PInt4 {

    /**
     * @var int минимальное значение
     */
    protected static $min = 1;

    /**
     * @var int максимальное значение
     */
    protected static $max = 32767;
}



/**
 * PInt1
 *
 * Положительное целое 1 байт, большее 0
 *
 * @method static int cast($value)
 * @method static bool check($value)
 * @method int val()
 * @package TP
 */
class PInt1 extends PInt2 {

    /**
     * @var int минимальное значение
     */
    protected static $min = 1;

    /**
     * @var int максимальное значение
     */
    protected static $max = 127;
}



/**
 * WeekDay
 *
 * Множество значений дней недели
 *
 * @package TP
 */
class WeekDay extends Set {
    const MON = 1;
    const TUE = 2;
    const WED = 3;
    const THU = 4;
    const FRI = 5;
    const SAT = 6;
    const SUN = 0;

    protected static $set = array(self::MON, self::TUE, self::WED, self::THU, self::FRI, self::SAT, self::SUN);
}



/**
 * Sex
 *
 * Пол.
 *
 * @property string short пользовательское короткое обозначения пола
 * @property string medium пользовательское среднее обозначения пола
 * @property string long пользовательское длинное обозначения пола
 * @property int alias алиас пола
 * @property int key ID пола в соответствии с ISO 5218
 * @property string sign знак пола
 * @package TP
 */
class Sex extends Type {

    /**
     * Формат по умолчанию key.
     */
    const FORMAT = 'a';

    protected static $locales = array(
        // локаль по умолчанию
        'ru' => array(
            //key (ISO 5218), alias, sign, short, medium, long
            array(1, 'male', '&#x2642;', 'М', 'Муж.', 'Мужской'),
            array(2, 'female', '&#x2640;', 'Ж', 'Жен.', 'Женский'),
        ),

        'en' => array(
            //key (ISO 5218), alias, sign, short, medium, long
            array(1, 'male', '&#x2642;', 'M', 'Male', 'Male'),
            array(2, 'female', '&#x2640;', 'F', 'Female', 'Female'),
        )
    );

    /**
     * @var array массив допустимых флагов в формате array('флаг' => индекс в $locales)
     */
    protected static $flags = array(
        'n' => 0,   // key
        'a' => 1,   // alias
        's' => 2,   // sign
        'S' => 3,   // short
        'M' => 4,   // medium
        'L' => 5,   // long
    );

    /**
     * @var string формат пола
     */
    protected $format;

    /**
     * Конструктор типа.
     *
     * Установка значений только для EN локали.
     *
     * @param string|int $value представление пола
     * @param string|null $format
     * @throws Exception\Sex
     * @access public
     */
    public function __construct($value, $format = null) {
        $format = self::autoFormat($value, $format);

        $key = null;
        self::cast($value, $format, $key);

        $this->value = $key;
        $this->format = $format;
    }

    /**
     * Преобразование значения к строке.
     *
     * @return string
     * @access public
     */
    public function __toString() {
        return $this->val('a');
    }

    /**
     * @param string $prop
     * @return mixed|string
     * @access public
     */
    public function __get($prop) {
        switch ($prop) {
            case 'short':
            case 'medium':
            case 'long':
            case 'alias':
            case 'key':
            case 'sign':
                return $this->$prop();
        }
    }

    /**
     * Проверка представления пола на соответствие требуемому формату.
     *
     * Проверка значений только для EN локали.
     *
     * @static
     * @param string|int $value представление пола
     * @param string|null $format
     * @param int|null $key ключ
     * @return string|int
     * @throws Exception\Sex
     * @access public
     */
    public static function cast($value, $format = Sex::FORMAT, &$key = null) {
        if (!static::checkFormat($format)) {
            throw new Exception\Sex(Exception\Sex::FORMAT, array($format));
        }

        $idx = self::$flags[$format];

        // если передано обозначение не в Unicode 16CC HTML мнемонике
        if ($format == 's' && strpos($value, '&#x') !== 0) {
            $value = '&#x' . sprintf('%04x', ord((string) $value)) . ';';
        }

        foreach (self::getLocales('en') as $k => $cur) {
            if ($cur[$idx] == $value) {
                $key = $k;
                return $value;
            }
        }

        throw new Exception\Sex(Exception\Sex::VALUE_NOT_FOUND, array(get_called_class(), $value));
    }

    /**
     * Проверка соответствия выбранному типу.
     *
     * Проверка значений только для EN локали.
     *
     * @static
     * @param mixed $value значение, проверяемое на соответствие выбранному типу
     * @param string|null $format
     * @return bool
     * @access public
     */
    public static function check($value, $format = Sex::FORMAT) {
        try {
            if (!static::checkFormat($format)) {
                throw new Exception\Sex(Exception\Sex::FORMAT, array($format));
            }

            static::cast($value, $format);

        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Получение значения.
     *
     * @param string $format
     * @param string|null $locale
     * @return string
     * @throws Exception\Sex
     * @access public
     */
    public function val($format = null, $locale = null) {
        if ($format === null) {
            $format = $this->format;
        }

        if ($format === null) {
            $format = self::FORMAT;
        }

        if (!static::checkFormat($format)) {
            throw new Exception\Sex(Exception\Sex::FORMAT, array($format));
        }

        $locales = self::getLocales($locale);
        return $locales[$this->value][self::$flags[$format]];
    }

    /**
     * ID пола в соответствии с ISO 5218.
     *
     * @return int
     * @access public
     */
    public function key() {
        $locales = self::getLocales('en');
        return $locales[$this->value][self::$flags['n']];
    }

    /**
     * Алиас пола.
     *
     * @return int
     * @access public
     */
    public function alias() {
        $locales = self::getLocales('en');
        return $locales[$this->value][self::$flags['a']];
    }

    /**
     * Знак пола.
     *
     * @return string
     * @access public
     */
    public function sign() {
        $locales = self::getLocales('en');
        return $locales[$this->value][self::$flags['s']];
    }

    /**
     * Установка и получение пользовательского короткого обозначения пола.
     *
     * @return string
     * @throws Exception\Sex
     * @access public
     */
    public function short() {
        static $short = array();

        // статический вызов
        if (empty($this)) {
            self::setSign(func_get_args(), $short);
            return;
        }

        if (isset($short[$this->value])) {
            return $short[$this->value];
        }

        $locales = self::getLocales();
        return $locales[$this->value][self::$flags['S']];
    }

    /**
     * Установка и получение пользовательского среднего обозначения пола.
     *
     * @return string
     * @throws Exception\Sex
     * @access public
     */
    public function medium() {
        static $medium = array();

        // статический вызов
        if (empty($this)) {
            self::setSign(func_get_args(), $medium);
            return;
        }

        if (isset($medium[$this->value])) {
            return $medium[$this->value];
        }

        $locales = self::getLocales();
        return $locales[$this->value][self::$flags['M']];
    }

    /**
     * Установка и получение пользовательского полного обозначения пола.
     *
     * @return string
     * @throws Exception\Sex
     * @access public
     */
    public function long() {
        static $long = array();

        // статический вызов
        if (empty($this)) {
            self::setSign(func_get_args(), $long);
            return;
        }

        if (isset($long[$this->value])) {
            return $long[$this->value];
        }

        $locales = self::getLocales();
        return $locales[$this->value][self::$flags['L']];
    }

    /**
     * Получение списка возможных значений в формате array([n] => [$format], ...).
     *
     * @static
     * @param string $format
     * @param null|string $locale
     * @return array
     * @throws Exception\Sex
     * @access public
     */
    public static function getList($format = Sex::FORMAT, $locale = null) {
        if (!static::checkFormat($format)) {
            throw new Exception\Sex(Exception\Sex::FORMAT, array($format));
        }

        $locales = self::getLocales($locale);
        $data = array();

        foreach ($locales as $v) {
            $data[$v[self::$flags['n']]] = $v[self::$flags[$format]];
        }

        return $data;
    }

    /**
     * Проверка формата на наличие запрещенных флагов.
     *
     * @static
     * @param string $format
     * @return bool true если формат допустим
     * @access protected
     */
    protected static function checkFormat($format) {
        return (isset(self::$flags[$format]) ? true : false);
    }

    /**
     * Автоматическое определение формата по значению валюты.
     *
     * Определяет только форматы: n, s, a.
     *
     * @static
     * @param $value
     * @param null|string $format
     * @return null|string
     * @throws Exception\Sex
     * @access protected
     */
    protected static function autoFormat($value, $format = null) {
        if ($format === null) {
            if (is_numeric($value) || preg_match('/^\d+$/', $value)) {
                $format = 'n';

            } elseif (is_string($value)) {
                if (strpos($value, '&#x') === 0) {
                    $format = 's';

                } else {
                    $format = 'a';
                }
            }
        }

        if (!static::checkFormat($format)) {
            throw new Exception\Sex(Exception\Sex::FORMAT_NOT_FOUND, array(get_called_class()));
        }

        return $format;
    }

    /**
     * Установка описания пола.
     *
     * @static
     * @param array $data
     * @param $target
     * @throws Exception\Sex
     * @access protected
     */
    protected static function setSign(array $data, &$target) {
        if (count($data) != 2) {
            throw new Exception\Sex(Exception\Sex::ARGUMENTS);
        }

        $alias = $data[0];
        $value = $data[1];

        $key = null;
        self::cast($alias, 'a', $key);

        $target[$key] = $value;
    }

    /**
     * Получение массива данных для локали.
     *
     * Русская локаль по умолчанию, если не найдена требуемая.
     *
     * @static
     * @param null|string $locale
     * @return mixed
     * @access protected
     */
    protected static function getLocales($locale = null) {
        $locale = self::locale($locale);
        return (isset(self::$locales[$locale]) ? self::$locales[$locale] : self::$locales['ru']);
    }

    public static function setLocales($locales) {
        static::$locales = $locales;
    }

}



/**
 * Currency
 *
 * Валюта.
 *
 * @property mixed short пользовательское короткое обозначения валюты
 * @property mixed medium пользовательское среднее обозначения валюты
 * @property mixed long пользовательское полное обозначения валюты
 * @property int number трёхзначный цифровой код валюты с ISO 4217
 * @property int key внутренний ключ валюты
 * @package TP
 */
class Currency extends Type {

    const USD = 'USD';
    const EUR = 'EUR';
    const RUB = 'RUB';
    const UAH = 'UAH';
    const TUR = 'TRY';
    const BYR = 'BYR';
    const KZT = 'KZT';

    /**
     * Формат по умолчанию alpha3.
     */
    const FORMAT = 'C';

    /**
     * @var array массив обозначений валюты.
     */
    protected static $locales = array(
        // локаль по умолчанию
        'en' => array(
            //key, number, alpha2, alpha3, sign, alpha2+sign
            array(1, 840, 'US', 'USD', '&#x0024;'           , 'US&#x0024;')         , // Доллар США
            array(2, 978, 'EU', 'EUR', '&#x20ac;'           , 'EU&#x20ac;')         , // Евро
            array(3, 643, 'RU', 'RUB', '&#x0420;'           , 'RU&#x0420;')         , // Рубль
            array(4, 980, 'UA', 'UAH', '&#x20B4;'           , 'UA&#x20B4;')         , // Гривна
            array(5, 974, 'BY', 'BYR', '<span class="bel-rub">&#1041;</span>'   , 'BY<span class="bel-rub">&#1041;</span>') , // Белорусский рубль
            array(6, 398, 'KZ', 'KZT', '<span class="tenge">&#97;</span>'   , 'KZ<span class="tenge">&#97;</span>') , // Казакхский тенгэ
            array(7, 949, 'TL', 'TRY', '&#x00A3;'           , 'TL&#x00A3;')         , // Турецкая лира
        ),

        'ru' => array(
            array(1, 840, 'US', 'длр.'      , '&#x0024;'                , 'US&#x0024;'), // Доллар США
            array(2, 978, 'EU', 'евро'      , '&#x20ac;'                , 'EU&#x20ac;'), // Евро
            array(3, 643, 'р.', 'руб.'      , '&#x0420;'                , 'RU&#x0420;'), // Рубль
            array(4, 980, 'UA', 'грн.'      , '&#x20B4;'                , 'UA&#x20B4;'), // Гривна
            array(5, 974, 'р.', 'руб.' , '<span class="bel-rub">&#1041;</span>'        , 'BY<span class="bel-rub">&#1041;</span>'), // Белорусский рубль
            array(6, 398, 'KZ', 'тен.' , '<span class="tenge">&#97;</span>'        , 'KZ<span class="tenge">&#97;</span>'), // // Казакхский тенгэ
            array(7, 949, 'TL', 'тлр.'      , '&#x00A3;'                , 'TL&#x00A3;'), // Турецкая лира
        )
    );

    /**
     * @var array массив допустимых флагов в формате array('флаг' => индекс в $locales)
     */
    protected static $flags = array(
        'n' => 0,   // key
        'N' => 1,   // number
        'c' => 2,   // alpha2
        'C' => 3,   // alpha3
        's' => 4,   // sign
        'S' => 5,   // alpha2+sign
    );

    /**
     * @var string формат даты/времени
     */
    protected $format;


    /**
     * Конструктор типа.
     *
     * Установка значений только для EN локали.
     *
     * @param string|int $value представление валюты
     * @param string|null $format
     * @throws Exception\Currency
     * @access public
     */
    public function __construct($value, $format = null) {
        $format = self::autoFormat($value, $format);

        $key = null;
        self::cast($value, $format, $key);

        $this->value = $key;
        $this->format = $format;
    }

    /**
     * Преобразование значения к строке.
     *
     * @return string
     * @access public
     */
    public function __toString() {
        return $this->val('C');
    }

    /**
     * @param string $prop
     * @return mixed|string
     * @access public
     */
    public function __get($prop) {
        switch ($prop) {
            case 'short':
            case 'medium':
            case 'long':
            case 'number':
            case 'key':
                return $this->$prop();
        }
    }

    /**
     * Проверка представления валюты на соответствие требуемому формату.
     *
     * Проверка значений только для EN локали.
     *
     * @static
     * @param string|int $value представление валюты
     * @param string|null $format
     * @param int|null $key ключ фалюты
     * @return string|int
     * @throws Exception\Currency
     * @access public
     */
    public static function cast($value, $format = Currency::FORMAT, &$key = null) {
        if (!static::checkFormat($format)) {
            throw new Exception\Currency(Exception\Currency::FORMAT, array($format));
        }

        $idx = self::$flags[$format];

        // если передано обозначение не в Unicode 16CC HTML мнемонике
        if ($format == 's' && strpos($value, '&#x') !== 0) {
            $dec = html_entity_decode($value, ENT_QUOTES, "cp1251");
            $enc = mb_convert_encoding($dec, "UTF-16BE", "cp1251");
            $value = "&#x" . sprintf("%04x", ord($enc[0]) << 8 | ord($enc[1])) . ';';
        }

        foreach (self::getLocales('en') as $k => $cur) {
            if (strcasecmp($cur[$idx], $value) == 0) {
                $key = $k;
                return $value;
            }
        }

        throw new Exception\Currency(Exception\Currency::VALUE_NOT_FOUND, array(get_called_class(), $value));
    }

    /**
     * Проверка соответствия выбранному типу.
     *
     * Проверка значений только для EN локали.
     *
     * @static
     * @param mixed $value значение, проверяемое на соответствие выбранному типу
     * @param string|null $format
     * @return bool
     * @access public
     */
    public static function check($value, $format = Currency::FORMAT) {
        try {
            if (!static::checkFormat($format)) {
                throw new Exception\Currency(Exception\Currency::FORMAT, array($format));
            }

            static::cast($value, $format);

        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Получение значения.
     *
     * @param string $format
     * @param string|null $locale
     * @return string
     * @throws Exception\Currency
     * @access public
     */
    public function val($format = null, $locale = null) {
        if ($format === null) {
            $format = $this->format;
        }

        if ($format === null) {
            $format = self::FORMAT;
        }

        if (!static::checkFormat($format)) {
            throw new Exception\Currency(Exception\Currency::FORMAT, array($format));
        }

        $locales = self::getLocales($locale);
        return $locales[$this->value][self::$flags[$format]];
    }

    /**
     * Установка и получение пользовательского короткого обозначения валюты.
     *
     * @return mixed
     * @throws Exception\Currency
     * @access public
     */
    public function short() {
        static $short = array();

        // статический вызов
        if (empty($this)) {
            self::setSign(func_get_args(), $short);
            return;
        }

        if (isset($short[$this->value])) {
            return $short[$this->value];
        }

        $locales = self::getLocales('en');
        return $locales[$this->value][self::$flags['s']];
    }

    /**
     * Установка и получение пользовательского среднего обозначения валюты.
     *
     * @return mixed
     * @throws Exception\Currency
     * @access public
     */
    public function medium() {
        static $medium = array();

        // статический вызов
        if (empty($this)) {
            self::setSign(func_get_args(), $medium);
            return;
        }

        if (isset($medium[$this->value])) {
            return $medium[$this->value];
        }

        $locales = self::getLocales('en');
        return $locales[$this->value][self::$flags['c']];
    }

    /**
     * Установка и получение пользовательского полного обозначения валюты.
     *
     * @return mixed
     * @throws Exception\Currency
     * @access public
     */
    public function long() {
        static $long = array();

        // статический вызов
        if (empty($this)) {
            self::setSign(func_get_args(), $long);
            return;
        }

        if (isset($long[$this->value])) {
            return $long[$this->value];
        }

        $locales = self::getLocales('en');
        return $locales[$this->value][self::$flags['C']];
    }

    /**
     * Символ валюты.
     *
     * Unicode 16CC HTML мнемоника.
     *
     * @param string|null $locale
     * @return string
     * @access public
     */
    public function sign($locale = null) {
        $locales = self::getLocales($locale);
        return $locales[$this->value][self::$flags['s']];
    }

    /**
     * Символ валюты с префиксом alpha2.
     *
     * @param string|null $locale
     * @return string
     * @access public
     */
    public function alphaSign($locale = null) {
        $locales = self::getLocales($locale);
        return $locales[$this->value][self::$flags['S']];
    }

    /**
     * Двухбуквенных алфавитный код валюты в соответствии с ISO 3166-1.
     *
     * @param string|null $locale
     * @return string
     * @access public
     */
    public function alpha2($locale = null) {
        $locales = self::getLocales($locale);
        return $locales[$this->value][self::$flags['c']];
    }

    /**
     * Трёхбуквенный алфавитный код валюты в соответствии с ISO 4217.
     *
     * @param string|null $locale
     * @return string
     * @access public
     */
    public function alpha3($locale = null) {
        $locales = self::getLocales($locale);
        return $locales[$this->value][self::$flags['C']];
    }

    /**
     * Трёхзначный цифровой код валюты с ISO 4217.
     *
     * @return int
     * @access public
     */
    public function number() {
        $locales = self::getLocales('en');
        return $locales[$this->value][self::$flags['N']];
    }

    /**
     * Внутренний ключ валюты.
     *
     * @return int
     * @access public
     */
    public function key() {
        $locales = self::getLocales('en');
        return $locales[$this->value][self::$flags['n']];
    }

    /**
     * Проверка формата на наличие запрещенных флагов.
     *
     * @static
     * @param string $format
     * @return bool true если формат допустим
     * @access protected
     */
    protected static function checkFormat($format) {
        return (isset(self::$flags[$format]) ? true : false);
    }

    /**
     * Автоматическое определение формата по значению валюты.
     *
     * @static
     * @param $value
     * @param null|string $format
     * @return null|string
     * @throws Exception\Currency
     * @access protected
     */
    protected static function autoFormat($value, $format = null) {
        if ($format === null) {
            if (is_numeric($value) || preg_match('/^\d+$/', $value)) {
                if ($value > 99) {
                    $format = 'N';

                } else {
                    $format = 'n';
                }

            } elseif (is_string($value)) {
                $len = strlen($value);

                switch ($len) {
                    case 1:
                    case 8:
                        $format = 's';
                        break;

                    case 2:
                        $format = 'c';
                        break;

                    case 3:
                        $format = 'C';
                        break;

                    case 10:
                        $format = 'S';
                        break;
                }
            }
        }

        if (!static::checkFormat($format)) {
            throw new Exception\Currency(Exception\Currency::FORMAT_NOT_FOUND, array(get_called_class()));
        }

        return $format;
    }

    /**
     * Установка знака валюты.
     *
     * @static
     * @param array $data
     * @param $target
     * @throws Exception\Currency
     * @access protected
     */
    protected static function setSign(array $data, &$target) {
        if (count($data) != 2) {
            throw new Exception\Currency(Exception\Currency::ARGUMENTS);
        }

        $alpha3 = $data[0];
        $code = $data[1];

        $key = null;
        self::cast($alpha3, 'C', $key);

        $target[$key] = $code;
    }

    /**
     * Получение массива данных для локали.
     *
     * Английская локаль по умолчанию, если не найдена требуемая.
     *
     * @static
     * @param null|string $locale
     * @return mixed
     * @access protected
     */
    protected static function getLocales($locale = null) {
        $locale = self::locale($locale);
        return (isset(self::$locales[$locale]) ? self::$locales[$locale] : self::$locales['en']);
    }

    public static function setLocales($locales) {
        static::$locales = $locales;
    }

}



/**
 * IP
 *
 * IP адрес. Возможна запись с маской.
 * 127.0.0.1
 * 127.0.0.1/32
 *
 * @package TP
 */
class IP extends Type {

    /**
     * Преобразование входного параметра к объектному типу IP.
     *
     * @static
     * @param string $value значение, преобразуемое к IP
     * @return string
     * @throws Exception\IP
     * @access public
     */
    public static function cast($value) {
        if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(\/\d{1,2})?$/', $value)) {
            throw new Exception\IP(Exception\IP::TYPE, array(
                get_called_class()
            ));
        }

        return $value;
    }
}

/**
 * Source
 *
 * Источник
 *
 * @package TP
 */
class Source extends Set {

    const AUTO = 0;
    const ADMIN = 1;
    const CRM = 2;
    const ALLSPO = 3;
    const TOURDEALER = 4;
    const TOURINDEX_RBKM = 5;
    const PROLONG_PAID_ACCESS = 6;

    protected static $set = array(0, 1, 2, 3, 4, 5, 6, '0', '1', '2', '3', '4', '5', '6');

    protected static $locales = array(
        self::AUTO => 'авт.',
        self::ADMIN => 'admin2.allspo.ru',
        self::CRM => 'CRM',
        self::ALLSPO => 'allspo',
        self::TOURDEALER => 'TourDealer',
        self::TOURINDEX_RBKM => 'tourindex rbkm',
        self::PROLONG_PAID_ACCESS => 'prolongPaidAccess'
    );

    /**
     * Получение длинного названия значения.
     *
     * @return string
     * @access public
     */
    public function long() {
        return static::$locales[$this->value];
    }

    /**
     * Получение списка возможных значений в формате array([n] => [$format], ...).
     *
     * @static
     * @param null $format
     * @param null $locale
     * @return array
     * @access public
     */
    public static function getList($format = null, $locale = null) {
        return static::$locales;
    }
}

/**
 * Mixed
 *
 * Произвольный тип данных.
 *
 * @package TP
 */
class Mixed extends Type {

    /**
     * Преобразование входного параметра к объектному типу Mixed.
     *
     * @static
     * @param mixed $value значение, преобразуемое к Mixed
     * @return mixed
     * @access public
     */
    public static function cast($value) {
        return $value;
    }
}


/**
 * TColor
 *
 * Цвет
 *
 * @package TP
 */

class TColor extends Type {

    protected $red;
    protected $green;
    protected $blue;
    protected $alpha;

    /**
     * TColor constructor.
     * @param null $value
     * @throws \TP\Exception\TColor
     */

    public function __construct($value = null) {

        $color = self::parse($value);

        $this->red = $color[0];
        $this->green = $color[1];
        $this->blue = $color[2];
        $this->alpha = $color[3];
    }

    /**
     * @param $value
     * @return array
     * @throws \TP\Exception\TColor
     */

    static protected function parse($value) {
        $result = [];
        $m = [];

        $value = str_replace(' ', '', $value);
        $value = strtolower($value);

        if ($value[0]==='#') {
            if (!preg_match('~^\#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$~', $value, $m))
                throw new \TP\Exception\TColor(\TP\Exception\TColor::FORMAT, [get_called_class()]);

            $result[0] = min(hexdec($m[1]), 255); // red
            $result[1] = min(hexdec($m[2]), 255); // green
            $result[2] = min(hexdec($m[3]), 255); // blue
            $result[3] = 1; // alpha

        } elseif (strncmp($value, 'rgba', 4)===0) {
            if (!preg_match('~^rgba\((\d+),(\d+),(\d+),(0|1|0\.\d+)\)$~', $value, $m))
                throw new \TP\Exception\TColor(\TP\Exception\TColor::FORMAT, [get_called_class()]);

            $result[0] = min((int)$m[1], 255); // red
            $result[1] = min((int)$m[2], 255); // green
            $result[2] = min((int)$m[3], 255); // blue
            $result[3] = round($m[4], 3); // alpha

        } elseif (strncmp($value, 'rgb', 3)===0) {
            if (!preg_match('~^rgb\((\d+),(\d+),(\d+)\)$~', $value, $m))
                throw new \TP\Exception\TColor(\TP\Exception\TColor::FORMAT, [get_called_class()]);

            $result[0] = min((int)$m[1], 255); // red
            $result[1] = min((int)$m[2], 255); // green
            $result[2] = min((int)$m[3], 255); // blue
            $result[3] = 1; // alpha

        } else {
            throw new \TP\Exception\TColor(\TP\Exception\TColor::FORMAT, [get_called_class()]);
        }

        return $result;
    }


    public function rgb() {
        return "rgba({$this->red},{$this->green},{$this->blue})";
    }

    public function rgba() {
        return "rgba({$this->red},{$this->green},{$this->blue},{$this->alpha})";
    }

    public static function cast($value) {
        $color = self::parse($value);
        return "rgba({$color[0]},{$color[1]},{$color[2]},{$color[3]})";
    }


    public function __toString() {
        return "rgba({$this->red},{$this->green},{$this->blue},{$this->alpha})";
    }

}