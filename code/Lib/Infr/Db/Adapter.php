<?php
/**
 * Date 15.08.12 16:01
 * @author anton
 * @package LSF2\Infr\Db
 * @copyright Lightsoft 2012
 */

namespace Lib\Infr\Db;
use \Lib\Infr\Db as Db;
use \Lib\Infr\DSN as DSN;
use \Lib\Infr\Db\Exception as Except;

/**
 * Adapter
 *
 * @package LSF2\Infr\Db
 */
abstract class Adapter {

    /**
     * Символ экранирования части запроса.
     */
    const QUOTE_CHAR = '"';

    /**
     * @var resource ресурс соединенния с БД
     */
    protected $_dbconn;

    /*
     * Открыта транзакция
     */
    protected static $inTransaction = false;

    /*
     * Имя текущей транзакции
     */
    protected static $transactionName;


    /**
     * Параметризованный запрос.
     *
     * @abstract
     * @param string $sql
     * @param array $params array(:param1 => значение1, ...)
     * @param array $types array(:param1 => тип1, ...)
     * @return Statement
     * @access public
     */
    abstract public function execute($sql, array $params = array(), array $types = array());

    /**
     * Выполнение запроса.
     *
     * @abstract
     * @param string $sql
     * @return Statement
     * @access public
     */
    abstract public function query($sql);

    /**
     * Начало транзакции.
     *
     * @abstract
     * @return bool
     * @throws Except
     * @access public
     */
    abstract public function beginTransaction($name = null);

    /**
     * Отмена транзакции.
     *
     * @abstract
     * @return bool
     * @throws Except
     * @access public
     */
    abstract public function rollback();

    /**
     * Подтверждение трензакции.
     *
     * @abstract
     * @return bool
     * @throws Except
     * @access public
     */
    abstract public function commit();



    public function inTransaction() {
        return static::$inTransaction;
    }


    public function getTransactionName() {
        return static::$transactionName;
    }


    /**
     * Конструктор адапретов.
     *
     * @final
     * @static
     * @param string $driver алиас адаптера
     * @param DSN $dsn объект параметров подключения
     * @return Adapter
     */
    final public static function create($driver, DSN $dsn) {
        $class = __CLASS__ . '\\' . ucfirst(strtolower($driver));
        return new $class($dsn);
    }

    /**
     * Конструктор адаптеров-одиночек.
     *
     * @final
     * @static
     * @param string $driver алиас адаптера
     * @param DSN $dsn объект параметров подключения
     * @return Adapter
     */
    final public static function getInstance($driver, DSN $dsn) {
        static $cache = array();
        $key = $driver . ' / ' . strval($dsn);

        if (empty($cache[$key])) {
            $cache[$key] = self::create($driver, $dsn);
        }

        return $cache[$key];
    }

    /**
     * Преобразование данных после экранирования.
     *
     * @param mixed $value
     * @param int $type
     * @return bool|float|int|string
     */
    public function unescape($value, $type) {
        if ($type & Db::PARAM_INT) {
            return (int) $value;

        } elseif ($type & Db::PARAM_SMALLINT) {
            return (int) $value;

        } elseif ($type & Db::PARAM_BIGINT) {
            return floor((float) $value);

        } elseif ($type & Db::PARAM_FLOAT) {
            return (float) $value;

        } elseif ($type & Db::PARAM_NUMERIC) {
            return (float) $value;

        } elseif ($type & Db::PARAM_BOOL) {
            return (bool) $value;

        } elseif ($type & Db::PARAM_STR) {
            return (string) $value;
        }

        return $value;
    }

    /**
     * Экранирование спецсимволов в строке запроса.
     *
     * Экранирование по умолчанию. Пререопределяется для различных адаптеров.<br>
     * Передаются только строки и специализированные типы, например PARAM_LOB и PARAM_BYTEA.
     *
     * @param string $value
     * @param int $type
     * @return string
     * @access public
     */
    public function escape($value, $type = Db::PARAM_STR) {
        if ($type & Db::PARAM_NULL) {
            return 'NULL';

        } elseif ($type & Db::PARAM_FLOAT) {
            return sprintf('%.0f', (float) $value);

        } elseif ($type & Db::PARAM_NUMERIC) {
            return sprintf('%.0f', (float) $value);

        } elseif ($type & Db::PARAM_BIGINT) {
            return sprintf('%.0f', floor((float) $value));

        } elseif ($type & Db::PARAM_SMALLINT) {
            return var_export((int) $value, true);

        } elseif ($type & Db::PARAM_INT) {
            return var_export((int) $value, true);

        } elseif ($type & Db::PARAM_BOOL) {
            return var_export((bool) $value, true);

        } elseif ($type & Db::PARAM_STR) {
            // в таком виде аналог pg_escape_string
            return "'" . str_replace("'", "''", addcslashes($value, "\000\032")) . "'"; //\n\r\\'\"
        }

        return var_export($value, true);
    }

    /**
     * Экранирование частей запроса.
     *
     * Экранирование по умолчанию. Пререопределяется для различных адаптеров.<br>
     * Передается только строка.
     *
     * @param string $value
     * @return string
     * @access public
     */
    public function escapeIdentifier($value) {
        $q = static::QUOTE_CHAR;
        return ($q . str_replace($q, $q . $q, $value) . $q);
    }

