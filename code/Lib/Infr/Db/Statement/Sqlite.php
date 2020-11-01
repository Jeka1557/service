<?php


namespace Lib\Infr\Db\Statement;

use \Lib\Infr\Db;
use \Lib\Infr\Db\Statement;
use \Lib\Infr\Db\Adapter;


/**
 * Sqlite
 *
 * @package LSF2\Infr\Db\Statement
 */
final class Sqlite extends Statement {

    static protected $useCP1251 = false;

    /**
     * @var array ������������ ����� ����������
     */
    protected static $fetch = array(
        Db::FETCH_BOTH => SQLITE3_BOTH,
        Db::FETCH_ASSOC => SQLITE3_ASSOC,
        Db::FETCH_NUM => SQLITE3_NUM
    );

    /**
     * @param Adapter $adapter
     * @param resource $result
     * @throws Exception
     * @access public
     */
    public function __construct(Adapter $adapter, $result) {
        if ( !($result instanceof \SQLite3Result)) {
            throw new Exception(Exception::STMT_RESOURCE_TYPE);
        }

        $this->adapter = $adapter;
        $this->result = $result;
        $this->countFields = $result->numColumns();
        $this->affected = $adapter->affectedRows();


        // ������ ���� ���� ������ ���������
        if ($this->countFields == -1) {
            throw new Exception(Exception::STMT_FIELDS_COUNT);
        }
    }

    /**
     * ������������ ���������� �������.
     *
     * @access public
     */
    public function __destruct() {
        $this->result->finalize();
    }

    /**
     * ������� ������� ���������� ������� � ���� �������.
     *
     * @param int $fetch ��� ������������� ����������
     * @param null|int $offset ��������
     * @param array|null $types ���� �����
     * @return array|null
     * @throws Exception
     * @access public
     */
    public function fetchRow($fetch = Db::FETCH_ASSOC, $offset = null, array $types = null) {

        if ($offset) {
            while($offset != $this->offset) {
                $this->result->fetchArray();
                $this->next();
            }
        }

        $data = $this->result->fetchArray(static::_adapterFetch($fetch));
        $this->next();

        if ($data && $types) {
            $data = $this->_unescape($data, $types);
        } elseif (!$data) {
            $data = null;
        }

        if (self::$useCP1251 and $data)
            $this->convert1251($data);

        $this->event()
            ->trigger(Db::EVENT_AFTER_FETCH_ROW, array(&$data, $fetch, $offset));

        return $data;
    }

    /**
     * ������� ������� ���������� ������� � ���� �������.
     *
     * @param null|int $offset ��������
     * @param string|null $className �������� ������, ���� �� �������, �� \stdClass
     * @param array|null $params ���������, ������������ � ����������� ������
     * @param array|null $types ���� �����
     * @return \stdClass|object|null
     * @access public
     */
    public function fetchObject($offset = null, $className = null, array $params = null, array $types = null) {
        throw new Exception('Method '.__METHOD__.' is not defined for SQLite statement');
    }

    /**
     * ������� ������� �������� ������ ���������� �������.
     *
     * @param null|int $type ��� ����������
     * @return array|null
     * @throws Exception
     * @access public
     */
    public function fetchOne($type = null) {
        $this->rewind();

        $data = $this->result->fetchArray(SQLITE3_NUM);

        if ($data && $type) {
            $data = $this->_unescape($data, $type);

        } elseif (!$data) {
            $data = null;
        }

        if (self::$useCP1251 and $data)
            $this->convert1251($data);

        $this->event()
            ->trigger(Db::EVENT_AFTER_FETCH_ONE, array(&$data));

        return $data;
    }

    /**
     * ������� ���� ����� ���������� �������.
     *
     * @param int $fetch ��� ������������� ����������
     * @param array|null $types ���� ��������
     * @return array
     * @throws Exception
     * @access public
     */
    public function fetchAll($fetch = Db::FETCH_ASSOC, array $types = null) {
        $this->rewind();
        $data = array();

        while ($row = $this->result->fetchArray(static::_adapterFetch($fetch))) {
            $data[] = $row;
        }

        if ($data && $types) {
            foreach ($data as &$row) {
                $row = $this->_unescape($row, $types);
            }
            unset($row);

        } elseif (!$data) {
            $data = array();
        }

        if (self::$useCP1251 and $data)
            foreach ($data as &$row) {
                $this->convert1251($row);
            }


        $this->event()
            ->trigger(Db::EVENT_AFTER_FETCH_ALL, array(&$data, $fetch));

        return $data;
    }

    /**
     * ������� ���� ����� ���������� �������.
     *
     * @param string|null $className �������� ������, ���� �� �������, �� \stdClass
     * @param array|null $params ���������, ������������ � ����������� ������
     * @param array|null $types ���� ��������
     * @return array
     * @access public
     */
    public function fetchAllObject($className = null, array $params = null, array $types = null) {
        return (object)$this->fetchAll();
    }

    /**
     * ������� ���� �������� �������.
     *
     * @param int|string $column ������ ��� �������� �������
     * @param null|int $type ��� �������
     * @throws Exception
     * @access public
     */
    public function fetchAllColumns($column, $type = null) {
        throw new Exception('Method '.__METHOD__.' is not defined for SQLite statement');
    }

    /**
     * ������� ���� �������� ������� $value � ������� ���������� ������� $key.
     *
     * @param int|string $key ������ ��� �������� ��������� �������
     * @param int|string $value ������ ��� �������� ������� ��������
     * @param null|int $typeKey ��� ������ ��������� �������
     * @param null|int $typeValue ��� ������ ������� ��������
     * @throws Exception
     * @access public
     */
    public function fetchAllPairs($key, $value, $typeKey = null, $typeValue = null) {
        throw new Exception('Method '.__METHOD__.' is not defined for SQLite statement');
    }

    /**
     * ������� ���� �������� ���������� ������� � ������� ���������� ������� $column.
     *
     * @param int|string $column ������ ��� �������� �������� �������
     * @param int $fetch ��� ������������� ������
     * @param null|int $typeColumn ��� ������ ��������� �������
     * @param array|null $types ���� �������� ������
     * @return array
     * @throws Exception
     * @access public
     */
    public function fetchAllAssoc($column, $fetch = Db::FETCH_ASSOC, $typeColumn = null, array $types = null) {
        throw new Exception('Method '.__METHOD__.' is not defined for SQLite statement');
    }

    /**
     * ������� �������� ���������� �������.
     *
     * @return array
     * @access public
     */
    public function current() {
        $data = $this->result->fetchArray(static::_adapterFetch($this->defaultFetch));
        return $data;
    }

    /**
     * ������� ����.
     *
     * @return int
     * @access public
     */
    public function key() {
        return $this->offset;
    }

    /**
     * ���������� ����� �������.
     *
     * @access public
     */
    public function next() {
        ++$this->offset;
    }

    /**
     * ����� ��������� ������ ����������.
     *
     * @access public
     */
    public function rewind() {
        $this->offset = 0;
        $this->result->reset();
    }

    /**
     * �������� ��������.
     *
     * @return bool
     * @access public
     */
    public function valid() {
        return ($this->offset>0);
    }


    protected function convert1251(&$row) {

        foreach ($row as $name => $value) {
            // ��� �������, ��������� ������� ��������� ��� �� utf � ������ �� ������������ �������������
            if (is_null($value) or $name == 'file_body')
                continue;

            $row[$name] = iconv('UTF-8', 'WINDOWS-1251//TRANSLIT', $value);
        }
    }

    static public function setCP1251() {
        self::$useCP1251 = true;
    }
}