<?php

namespace Lib\Infr\Db\Adapter;
use \Lib\Infr\Db;
use \Lib\Infr\Db\Adapter;
use \Lib\Infr\Db\Select;
use \Lib\Infr\Db\Statement;
use \Lib\Infr\DSN\Pgsql as DSN;

/**
 * Генерация исключения.
 *
 * @param $code
 * @param array $params
 * @throws Exception
 */
function except($code, array $params = array()) {
    throw new Exception($code, $params);
}

/**
 * Pgsql
 *
 * @package LSF2\Infr\Db\Adapter
 */
final class Pgsql extends Adapter {

    /**
     * Символ экранирования части запроса.
     */
    const QUOTE_CHAR = '"';

    /**
     * Для экранирования частей запроса с php 5.4.4 доступна pg_escape_identifier.
     *
     * @var bool
     */
    private static $pgEscapeIdent;

    /**
     * Для экранирования пользовательских данных с php 5.4.4 доступна pg_escape_literal.
     *
     * @var bool
     */
    private static $pgEscapeLiteral;

    /**
     * Конструктор адаптера.
     *
     * @param DSN $dsn
     * @throws Exception
     * @access public
     */
    public function __construct(DSN $dsn) {
        if (!extension_loaded('pgsql')) {
            throw new Exception(Exception::PGSQL_UNDEF);
        }

        // проверка наличия функции экранирования часте запроса
        if (self::$pgEscapeIdent === null) {
            self::$pgEscapeIdent = function_exists('pg_escape_identifier');
        }

        // проверка наличия функции экранирования пользовательских данных
        if (self::$pgEscapeLiteral === null) {
            self::$pgEscapeLiteral = function_exists('pg_escape_literal');
        }

        $host = $dsn->host->val();
        $port = $dsn->port->val();

        $this->_trace("CONNECT $host:$port", array(
            'host' => $host,
            'port' => $port,
            'user' => $dsn->user->val(),
            'dbname' => $dsn->dbname->val()
        ));

        $this->_dbconn = pg_connect(strval($dsn))
            or except(Exception::PGSQL_CONNECT, array($host, $port));

        $this->_trace();

        $this->event()
            ->trigger(Db::EVENT_AFTER_CONNECT, array($this));
    }

    /**
     * Параметризованный запрос.
     *
     * sql приходит с переменными вида :param1
     * массиы параметров вида array(:param1 => значение1, ...)
     * далее выполняется замена :param1 на $1
     * формируется новый массив параметров array(значение1)
     *
     * @param string $sql
     * @param array $params array(:param1 => значение1, ...)
     * @param array $types array(:param1 => тип1, ...)
     * @return Statement
     * @access public
     */
    public function execute($sql, array $params = array(), array $types = array()) {
        $self = $this;
        $pgparams = array();

        $sql = preg_replace_callback(Select::REG_PARAM, function($matches) use ($self, &$params, &$pgparams, $types) {
            if (!isset($matches[1])) {
                return $matches[0];
            }

            /* @var Adapter $self */
            static $i = 0;

            $param = $matches[2];
            if (!array_key_exists($param, $params)) {
                throw new Exception(Exception::PARAM_UNDEF, array($param));
            }

            $type = (empty($types[$param])) ? 0 : $types[$param];
            $pgparams[$i] = &$params[$param];
            $pgparams[$i] = $self->escapeExecute($pgparams[$i], $type);
            $postfix = $self->typeSign($type);

            $i++;
            return $matches[1] . '$' . $i . $postfix;
        }, $sql);

        $sql = $this->_callInfo() . $sql;

        $this->_trace($sql, $pgparams);

        try {
            $result = pg_query_params($this->_dbconn, $sql, $pgparams)
                or except(Exception::PGSQL_QUERY, array(pg_last_error($this->_dbconn)));
        } catch (\Exception $e) {
            $this->_trace();
            throw $e;
        }

        $this->_trace();



        unset($pgparams, $params);
        $stmt = new Statement\Pgsql($this, $result);
        return $stmt;
    }

    /**
     * Выполнение запроса.
     *
     * @param string $sql
     * @return Statement\Pgsql
     * @access public
     */
    public function query($sql) {
        $sql = $this->_callInfo() . $sql;

        $this->_trace($sql);

        try {
            $result = pg_query($this->_dbconn, $sql)
                or except(Exception::PGSQL_QUERY, array(pg_last_error($this->_dbconn)));
        } catch (\Exception $e) {
            $this->_trace();
            throw $e;
        }

        $this->_trace();

        $stmt = new Statement\Pgsql($this, $result);
        return $stmt;
    }

    /**
     * Экранирование и преобразование данных при подстановке в параметризованный запрос.
     *
     * @param mixed $value
     * @param int $type
     * @return mixed
     * @access public
     */
    public function escapeExecute($value, $type) {
        if ($type & Db::PARAM_DATAARR) {
            if (!$value) {
                return null;
            }

            if (!is_array($value)) {
                $value = array($value);
            }

            $type = $type - Db::PARAM_DATAARR;
            foreach ($value as &$p) {
                $p = $this->escapeExecute($p, $type);
            }

            return '{' . implode(', ', $value) . '}';

        } elseif ($type & Db::PARAM_BYTEA) {
            return str_replace(array("\\\\", "''"), array("\\", "'"), pg_escape_bytea($this->_dbconn, $value));
        }

        return parent::escapeExecute($value, $type);
    }

