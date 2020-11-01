<?php


final class RskAutoload {

    /**
     * @var array|null массив директорий библиотек
     */
    private static $includes;

    /**
     * @var array массив соответствий названий классов и их расположения
     */
    private static $classes = array();

    /**
     * @var array массив соответствий названий неймспейсов и их расположения
     */
    private static $namespaces = array();

    /**
     * @var array
     */
    private static $prefix = array();

    /**
     * @var bool Поле хранит настрйоку, отвечающую за генерацию исключения при невозможности загрузить указанный класс
     */
    private static $_throwAutoloadExceptions = false;

    public static function throwAutoloadExceptions() {
        self::$_throwAutoloadExceptions = true;
    }

    /**
     * Автозагрузчик.
     *
     * @static
     * @param string $className
     * @return bool
     * @access public
     */
    public static function autoload($className) {
        if (isset(self::$classes[$className])) {
            require(self::$classes[$className]);
            return true;
        }

        $sep = DIRECTORY_SEPARATOR;
        $class = $className;
        $includes = array();
        $readable = false;
        $nsresult = false;
        $ns = false;
        $lib = false;

        $nslib = substr($className, 0, strpos($className, '\\'));               // LT\Str\WordEn    => LT

        if ($nslib) {
            $pos = strrpos($className, '\\');
            $nsresult = $ns = substr($className, 0, $pos);                      // LT\Str\WordEn    => LT\Str
            $class = substr($className, $pos + 1);                              // LT\Str\WordEn    => WordEn

            // удвоение NS только для LT
            if ($nslib == 'LT' && $nslib == $ns) {
                $nsresult = $nslib . $sep . $ns;                                // LT/Int           => LT/LT
            }

        } else {
            $pos = strpos($className, '_');
            $lib = substr($className, 0, $pos);                                 // LSF2_Object      => LSF2
        }

        if ($ns && isset(self::$namespaces[$ns])) {
            require(self::$namespaces[$ns]);
            return true;

        } elseif ($lib && isset(self::$prefix[$lib])) {
            $prefix = is_array(self::$prefix[$lib]) ? self::$prefix[$lib] : array(self::$prefix[$lib]);
            $includes = array_merge($includes, $prefix);                        // array('/usr/local/lib/php/LSF2' ...)
            $class = substr($class, $pos + 1);                                  // LSF2_Object      => Object
        }

        if ($lib == 'cls') {
            $classDir = "{$sep}LS{$sep}{$class}";
            $readable = true;

        } else {
            $classDir = $sep . str_replace(array('\\', '_'), $sep, ($nsresult) ? $nsresult . $sep . $class : $class);
        }

        if (!$includes) {
            $includes = self::_getIncludePath();
            $readable = true;
        }

        $res = self::_require($includes, $classDir, $className, $readable);
        if(self::$_throwAutoloadExceptions && !$res) {
            throw new \Exception("Can't load class " . $className);
        }
        return true;
    }

    /**
     * Добавление директорий библиотеки.
     *
     * @static
     * @param array $dirs
     * @param bool $first
     * @access public
     */
    public static function registerDirs(array $dirs, $first = false) {
        $includes = explode(PATH_SEPARATOR, get_include_path());

        $dirs = array_diff($dirs, $includes);
        if ($dirs) {
            if ($first) {
                $includes = array_merge($dirs, $includes);

            } else {
                $includes = array_merge($includes, $dirs);
            }

            set_include_path(implode(PATH_SEPARATOR, $includes));
            self::$includes = null;
        }
    }

    /**
     * Восстановление по умолчанию списка директорий библиотек.
     *
     * @static
     * @access public
     */
    public static function restoreDirs() {
        restore_include_path();
        self::$includes = null;
    }

    /**
     * Регистрация неймспейса и его расположения.
     *
     * Добавление только файлов.
     *
     * @static
     * @param array $namespaces
     * @access public
     */
    public static function registerNamespaces(array $namespaces) {
        foreach ($namespaces as $name => $location) {
            self::$namespaces[$name] = $location;
        }
    }

    /**
     * Регистрация префикса.
     *
     * Добавление только директории.
     *
     * @static
     * @param array $prefix
     * @access public
     */
    public static function registerPrefix(array $prefix) {
        foreach ($prefix as $name => $location) {
            self::$prefix[$name] = $location;
        }
    }

    /**
     * Регистрация класса и его расположения.
     *
     * Добавление только файлов.
     *
     * @static
     * @param array $classes
     * @access public
     */
    public static function registerClasses(array $classes) {
        foreach ($classes as $name => $location) {
            self::$classes[$name] = $location;
        }
    }


    /**
     * Получение массива директорий библиотек.
     *
     * @static
     * @return array
     * @access private
     */
    private static function _getIncludePath() {
        if (self::$includes === null) {
            self::$includes = explode(PATH_SEPARATOR, get_include_path());
            $key = array_search('.', self::$includes);
            if ($key !== false) {
                unset(self::$includes[$key]);
            }
        }

        return self::$includes;
    }

    /**
     * Метод ищет файл по указанным директориям. Если файл найден, то проверяется наличие класса в этом файле.
     * @param $includes - Список директорий в которых искать класс
     * @param $classDir - Путь до файла класса
     * @param $className - Имя загружаемого класса
     * @param $readable
     * @return bool true - если класс успешно загружен. false в других случаях.
     */
    private static function _require($includes, $classDir, $className, $readable) {
        if (!$classDir || $classDir == '.' || $classDir == '/') {
            return false;
        }

        foreach ($includes as $path) {
            $file = $path . $classDir . '.php';

            if ($readable && !is_readable($file)) {
                continue;
            }

            require_once($file);

            if (class_exists($className, false) || interface_exists($className, false)) {
                return true;
            }
        }

        return self::_require($includes, dirname($classDir), $className, $readable);
    }
}


spl_autoload_register(array('RskAutoload', 'autoload'), true, true);
