<?php

namespace Lib\Infr\Db\Adapter;

use \Lib\Infr\Db;
use \Lib\Infr\Db\Adapter;
use \Lib\Infr\Db\Select;
use \Lib\Infr\Db\Statement;
use \Lib\Infr\DSN\Sqlite as DSN;


/**
 * Генерация исключения.
 *
 * @param $code
 * @param array $params
 * @throws Exception
 */
function sqliteExcept($code, array $params = array()) {
    throw new Exception($code, $params);
}

/**
 * Sqlite
 *
 * @package LSF2\Infr\Db\Adapter
 */
final class Sqlite extends Adapter {

    /**
     * Символ экранирования части запроса.
     */
    const QUOTE_CHAR = '"';

    /**
     * Конструктор адаптера.
     *
     * @param DSN $dsn
     * @throws Exception
     * @access public
     */
    public function __construct(DSN $dsn) {
        if (!extension_loaded('sqlite3')) {
            throw new Exception(Exception::PGSQL_UNDEF);
        }
        
        $filePath = $dsn->path->val() . $dsn->file->val();

        if ($dsn->isNull('readOnly') || $dsn->readOnly->val()) {
            $mode = SQLITE3_OPEN_READONLY;
        } else {
            $mode = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE;
        }

        $this->_trace("CONNECT ", array(
            'adapter' => $dsn->__toString()
        ));

        $this->_dbconn = new \SQLite3($filePath, $mode) or sqliteExcept(Exception::PGSQL_CONNECT, array($filePath));
        $this->_dbconn->busyTimeout(5);

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
        $sqliteParams = array();

        $sql = preg_replace_callback(Select::REG_PARAM, function($matches) use ($self, &$params, &$sqliteParams, $types) {
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
            $sqliteParams[$i] = &$params[$param];
            $sqliteParams[$i] = $self->escapeExecute($sqliteParams[$i], $type);
            $postfix = $self->typeSign($type);
            
//            $i++;
            if ($postfix) {
                return $matches[1] . 'CAST(:' . $i++ . ' AS '. $postfix .')';
            } else {
                return $matches[1] . ':' . $i++ . $postfix;
            }
        }, $sql);

        // file_put_contents(SERVICE_ROOT.'/sql.log', $sql."\n\n", FILE_APPEND);

        $sql = $this->_callInfo() . $sql;
        

        $this->trace($sql, $sqliteParams);

        try {
            $stmt = $this->_dbconn->prepare($sql)
            or sqliteExcept(Exception::PGSQL_QUERY, array($this->_dbconn->lastErrorMsg()));

            foreach ($sqliteParams as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $result = $stmt->execute()
            or sqliteExcept(Exception::PGSQL_QUERY, array($this->_dbconn->lastErrorMsg()));;
        } catch (\Exception $e) {
            $this->_trace();
            throw $e;
        }

        $this->_trace();
        
        unset($sqliteParams, $params);
        $stmt = new Statement\Sqlite($this, $result);
        return $stmt;
    }

    /**
     * Выполнение запроса.
     *
     * @param string $sql
     * @return Statement\Sqlite
     * @access public
     */
    public function query($sql) {
        $sql = $this->_callInfo() . $sql;

        $this->_trace($sql);

        try {
            $result = $this->_dbconn->query($sql)
            or sqliteExcept(Exception::PGSQL_QUERY, array($this->_dbconn->lastErrorMsg()));
        } catch (\Exception $e) {
            $this->_trace();
            throw $e;
        }

        $this->_trace();

        $stmt = new Statement\Sqlite($this, $result);
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
            return str_replace(array("\\\\", "''"), array("\\", "'"), $this->_dbconn->escapeString($value));
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
        switch (true) {
            case ($type & Db::PARAM_BYTEA):
                $strtype = 'BLOB';
                break;
            case ($type & Db::PARAM_INT):
            case ($type & Db::PARAM_SMALLINT):
            case ($type & Db::PARAM_BIGINT):
            case ($type & Db::PARAM_BOOL):
                $strtype = 'INTEGER';
                break;
            case ($type & Db::PARAM_NUMERIC):
            case ($type & Db::PARAM_FLOAT):
                $strtype = 'NUMERIC';
                break;
            case ($type & Db::PARAM_DATE):
            case ($type & Db::PARAM_TIMESTAMP):
                $strtype = 'TEXT';
                break;

        }
        if ($strtype) {
            return $strtype;
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
        }

        return parent::unescape($value, $type);
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
        if ($type & Db::PARAM_BYTEA) {
            return "'" . str_replace(array("\\\\", "''"), array("\\", "'"), $this->_dbconn->escapeString($value)) . "'";

        } elseif ($type & Db::PARAM_STR) {
            return '\'' . $this->_dbconn->escapeString($value) . '\'';
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
        return $this->_dbconn->escapeString($value);
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
            $result = $this->_dbconn->exec($sql)
            or sqliteExcept(Exception::PGSQL_QUERY, array($this->_dbconn->lastErrorMsg()));
        } catch (\Exception $e) {
            $this->_trace();
            throw $e;
        }

        $this->_trace();

        unset($result);

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
            $result = $this->_dbconn->exec($sql)
            or sqliteExcept(Exception::PGSQL_QUERY, array($this->_dbconn->lastErrorMsg()));
        } catch (\Exception $e) {
            $this->_trace();
            throw $e;
        }

        $this->_trace();

        unset($result);

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
            $result = $this->_dbconn->exec($sql)
            or sqliteExcept(Exception::PGSQL_QUERY, array($this->_dbconn->lastErrorMsg()));
        } catch (\Exception $e) {
            $this->_trace();
            throw $e;
        }

        $this->_trace();

        unset($result);

        return true;
    }

    /**
     * Количество затронутых строк
     *
     * @return int
     */
    public function affectedRows() {
        return $this->_dbconn->changes();
    }

    /**
     * Закрытие соединения с БД
     *
     * @return bool
     */
    public function close() {
        return $this->_dbconn->close();
    }

    protected function trace($sql, $sqliteParams) {
        if ($sqliteParams)
            $sql = preg_replace_callback('~\:(\d+)~',function ($matches) use (&$sqliteParams) {
                $idx = (int)$matches[1];
                $value = $sqliteParams[$idx];
                unset($sqliteParams[$idx]);
                return "'".$value."'";
            }, $sql);
        $this->_trace($sql, $sqliteParams);
    }
}