    /**
     * Экранирование и преобразование данных при подстановке в параметризованный запрос.
     *
     * Переопределять в потомках.
     *
     * @param mixed $value
     * @param int $type
     * @return mixed
     * @access public
     */
    public function escapeExecute($value, $type) {
        if ($type & (Db::PARAM_SMALLINT + Db::PARAM_INT)) {
            return (int) $value;

        } elseif ($type & Db::PARAM_BIGINT) {
            return sprintf('%.0f', floor((float) $value));

        } elseif ($type & Db::PARAM_FLOAT) {
            return sprintf('%.0f', (float) $value);

        } elseif ($type & Db::PARAM_NUMERIC) {
            return sprintf('%.0f', (float) $value);

        } elseif ($type & Db::PARAM_BOOL) {
            return (int) (bool) $value;

        } elseif ($type & Db::PARAM_NULL) {
            return null;
        }

        return (string) $value;
    }

    /**
     * Строковый определитель типа.
     *
     * Переопределять в потомках.
     * Для postgresql например ::bytea и т.д.
     *
     * @static
     * @param int $type
     * @return string
     * @access public
     */
    public static function typeSign($type) {
        return '';
    }

    /**
     * Обработчик событий.
     *
     * @final
     * @return Event
     * @access public
     */
    final public function event() {
        static $event;
        if ($event === null) {
            $event = new Event();
        }

        return $event;
    }

    /**
     * Формирование комментария к SQL запросу с указанием места вызова.
     *
     * @final
     * @return string
     * @access protected
     */
    final protected function _callInfo() {
        static $php54, $php536, $server;
        if ($php54 === null) {
            $php54 = version_compare(PHP_VERSION, '5.4', '>=');
            $php536 = version_compare(PHP_VERSION, '5.3.6', '>=');
            $server = ''; //\LSF2_Infr_Utility_NET::serverIp();
        }

        if ($php536) {
            
            $arg = DEBUG_BACKTRACE_IGNORE_ARGS;

        } else {

            $arg = false;
        }

        if ($php54) {

            $call = debug_backtrace($arg, 3);

        } else {

            $call = debug_backtrace($arg);
        }

        $info = '';
        if (isset($call[2])) {
            /**
             * @todo Здесь нужно учесть что backtrace не всегда содержит file и line
             */
            if (isset($call[2]['file']))
                $info = "/* {$server}: {$call[2]['file']}: {$call[2]['line']} */\n";
        }

        return $info;
    }

    /**
     * Трассировщик.
     *
     * @final
     * @param null|string $sql
     * @param null|array $pgparams
     * @access protected
     */
    final protected function _trace($sql = null, $pgparams = null) {
        /*
        static $trace, $call = 0, $context = array();

        if ($trace === null) {
            $trace = \LSF2_Infr_Tracer::create('LSF2\Db');
            if(!$trace->isWrite('LSF2\Db')) {
                return;
            }
        }

        $call++;

        if ($call == 1) {
            $context = array(
                'begin' => microtime(true),
                'end' => null,
                'sql' => $sql,
                'pgparams' => $pgparams
            );

        } else {
            $call = 0;
            $context['end'] = microtime(true);

            $end = 0;
            $begin = 0;
            extract($context);

            $time = sprintf('%.5f', $end - $begin);
            $errmark = '';
            $tag = array('query');
            if ($time > 0.05) {
                $errmark = 'color: #ff0000 !important;';
                $tag[] = 'slow';
            }

            if ($pgparams)
                $sql = preg_replace_callback('~\$(\d+)~',function ($matches) use ($pgparams) {
                        if (is_null($pgparams[(int)$matches[1]-1]))
                            return 'NULL';
                        else
                            return "'".$pgparams[(int)$matches[1]-1]."'";
                }, $sql);

            $sql = <<<EOT
<div>
    <b>SQL:</b>
    <a href="#" style="text-decoration:none;" onclick="event.stopPropagation(); event.preventDefault(); event.currentTarget.nextSibling.nextSibling.nextSibling.nextSibling.style.display=(event.currentTarget.nextSibling.nextSibling.nextSibling.nextSibling.style.display=='none')?'block':'none'; return false;">+</a>
    <a href="#" style="float: right;" onclick="event.stopPropagation(); event.preventDefault(); if (document.selection) { document.selection.empty(); } else if (window.getSelection) { window.getSelection().removeAllRanges(); } if (document.selection) { var r = document.body.createTextRange(); r.moveToElementText(event.currentTarget.nextSibling.nextSibling); r.select(); } else if (window.getSelection) { var r = document.createRange(); r.selectNode(event.currentTarget.nextSibling.nextSibling); window.getSelection().addRange(r); } return false;">select all</a>
    <pre style="display:block;"><code class="sql" style="max-width:600px;max-height:200px;overflow:auto;">$sql</code></pre>
</div>
EOT;

            if ($pgparams) {
                $pgparams = var_export($pgparams, true);
                $pgparams = <<<EOT
<div>
    <b>Данные:</b> <a href="#" style="text-decoration:none;" onclick="event.stopPropagation(); event.preventDefault(); event.currentTarget.nextSibling.nextSibling.style.display=(event.currentTarget.nextSibling.nextSibling.style.display=='none')?'block':'none'; return false;">+</a>
    <pre style="display:none;"><code class="php" style="max-width:600px;max-height:200px;overflow:auto;">$pgparams</code></pre>
</div>
EOT;
            } else {
                $pgparams = '';
            }

            $message = <<<EOT
<div style="font-size:11px; $errmark">
    <div><b>Время выполнения:</b> $time</div>
    $sql
    $pgparams
</div>
EOT;

            $trace->mark($message, array('type' => 'html', 'tag' => implode('.', $tag)));
            $context = array();
        }
        */
    }
}