    /**
     * Строковый определитель типа.
     *
     * @static
     * @param int $type
     * @return string
     * @access public
     */
    public static function typeSign($type) {
        $strtype = '';

        if ($type & Db::PARAM_BYTEA) {
            $strtype = '::bytea';

        } elseif ($type & Db::PARAM_SMALLINT) {
            $strtype = '::smallint';

        } elseif ($type & Db::PARAM_INT) {
            $strtype = '::int';

        } elseif ($type & Db::PARAM_BIGINT) {
            $strtype = '::bigint';

        } elseif ($type & Db::PARAM_FLOAT) {
            $strtype = '::real';

        } elseif ($type & Db::PARAM_NUMERIC) {
            $strtype = '::numeric';

        } elseif ($type & Db::PARAM_BOOL) {
            $strtype = '::bool';

        } elseif ($type & Db::PARAM_TIMESTAMP) {
            $strtype = '::timestamp without time zone';

        } elseif ($type & Db::PARAM_DATE) {
            $strtype = '::date';

        } elseif ($type & Db::PARAM_TSVECTOR) {
            $strtype = '::tsvector';

        } elseif ($type & Db::PARAM_TSQUERY) {
            $strtype = '::tsquery';
        }

        if ($strtype) {
            return $strtype . (($type & Db::PARAM_DATAARR) ? '[]' : '');
        }

        return parent::typeSign($type);
    }

    /**
     * Преобразование данных после экранизации.
     *
     * @param mixed $value
     * @param int $type
     * @return bool|float|int|string
     */
    public function unescape($value, $type) {
        if ($type & Db::PARAM_DATAARR) {
            if ($value) {
                $value = explode(',', substr($value, 1, strlen($value) - 2));
                $type = $type - Db::PARAM_DATAARR;
                foreach ($value as &$v) {
                    $v = $this->unescape($v, $type);
                }
            }

            return (($value) ? $value : array());

        } elseif ($type & Db::PARAM_BYTEA) {
            return pg_unescape_bytea($value);
        }

        return parent::unescape($value, $type);
    }

    /**
     * Экранирование спецсимволов в строке запроса.
     *
     * Экранирование по умолчанию. Пререопределяется для различных адаптеров.<br>
     * Передаются только строки и специализированные типы, например PARAM_LOB и PARAM_BYTEA.
     *
     * pg_escape_literal
     * добавлен с php 5.4.4
     * добавляет символ ' в начале и в конце строки
     * !! E'...'
     * !! не экранирует переносы строк
     *
     * pg_escape_string
     * параметр подключения добавлен с php 5.2.0
     * !! НЕ добавляет символ ' в начале и в конце строки
     * !! не экранирует переносы строк
     *
     * pg_escape_bytea
     * параметр подключения добавлен с php 5.2.0
     * в php 5.4.4 возвращает HEX а не BYTEA
     * постфикс ::bytea приводит к типу bytea
     * есди дописать E'...'::bytea, то преобразования к bytea не будет
     *
     * @param string $value
     * @param int $type
     * @return string
     * @access public
     */
    public function escape($value, $type = Db::PARAM_STR) {
        if ($type & Db::PARAM_BYTEA) {
            return "'" . str_replace(array("\\\\", "''"), array("\\", "'"), pg_escape_bytea($this->_dbconn, $value)) . "'::bytea";

        } elseif ($type & Db::PARAM_STR) {
            if (self::$pgEscapeLiteral) {
                return pg_escape_literal($this->_dbconn, $value);
            }

            return "'" . pg_escape_string($this->_dbconn, $value) . "'";
        }

        return parent::escape($value, $type);
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
        if (static::$pgEscapeIdent) {
            return pg_escape_identifier($this->_dbconn, $value);

        } else {
            $q = static::QUOTE_CHAR;
            return ($q . str_replace($q, $q . $q, $value) . $q);
        }
    }

    /**
     * Начало транзакции.
     *
     * @return bool
     * @throws Exception
     * @access public
     */
    public function beginTransaction($name = null) {
        $sql = 'BEGIN';
        $this->_trace($sql);

        try {
            $result = pg_query($this->_dbconn, $sql)
                or except(Exception::PGSQL_QUERY, array(pg_last_error($this->_dbconn)));
        } catch (\Exception $e) {
            $this->_trace();
            throw $e;
        }

        $this->_trace();

        pg_free_result($result);


        self::$inTransaction = true;
        self::$transactionName = $name;

        return true;
    }

    /**
     * Отмена транзакции.
     *
     * @return bool
     * @throws Exception
     * @access public
     */
    public function rollback() {
        $sql = 'ROLLBACK';
        $this->_trace($sql);

        try {
            $result = pg_query($this->_dbconn, $sql)
                or except(Exception::PGSQL_QUERY, array(pg_last_error($this->_dbconn)));
        } catch (\Exception $e) {
            $this->_trace();
            throw $e;
        }

        $this->_trace();

        pg_free_result($result);

        self::$inTransaction = false;
        self::$transactionName = null;

        return true;
    }

    /**
     * Подтверждение трензакции.
     *
     * @return bool
     * @throws Exception
     * @access public
     */
    public function commit() {
        $sql = 'COMMIT';
        $this->_trace($sql);

        try {
            $result = pg_query($this->_dbconn, $sql)
                or except(Exception::PGSQL_QUERY, array(pg_last_error($this->_dbconn)));
        } catch (\Exception $e) {
            $this->_trace();
            throw $e;
        }

        $this->_trace();

        pg_free_result($result);

        self::$inTransaction = false;
        self::$transactionName = null;

        return true;
    }
}