<?php

namespace Lib\Infr\Db;
use \Lib\Infr\Db as Db;
use \Lib\Infr\Db\Exception as Except;



class Select {

    const WITH = 'with';
    const DISTINCT = 'distinct';
    const COLUMNS = 'columns';
    const FROM = 'from';
    const JOIN = 'join';
    const WHERE = 'where';
    const GROUP = 'group';
    const HAVING = 'having';
    const WINDOW = 'window';
    const SELECT = 'select';
    const ORDER = 'order';
    const LIMIT = 'limit';
    const OFFSET = 'offset';
    const FETCH = 'fetch';

    /**
     * Сортировка по возрастанию.
     */
    const ORDER_ASC = 1;

    /**
     * Сортировка по убыванию.
     */
    const ORDER_DESC = 2;

    /**
     * NULL значения вначале списка.
     */
    const ORDER_NFIRST = 4;

    /**
     * NULL значения в конце списка.
     */
    const ORDER_NLAST = 8;

    const FROM_UNION = 'UNION';
    const FROM_INTERSECT = 'INTERSECT';
    const FROM_EXCEPT = 'EXCEPT';
    const FROM_UNION_ALL = 'UNION ALL';
    const FROM_INTERSECT_ALL = 'INTERSECT ALL';
    const FROM_EXCEPT_ALL = 'EXCEPT ALL';

    const INNER_JOIN = 'JOIN';
    const LEFT_JOIN = 'LEFT JOIN';
    const RIGHT_JOIN = 'RIGHT JOIN';
    const FULL_JOIN = 'FULL JOIN';
    const CROSS_JOIN = 'CROSS JOIN';

    const REG_PARAM = '/(?:\'.*\')|(?:\".*\")|([^\:])(\:[a-z0-9_]+)/i'; ///([^\:])(\:[a-z0-9_]+)/i

    /**
     * @var array части запроса
     */
    private $_parts = array(
        self::WITH => null,        // array('recursive' => recursive, 'queryName' => queryName, 'column' => column, 'select' => select)
        self::DISTINCT => null,    // array(expr, ...)
        self::COLUMNS => null,     // array(array('expr' => expr, 'alias' => alias), ...)
        self::FROM => null,        // array(array('expr' => expr, 'alias' => alias), ...)
        self::JOIN => null,        // array(array('expr' => expr, 'alias' => alias, 'cond' => condition 'flags' => flags), ...)
        self::WHERE => null,       // array(array('cond' => condition, 'params' => params, 'type' => type, 'or' => true|false), ...)
        self::GROUP => null,       // array(expr, ...)
        self::HAVING => null,      // array(array('cond' => condition, 'params' => params, 'type' => type, 'or' => true|false), ...)
        //self::WINDOW => null,
        self::SELECT => null,      // array(array('expr' => Select, 'flags' => flags), ...)
        self::ORDER => null,       // array(array('expr' => expr, 'flags' => flags), ...)
        self::LIMIT => null,       // int
        self::OFFSET => null,      // int
        self::FETCH => null        // int|bool
    );

    /**
     * @var array параметры запроса
     */
    protected $_binds = array();    // array(:name1 => value1, ...)

    /**
     * @var array типы параметров запроса
     */
    private $_types = array();    // array(:name1 => type1, ...)

    /**
     * @var Adapter
     */
    private $_adapter;

    static $_event;

    /**
     * Конструктор запроса.
     *
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter) {
        $this->_adapter = $adapter;
    }

    /**
     * Адаптер.
     *
     * @final
     * @return Adapter
     * @access public
     */
    final public function adapter() {
        return $this->_adapter;
    }

    /**
     * Установка адаптера.
     *
     * @final
     * @param Adapter $adapter
     * @return Select
     * @access public
     */
    final public function setAdapter(Adapter $adapter) {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * Обработчик событий.
     *
     * @final
     * @return Event
     * @access public
     */
    final public function event() {
        if (self::$_event === null) {
            self::$_event = new Event();
        }

        return self::$_event;
    }

    /**
     * Возвращает представление определенной части запроса.
     *
     * Возвращаемое значение является внутренним представлением в объекте, обычно это массив,<br>
     * содержащий значения и выражения.<br>
     * Каждая часть запроса имеет различную структуру.
     *
     * @final
     * @param string $part часть запроса
     * @return null|array
     * @throws Except
     * @access public
     */
    final public function getPart($part) {
        if (!array_key_exists($part, $this->_parts)) {
            throw new Except(Except::PART_UNDEF);
        }

        return $this->_parts[$part];
    }

    /**
     * Проверка наличия части запроса.
     *
     * @final
     * @param string $part часть запроса
     * @return bool
     */
    final public function hasPart($part) {
        return isset($this->_parts[$part]);
    }

    /**
     * Очистка части SQL-запроса или всех частей, если аргумент не указан.
     *
     * @param string|null $part часть запроса
     * @return Select
     * @throws Except
     * @access public
     */
    final public function reset($part = null) {
        if ($part) {
            if (!array_key_exists($part, $this->_parts)) {
                throw new Except(Except::PART_UNDEF);
            }

            $this->_parts[$part] = null;

        } else {
            foreach ($this->_parts as $k => $v) {
                $this->_parts[$k] = null;
            }
        }

        return $this;
    }

    /**
     * Добавление части WITH запроса.
     *
     * @final
     * @param string $queryName
     * @param Select $select
     * @param array|null $column
     * @param bool $recursive
     * @return Select
     * @access public
     */
    /*final public function with($queryName, Select $select, array $column = null, $recursive = false) {
        $this->_parts[self::WITH] = array(
            'recursive' => $recursive,
            'queryName' => $queryName,
            'column' => $column,
            'select' => $select
        );

        return $this;
    }*/

    /**
     * Выборка уникальных записей.
     *
     * Варианты использования:
     * <ul>
     *     <li>array() => DISTINCT</li>
     *     <li>array(field) => DISTINCT ON (field)</li>
     *     <li>field => DISTINCT ON (field)</li>
     * </ul>
     *
     * @final
     * @return Select
     * @access public
     */
    final public function distinct() {
        if ($this->_parts[self::DISTINCT] === null) {
            $this->_parts[self::DISTINCT] = array();
        }

        $dist = &$this->_parts[self::DISTINCT];
        $args = func_get_args();

        array_walk_recursive($args, function($item) use (&$dist) {
            $dist[] =  $item;
        });

        return $this;
    }

    /**
     * Поля.
     *
     * Варианты использования:
     * <ul>
     *     <li>array('alias' => 'field')</li>
     *     <li>array('field')</li>
     *     <li>array('alias' => Select::expr('COUNT(*)'))</li>
     *     <li>array(Select::expr('COUNT(*)'))</li>
     *     <li>array(function() { return 'COUNT(*)'; })</li>
     *     <li>array('alias' => Select)</li>
     * </ul>
     *
     * @final
     * @return Select
     * @access public
     */
    final public function columns() {
        if ($this->_parts[self::COLUMNS] === null) {
            $this->_parts[self::COLUMNS] = array();
        }

        $columns = &$this->_parts[self::COLUMNS];
        $args = func_get_args();

        array_walk_recursive($args, function($item, $key) use (&$columns) {
            $columns[] = array(
                'expr' => $item,
                'alias' => (is_int($key) ? null : $key)
            );
        });

        return $this;
    }

    /**
     * Условия AND.
     *
     * Варианты использования:
     * <ul>
     *     <li>where('id = ?', 'test') => WHERE (id = 'test')</li>
     *     <li>where('id = ? OR id = ?', array(1, 2), Db::PARAM_INT) => WHERE (id = 1 OR id = 2)</li>
     *     <li>where('id = $1', 1) => WHERE (id = '1')</li>
     *     <li>where('id IN $1', array(array(1,2,3)), Db::PARAM_INT) => WHERE (id IN (1, 2, 3))</li>
     *     <li>where('id IN (?, ?, ?)', array(1, 2, 3), Db::PARAM_INT) => WHERE (id IN (1, 2, 3))</li>
     *     <li>where('id = :id', array(':id' => 1)) => WHERE (id = '1')</li>
     * </ul>
     *
     * @final
     * @param mixed $condition условие или массив условий
     * @param null|mixed $params значения параметров
     * @param int|array $type тип параметров
     * @return Select
     * @access public
     */
    final public function where($condition, $params = null, $type = Db::PARAM_STR) {
        return $this->_where($condition, $params, false, $type);
    }

    /**
     * Условия OR.
     *
     * Модификации вызова аналогичные where().
     *
     * @final
     * @param mixed $condition условие или массив условий
     * @param null|mixed $params значения параметров
     * @param int|array $type тип параметров
     * @return Select
     * @access public
     */
    final public function orWhere($condition, $params = null, $type = Db::PARAM_STR) {
        return $this->_where($condition, $params, true, $type);
    }

    /**
     * Группировка.
     *
     * Варианты использования:
     * <ul>
     *     <li>array('id') = GROUP BY id</li>
     *     <li>array(Select::expr('COUNT(*)')) = GROUP BY COUNT(*)</li>
     *     <li>array(function() { return 'COUNT(*)'; }) = GROUP BY COUNT(*)</li>
     * </ul>
     *
     * @final
     * @return Select
     * @throws Except
     * @access public
     */
    final public function group() {
        if ($this->_parts[self::GROUP] === null) {
            $this->_parts[self::GROUP] = array();
        }

        $group = &$this->_parts[self::GROUP];
        $args = func_get_args();

        array_walk_recursive($args, function($item) use (&$group) {
            $group[] = $item;
        });

        if (!$group) {
            throw new Except(Except::GROUP_UNDEF);
        }

        return $this;
    }

    /**
     * Ограничения AND.
     *
     * @final
     * @param mixed $condition условие или массив условий
     * @param null|mixed $params значения параметров
     * @param int|array $type тип параметров
     * @return Select
     * @access public
     */
    final public function having($condition, $params = null, $type = Db::PARAM_STR) {
        return $this->_having($condition, $params, false, $type);
    }

    /**
     * Ограничения OR.
     *
     * @final
     * @param mixed $condition условие или массив условий
     * @param null|mixed $params значения параметров
     * @param int|array $type тип параметров
     * @return Select
     * @access public
     */
    final public function orHaving($condition, $params = null, $type = Db::PARAM_STR) {
        return $this->_having($condition, $params, true, $type);
    }

    /**
     * Сортировка.
     *
     * Экранируется, учитываются флаги
     * <ul>
     *     <li>array('id') = id ASC NULLS FIRST</li>
     *     <li>array('id' => Select::ORDER_DESC | Select::ORDER_NLAST) = id DESC NULLS LAST</li>
     * </ul>
     *
     * Не экранируется, флаги не учитфывются
     * <ul>
     *     <li>array(Select::expr('COUNT(*) DESC NULLS LAST'))</li>
     *     <li>array(function() { return 'COUNT(*) DESC NULLS LAST'; })</li>
     * </ul>
     *
     * @final
     * @return Select
     * @throw \LSF2\Infr\Db\Exception
     * @access public
     */
    final public function order() {
        if ($this->_parts[self::ORDER] === null) {
            $this->_parts[self::ORDER] = array();
        }

        $order = &$this->_parts[self::ORDER];
        $args = func_get_args();

        array_walk_recursive($args, function($flags, $field) use (&$order) {
            if (is_string($field)) {
                // отсечение значения по умолчанию
                $flags = $flags & (Select::ORDER_DESC | Select::ORDER_NLAST);

            } else {
                $field = $flags;
                // значение по умолчанию
                $flags = Select::ORDER_ASC | Select::ORDER_NFIRST;
            }


            $order[] = array(
                'expr' => $field,
                'flags' => $flags
            );
        });

        if (!$order) {
            throw new Except(Except::ORDER_UNDEF);
        }

        return $this;
    }

    /**
     * Предел.
     *
     * @final
     * @param int $count количество записей
     * @param int|null $offset смещение
     * @return Select
     * @access public
     */
    final public function limit($count, $offset = null) {
        $this->_parts[self::LIMIT] = (int) $count;
        if (is_int($offset)) {
            $this->offset($offset);
        }

        return $this;
    }

    /**
     * Предел по страницам.
     *
     * @final
     * @param int $page номер страницы
     * @param int $count количество записей на странице
     * @return Select
     * @access public
     */
    final public function limitPage($page, $count) {
        return $this->limit($count, $page * $count);
    }

    /**
     * Смещение.
     *
     * @final
     * @param int $start смещение
     * @return Select
     * @access public
     */
    final public function offset($start) {
        $this->_parts[self::OFFSET] = (int) $start;
        return $this;
    }

    /**
     * Выборка указанного числа строк.
     *
     * Варианты использования:
     * <ul>
     *     <li>null = FETCH FIRST ROWS ONLY</li>
     *     <li>5 = FETCH FIRST 5 ROWS ONLY</li>
     * </ul>
     *
     * @final
     * @param null|int $countколичество записей
     * @return Select
     * @access public
     */
    final public function fetch($count = null) {
        if (is_int($count)) {
            $this->_parts[self::FETCH] = (int) $count;

        } elseif ($count !== null) {
            $this->_parts[self::FETCH] = true;
        }

        return $this;
    }

    /**
     * Определения источника данных.
     *
     * Варианты использования:
     * <ul>
     *     <li>'table'</li>
     *     <li>'scheme.table'</li>
     *     <li>array('alias' => 'table')</li>
     *     <li>array('alias' => 'schema.table')</li>
     *     <li>Select</li>
     *     <li>array('alias' => Select)</li>
     *     <li>\Closure</li>
     *     <li>array('alias' => \Closure)</li>
     * </ul>
     *
     * @final
     * @param Select|\Closure|string|array $expr
     * @param array|string|null $columns
     * @return Select
     * @access public
     */
    final public function from($expr, $columns = '*') {
        $this->columns($columns);
        return $this->_from($expr);
    }

    /**
     * UNION запрос.
     *
     * Варианты использования:
     * <ul>
     *     <li>Select</li>
     * </ul>
     *
     * @final
     * @param Select $select
     * @param bool $all выборка всех записей, по умолчанию только уникальные
     * @return Select
     * @access public
     */
    final public function union(Select $select, $all = false) {
        return $this->_select($select, (($all) ? self::FROM_UNION_ALL : self::FROM_UNION));
    }

    /**
     * INTERSECT запрос.
     *
     * Варианты использования:
     * <ul>
     *     <li>Select</li>
     * </ul>
     *
     * @final
     * @param Select $select
     * @param bool $all выборка всех записей, по умолчанию только уникальные
     * @return Select
     * @access public
     */
    final public function intersect(Select $select, $all = false) {
        return $this->_select($select, (($all) ? self::FROM_INTERSECT_ALL : self::FROM_INTERSECT));
    }

    /**
     * EXCEPT запрос.
     *
     * Варианты использования:
     * <ul>
     *     <li>Select</li>
     * </ul>
     *
     * @final
     * @param Select $select
     * @param bool $all выборка всех записей, по умолчанию только уникальные
     * @return Select
     * @access public
     */
    final public function except(Select $select, $all = false) {
        return $this->_select($select, (($all) ? self::FROM_EXCEPT_ALL : self::FROM_EXCEPT));
    }

    /**
     * JOIN запрос.
     *
     * Строки из каждой таблицы сравниваются с использованием условия сравнения.<br>
     * Результат включает в себя только те строки, которые удовлетворяют условию объединения.<br>
     * Результат может быть пустым, если ни одна строка не удовлетворяет этому условию.<br><br>
     *
     * Все СУРБД поддерживают этот тип объединения.<br><br>
     *
     * Варианты использования:
     * <ul>
     *     <li>'table'</li>
     *     <li>'scheme.table'</li>
     *     <li>array('alias' => 'table')</li>
     *     <li>array('alias' => 'schema.table')</li>
     *     <li>Select</li>
     *     <li>array('alias' => Select)</li>
     *     <li>\Closure</li>
     *     <li>array('alias' => \Closure)</li>
     * </ul>
     *
     * @final
     * @param Select|\Closure|string|array $expr
     * @param null|string $condition условие объединения
     * @param null|string|array $columns
     * @return Select
     * @access public
     */
    final public function join($expr, $condition = null, $columns = array()) {
        return $this->joinInner($expr, $condition, $columns);
    }

    /**
     * Аналог join().
     *
     * Варианты использования:
     * <ul>
     *     <li>'table'</li>
     *     <li>'scheme.table'</li>
     *     <li>array('alias' => 'table')</li>
     *     <li>array('alias' => 'schema.table')</li>
     *     <li>Select</li>
     *     <li>array('alias' => Select)</li>
     *     <li>\Closure</li>
     *     <li>array('alias' => \Closure)</li>
     * </ul>
     *
     * @final
     * @param Select|\Closure|string|array $expr
     * @param null|string $condition условие объединения
     * @param null|string|array $columns
     * @return Select
     * @access public
     */
    final public function joinInner($expr, $condition = null, $columns = array()) {
        return $this->_join(self::INNER_JOIN, $expr, $condition, $columns);
    }

    /**
     * LEFT JOIN запрос.
     *
     * В результат входят все строки из таблицы слева и все соответствующие строки из таблицы справа.<br>
     * Если нет соответствующих строк из таблицы справа, то соответствующие столбцы в результате заполняются NULL.<br<br>
     *
     * Все СУРБД поддерживают этот тип объединения.<br><br>
     *
     * Варианты использования:
     * <ul>
     *     <li>'table'</li>
     *     <li>'scheme.table'</li>
     *     <li>array('alias' => 'table')</li>
     *     <li>array('alias' => 'schema.table')</li>
     *     <li>Select</li>
     *     <li>array('alias' => Select)</li>
     *     <li>\Closure</li>
     *     <li>array('alias' => \Closure)</li>
     * </ul>
     *
     * @final
     * @param Select|\Closure|string|array $expr
     * @param null|string $condition условие объединения
     * @param null|string|array $columns
     * @return Select
     * @access public
     */
    final public function joinLeft($expr, $condition = null, $columns = array()) {
        return $this->_join(self::LEFT_JOIN, $expr, $condition, $columns);
    }

    /**
     * RIGHT JOIN запрос.
     *
     * Правое внешнее объединение дополняет левое внешнее объединение.<br>
     * В результат входят все строки из таблицы справа и все соответствующие строки из таблицы слева.<br>
     * Если нет соответствующих строк из таблицы слева, то соответствующие столбцы в результате заполняются NULL.<br><br>
     *
     * Некоторые СУРБД не поддерживают этот тип объединения, но, как правило,<br>
     * любое правое объединение может быть заменено на левое посредством изменения порядка таблиц на обратный.<br><br>
     *
     * Варианты использования:
     * <ul>
     *     <li>'table'</li>
     *     <li>'scheme.table'</li>
     *     <li>array('alias' => 'table')</li>
     *     <li>array('alias' => 'schema.table')</li>
     *     <li>Select</li>
     *     <li>array('alias' => Select)</li>
     *     <li>\Closure</li>
     *     <li>array('alias' => \Closure)</li>
     * </ul>
     *
     * @final
     * @param Select|\Closure|string|array $expr
     * @param null|string $condition условие объединения
     * @param null|string|array $columns
     * @return Select
     * @access public
     */
    final public function joinRight($expr, $condition = null, $columns = array()) {
        return $this->_join(self::RIGHT_JOIN, $expr, $condition, $columns);
    }

    /**
     * FULL JOIN запрос.
     *
     * Полное внешнее объединение является как бы комбинацией левого и правого объединений.<br>
     * Все строки из обоих таблиц входят в результат, при этом объединяются друг с другом в одну строку<br>
     * результата, если соответствуют условию объединения, иначе объединяются с NULL<br>
     * вместо значений столбцов из другой таблицы.<br><br>
     *
     * Некоторые СУРБД не поддерживают этот тип объединения.<br><br>
     *
     * Варианты использования:
     * <ul>
     *     <li>'table'</li>
     *     <li>'scheme.table'</li>
     *     <li>array('alias' => 'table')</li>
     *     <li>array('alias' => 'schema.table')</li>
     *     <li>Select</li>
     *     <li>array('alias' => Select)</li>
     *     <li>\Closure</li>
     *     <li>array('alias' => \Closure)</li>
     * </ul>
     *
     * @final
     * @param Select|\Closure|string|array $expr
     * @param null|string $condition условие объединения
     * @param null|string|array $columns
     * @return Select
     * @access public
     */
    final public function joinFull($expr, $condition = null, $columns = array()) {
        return $this->_join(self::FULL_JOIN, $expr, $condition, $columns);
    }

    /**
     * CROSS JOIN запрос.
     *
     * Перекрестное объединение является декартовым произведением.<br>
     * Каждая строка в первой таблице объединяется с со всеми строками во второй таблице.<br>
     * Таким образом, количество строк в результате будет равно произведению числа строк в обоих таблицах.<br><br>
     *
     * Метод joinCross() не имеет параметров для определения условий объединения.<br>
     * Некоторые СУРБД не поддерживают этот тип объединения.<br><br>
     *
     * Варианты использования:
     * <ul>
     *     <li>'table'</li>
     *     <li>'scheme.table'</li>
     *     <li>array('alias' => 'table')</li>
     *     <li>array('alias' => 'schema.table')</li>
     *     <li>Select</li>
     *     <li>array('alias' => Select)</li>
     *     <li>\Closure</li>
     *     <li>array('alias' => \Closure)</li>
     * </ul>
     *
     * @final
     * @param Select|\Closure|string|array $expr
     * @param null|string|array $columns
     * @return Select
     */
    final public function joinCross($expr, $columns = array()) {
        return $this->_join(self::CROSS_JOIN, $expr, null, $columns);
    }

    /**
     * Регистрация параметра запроса.
     *
     * @final
     * @param string $param название параметра
     * @param Select|\Closure|mixed $value значение параметра
     * @param int $type
     * @return Select
     * @access public
     */
    final public function bindParam($param, $value, $type = Db::PARAM_STR) {
        if (!preg_match('/^\:[a-z0-9_]+$/i', $param)) {
            throw new Except(Except::PARAM_NAME, array($param));
        }

        $this->_binds[$param] = $value;
        $this->_types[$param] = self::combineType($value, $type);
        return $this;
    }

    /**
     * Удаление параметра запроса.
     *
     * @final
     * @param string $param название параметра
     * @return Select
     * @access public
     */
    final public function unbindParam($param) {
        unset($this->_binds[$param], $this->_types[$param]);
        return $this;
    }

    /**
     * Получение значения параметра.
     *
     * @final
     * @param string $param название параметра
     * @return array
     * @throw \LSF2\Infr\Db\Exception
     * @access public
     */
    final public function getParam($param) {
        if (!array_key_exists($param, $this->_binds)) {
            throw new Except(Except::PARAM_UNDEF, array($param));
        }

        return $this->_binds[$param];
    }

    /**
     * Получение значения типа параметра.
     *
     * @final
     * @param string $param название параметра
     * @return int
     * @access public
     */
    final public function getParamType($param) {
        return (isset($this->_types[$param]) ? $this->_types[$param] : Db::PARAM_STR);
    }

    /**
     * Проверка наличия параметра.
     *
     * @final
     * @param string $param название параметра
     * @return bool
     * @access public
     */
    final public function hasParam($param) {
        return array_key_exists($param, $this->_binds);
    }

    /**
     * Очистка параметров.
     *
     * @final
     * @param null|int $mask маска типов
     * @return Select
     * @access public
     */
    final public function resetParams($mask = null) {
        if ($mask) {
            foreach ($this->_binds as $name => $value) {
                if ($this->getParamType($name) & $mask) {
                    unset($this->_binds[$name], $this->_types[$name]);
                }
            }

        } else {
            $this->_binds = array();
            $this->_types = array();
        }

        return $this;
    }

    /**
     * Формирование не экранируемого выражения.
     *
     * @final
     * @static
     * @param $expression - не экранируемое выражение
     * @return \Closure
     * @access public
     */
    final public static function expr($expression) {
        return function() use ($expression) {
            return $expression;
        };
    }

    /**
     * Формирование строки запроса.
     *
     * @final
     * @param array $params список именованных локальных переменных
     * @param array $types список типов переменных
     * @return mixed|string
     * @throws Except
     * @access public
     */
    final public function assemble(array $params = array(), array $types = array()) {
        $this->resetParams(Db::PARAM_AUTO);

        foreach ($params as $param => $value) {
            $type = ((isset($types[$param])) ? $types[$param] : Db::PARAM_STR);
            $this->bindParam($param, $value, $type + Db::PARAM_AUTO);
        }

        $params = array();
        $types = array();

        $sql = '';

        /*if ($this->_parts[self::WITH] !== null) {
            $with = $this->_parts[self::WITH];
            if ($with['column']) {
                foreach ($with['column'] as &$item) {
                    $item = $this->escapeIdentifier($item);
                }
                unset($item);
            }

            $sql .= 'WITH '
                . (($with['recursive']) ? 'RECURSIVE ' : '')
                . $with['queryName'] . ' '
                . (($with['column']) ? '(' . implode(', ', $with['column']) . ') ' : '')
                . 'AS ' . $this->escapeIdentifier($with['select']) . "\n";

            unset($with);
        }*/

        $sql .= 'SELECT ';

        if ($this->_parts[self::DISTINCT] !== null) {
            $dist = $this->_parts[self::DISTINCT];
            $sql .= 'DISTINCT ';

            if ($dist) {
                foreach ($dist as &$item) {
                    $item = $this->escapeIdentifier($item);
                }
                unset($item);
                $sql .= 'ON (' . implode(', ', $dist) . ') ';
            }

            unset($dist);
        }

        if ($this->_parts[self::COLUMNS] !== null) {
            $columns = $this->_parts[self::COLUMNS];

            foreach ($columns as &$item) {
                $item = $this->escapeIdentifier($item['expr'])
                    . (($item['alias']) ? ' AS ' . $this->escapeIdentifier($item['alias']) : '');
            }

            $sql .= "\n\t" . implode(",\n\t", $columns) . "\n";
            unset($columns, $item);
        }

        if ($this->_parts[self::FROM] !== null) {
            $from = array();
            foreach ($this->_parts[self::FROM] as $item) {
                $from[] = ",\n\t";
                $from[] = $this->escapeIdentifier($item['expr']) . (($item['alias']) ? ' AS ' . $this->escapeIdentifier($item['alias']) : '');
            }

            if ($from) {
                unset($from[0]);
                $sql .= "FROM \n\t" . implode('', $from) . "\n";
            }

            unset($from);
        }

        if ($this->_parts[self::JOIN] !== null) {
            $join = array();
            foreach ($this->_parts[self::JOIN] as $item) {
                $join[] = $item['flags'] . ' '
                    . $this->escapeIdentifier($item['expr'])
                    . (($item['alias']) ? ' AS ' . $this->escapeIdentifier($item['alias']) : '')
                    . (($item['cond']) ? ' ON ' . $this->_parseCondition($item['cond'], array(), Db::PARAM_STR, $params, $types) : '');
            }

            $sql .= "\t" . implode("\n\t", $join) . "\n";
            unset($join);
        }

        if ($this->_parts[self::WHERE] !== null) {
            $conds = array();
            foreach ($this->_parts[self::WHERE] as $item) {
                $conds[] = (($item['or']) ? "\tOR " : "\tAND ");
                $conds[] = '(' . $this->_parseCondition($item['cond'], $item['params'], $item['type'], $params, $types) . ")\n";
            }

            if ($conds) {
                unset($conds[0]);
                $sql .= "WHERE \t" . implode('', $conds);
            }
            unset($conds);
        }

        if ($this->_parts[self::GROUP] !== null) {
            $group = $this->_parts[self::GROUP];

            foreach ($group as &$item) {
                $item = $this->escapeIdentifier($item);
            }

            $sql .= 'GROUP BY ' . implode(', ', $group) . "\n";
            unset($group, $item);
        }

        if ($this->_parts[self::HAVING] !== null) {
            $conds = array();
            foreach ($this->_parts[self::HAVING] as $item) {
                $conds[] = (($item['or']) ? "\tOR " : "\tAND ");
                $conds[] = '(' . $this->_parseCondition($item['cond'], $item['params'], $item['type'], $params, $types) . ")\n";
            }

            if ($conds) {
                unset($conds[0]);
                $sql .= "HAVING \t" . implode('', $conds);
            }
            unset($conds);
        }


        // render Window


        if ($this->_parts[self::SELECT] !== null) {
            $select = array();
            foreach ($this->_parts[self::SELECT] as $item) {
                $select[] = "\n\n" . $item['flags'] . "\n\n";
                $select[] = $this->escapeIdentifier($item['expr']);
            }

            if ($select) {
                $sql .= implode('', $select) . "\n";
            }

            unset($select);
        }

        if ($this->_parts[self::ORDER] !== null) {
            $order = $this->_parts[self::ORDER];

            foreach ($order as &$item) {
                $direct = '';
                if ($item['flags'] & self::ORDER_DESC) {
                    $direct .= ' DESC';
                }

                if ($item['flags'] & self::ORDER_NLAST) {
                    $direct .= ' NULLS LAST';
                }

                $item = $this->escapeIdentifier($item['expr']) . $direct;
            }

            $sql .= 'ORDER BY ' . implode(', ', $order) . "\n";
            unset($order, $item);
        }

        if ($this->_parts[self::LIMIT] !== null) {
            $sql .= 'LIMIT ' . $this->_parts[self::LIMIT] . "\n";
        }

        if ($this->_parts[self::OFFSET] !== null) {
            $sql .= 'OFFSET ' . $this->_parts[self::OFFSET] . "\n";
        }

        if ($this->_parts[self::FETCH] !== null) {
            $sql .= 'FETCH FIRST ' . $this->_parts[self::FETCH] . " ROWS ONLY\n";
        }

        // render For

        foreach ($params as $param => $value) {
            $this->bindParam($param, $value, $types[$param] + Db::PARAM_AUTO);
        }

        unset($params, $types);
        return trim(preg_replace('/\n+/', "\n", $sql));
    }

    /**
     * Выполнение подстановки всех переменных в запрос.
     *
     * @final
     * @param string $sql строка SQL запроса
     * @param null|int $mask маска типов
     * @return string
     * @access public
     */
    final public function prepare($sql, $mask = null) {
        $self = $this;
        return preg_replace_callback(self::REG_PARAM, function($matches) use ($self, $mask) {
            if (isset($matches[1])) {
                /* @var Select $self */
                $param = $matches[2];
                $type = $self->getParamType($param);

                if (!$mask || ($mask & $type)) {
                    return $matches[1] . $self->escape($self->getParam($param), $type);
                }
            }

            return $matches[0];
        }, $sql);
    }

    /**
     * Подсьановка произвольных переменных в запрос.
     *
     * @final
     * @param string $sql строка SQL запроса
     * @param array $params массив параметров
     * @param array $types массив типов параметров
     * @param null|int $mask маска типов
     * @return string
     * @throws Except
     * @access public
     */
    final public function prepareParams($sql, array $params, array $types = array(), $mask = null) {
        $self = $this;
        return preg_replace_callback(self::REG_PARAM, function($matches) use ($self, $params, $types, $mask) {
            if (isset($matches[1])) {
                /* @var Select $self */
                $param = $matches[2];
                $type = (empty($types[$param]) ? Db::PARAM_STR : $types[$param]);

                if (!array_key_exists($param, $params)) {
                    throw new Except(Except::PARAM_UNDEF, array($param));
                }

                if (!$mask || ($type & $mask)) {
                    return $matches[1] . $self->escape($params[$param], $type);
                }
            }

            return $matches[0];
        }, $sql);
    }

    /**
     * Параметризованный запрос.
     *
     * @final
     * @param array $params список именованных локальных переменных
     * @param array $types список типов переменных
     * @return Statement
     * @access public
     */
     public function execute(array $params = array(), array $types = array()) {
        $ass = $this->assemble($params, $types);
        $sql = $this->prepare($ass, Db::MASK_STRUCT);
        $params = &$this->_binds;
        $types = &$this->_types;

        $this->event()
            ->trigger(Db::EVENT_BEFORE_SELECT, array(&$sql, &$params, &$types));

        $stmt = $this->_adapter
            ->execute($sql, $params, $types);

        $this->event()
            ->trigger(Db::EVENT_AFTER_SELECT);

        return $stmt;
    }

    /**
     * Запрос.
     *
     * @final
     * @return Statement
     * @access public
     */
    final public function query() {
        $sql = $this->prepare($this->assemble());

        $this->event()
            ->trigger(Db::EVENT_BEFORE_SELECT, array(&$sql, null, null));

        $stmt = $this->_adapter
            ->query($sql);

        $this->event()
            ->trigger(Db::EVENT_AFTER_SELECT);

        return $stmt;
    }

    /**
     * Получение количества записей.
     * @param $rebuild - признак пересборки запроса (добавлено и используется группой ТИ)
     * @final
     * @return int
     * @access public
     */
    final public function count($rebuild = true) {
        if ($this->hasPart(self::GROUP) || $this->hasPart(self::HAVING) || $this->hasPart(self::DISTINCT) || !$rebuild) {
            $sql = "SELECT COUNT(*)\nFROM (" . $this->prepare($this->assemble(), Db::MASK_STRUCT) . ") AS lsf_db_inner";
            $params = &$this->_binds;
            $types = &$this->_types;

            $this->event()
                ->trigger(Db::EVENT_BEFORE_SELECT, array(&$sql, &$params, &$types));

            $rez = $this->_adapter
                ->execute($sql, $params, $types)
                ->fetchOne();

            $this->event()
                ->trigger(Db::EVENT_AFTER_SELECT);

        } else {
            $rez = $this->reset(self::COLUMNS)
                ->reset(self::ORDER)
                ->columns(self::expr('COUNT(*)'))
                ->execute()
                ->fetchOne();
        }

        return (int) $rez;
    }

    /**
     * Экранирование частей запроса.
     *
     * @final
     * @param string|Select|\Closure $value
     * @return string
     * @throws Except
     * @access public
     */
    final public function escapeIdentifier($value) {
        $origType = self::type($value);

        switch ($origType) {
            case Db::PARAM_EXPR:
                return (string) call_user_func($value);

            case Db::PARAM_SELECT:
                return '(' . $value->prepare($value->assemble()) . ')';

            case Db::PARAM_STR:
                $adapter = $this->_adapter;
                return preg_replace_callback('/([^[:^punct:]]){0,1}([[:alnum:]\_]+)[[:alnum:]\_' . $adapter::QUOTE_CHAR . ']*[[:alnum:]\_]*/i', function($matches) use ($adapter) {
                    /* @var Adapter $adapter */
                    return (($matches[1] != ':') ? $matches[1] . $adapter->escapeIdentifier($matches[2]) : $matches[1] . $matches[2]);
                }, $value);

            default:
                return var_export($value, true);
        }
    }

    /**
     * Преобразование данных после экранирования.
     *
     * @param mixed $value
     * @param int $type
     * @return bool|float|int|string
     */
    final public function unescape($value, $type) {
        return $this->_adapter->unescape($value, $type);
    }

    /**
     * Экранирование пользовательских данных.
     *
     * @final
     * @param mixed|Select|\Closure $value
     * @param int $type
     * @return string
     * @access public
     */
    final public function escape($value, $type = Db::PARAM_STR) {
        if ($type & Db::PARAM_ARR) {
            $type = $type - Db::PARAM_ARR;
            foreach ($value as &$p) {
                $p = $this->escape($p, $type);
            }

            return '(' . implode(', ', $value) . ')';

        } /*elseif ($type & Db::PARAM_DATAARR) {
            if (!is_array($value)) {
                $value = array($value);
            }

            $type = $type - Db::PARAM_DATAARR;
            foreach ($value as &$p) {
                //$p = $this->escape($p, $type);
            }

            return '\'{' . implode(', ', $value) . '}\'';

        }*/ elseif ($type & Db::PARAM_EXPR) {
            return call_user_func($value);

        } elseif ($type & Db::PARAM_SELECT) {
            return '(' . $value->prepare($value->assemble()) . ')';
        }

        return $this->_adapter->escape($value, $type);
    }

    /**
     * Проверка подключения JOIN.
     *
     * @final
     * @param Select|\Closure|string $expr
     * @param null|string $alias
     * @return bool
     * @throws Except
     * @access public
     */
    final public function requireJoin($expr, $alias = null) {
        if ($this->_parts[self::JOIN]) {
            foreach ($this->_parts[self::JOIN] as $join) {
                if ($join['expr'] == $expr && (!$alias || ($alias && $join['alias'] == $alias))) {
                    return true;
                }
            }
        }

        throw new Except(Except::JOIN_UNDEF, array($expr, $alias));
    }

    /**
     * Добавление FROM части запроса.
     *
     * Варианты использования:
     * <ul>
     *     <li>'table'</li>
     *     <li>'scheme.table'</li>
     *     <li>array('alias' => 'table')</li>
     *     <li>array('alias' => 'schema.table')</li>
     *     <li>Select</li>
     *     <li>array('alias' => Select)</li>
     *     <li>\Closure</li>
     *     <li>array('alias' => \Closure)</li>
     * </ul>
     *
     * @param Select|\Closure|string $expr
     * @return Select
     * @access private
     */
    private function _from($expr) {
        if ($this->_parts[self::FROM] === null) {
            $this->_parts[self::FROM] = array();
        }

        $alias = null;
        if (is_array($expr)) {
            $alias = key($expr);
            $expr = $expr[$alias];
        }

        $this->_parts[self::FROM][] = array(
            'expr' => $expr,
            'alias' => $alias
        );

        return $this;
    }

    /**
     * Добавление UNION | INTERSECT | EXCEPT части запроса.
     *
     * Варианты использования:
     * <ul>
     *     <li>Select</li>
     * </ul>
     *
     * @param Select $expr
     * @param null|int $flags
     * @return Select
     * @access private
     */
    private function _select(Select $expr, $flags = null) {
        if ($this->_parts[self::SELECT] === null) {
            $this->_parts[self::SELECT] = array();
        }

        $this->_parts[self::SELECT][] = array(
            'expr' => $expr,
            'flags' => $flags
        );

        return $this;
    }

    /**
     * Добавление JOIN части запроса.
     *
     * @param string $flags
     * @param Select|\Closure|string|array $expr
     * @param null|mixed $condition
     * @param null|string|array $columns
     * @return Select
     * @access private
     */
    private function _join($flags, $expr, $condition, $columns) {
        if ($this->_parts[self::JOIN] === null) {
            $this->_parts[self::JOIN] = array();
        }

        $this->columns($columns);

        $alias = null;
        if (is_array($expr)) {
            $alias = key($expr);
            $expr = current($expr);
        }

        $this->_parts[self::JOIN][] = array(
            'expr' => $expr,
            'alias' => $alias,
            'cond' => $condition,
            'flags' => $flags
        );

        return $this;
    }

    /**
     * Добавление WHERE части запроса.
     *
     * @param $condition
     * @param null|mixed $params
     * @param bool $or
     * @param int|array $type
     * @return Select
     * @access private
     */
    private function _where($condition, $params, $or, $type) {
        if ($this->_parts[self::WHERE] === null) {
            $this->_parts[self::WHERE] = array();
        }

        if (is_array($condition)) {
            foreach ($condition as $c => $p) {
                if (is_int($c)) {
                    $this->_where($p, null, $or, $type);

                } else {
                    $this->_where($c, $p, $or, $type);
                }
            }

        } else {
            $this->_parts[self::WHERE][] = array(
                'cond' => $condition,
                'params' => $params,
                'type' => $type,
                'or' => $or
            );
        }

        return $this;
    }

    /**
     * Добавление HAVING части запроса.
     *
     * @param $condition
     * @param null|mixed $params
     * @param bool $or
     * @param int|array $type
     * @return Select
     * @access private
     */
    private function _having($condition, $params, $or, $type) {
        if ($this->_parts[self::HAVING] === null) {
            $this->_parts[self::HAVING] = array();
        }

        if (is_array($condition)) {
            foreach ($condition as $c => $p) {
                if (is_int($c)) {
                    $this->_having($p, null, $or, $type);

                } else {
                    $this->_having($c, $p, $or, $type);
                }
            }

        } else {
            $this->_parts[self::HAVING][] = array(
                'cond' => $condition,
                'params' => $params,
                'type' => $type,
                'or' => $or
            );
        }

        return $this;
    }

    /**
     * Разбор строки условия с выделением параметров запроса.
     *
     * @param string $condition
     * @param array $params
     * @param int|array $types
     * @param array $target
     * @param array $targetTypes
     * @return string
     * @throws Except
     * @access protected
     */
    protected function _parseCondition($condition, $params, $types, &$target = array(), &$targetTypes = array()) {
        static $pid = 0;

        // условие содержит только строку без параметров для подстановки
        $adapter = $this->_adapter;
        if (preg_match('/^[a-z0-9._' . $adapter::QUOTE_CHAR . ']+$/si', $condition)) {
            $condition = $this->escapeIdentifier($condition);
            $types = self::combineType($params, $types);
            $params = array($params);

            if ($types & (Db::PARAM_ARR + Db::PARAM_SELECT)) {
                $condition .= ' IN ?';

            } elseif ($types & Db::PARAM_NULL) {
                $condition .= ' IS ?';

            } else {
                $condition .= ' = ?';
            }

            return $this->_parseCondition($condition, $params, $types, $target, $targetTypes);
        }



        if (!is_array($params)) {
            $params = array($params);
        }

        $typesArray = is_array($types);

        $condition = trim($condition);


        if (preg_match_all(self::REG_PARAM, $condition, $match)) {
            foreach ($match[2] as $param) {
                // ранее добавленный параметр повторно не добавляется
                if (!$param || $this->hasParam($param)) {
                    continue;
                }

                if (!array_key_exists($param, $params)) {
                    throw new Except(Except::PARAM_UNDEF, array($param));
                }

                $type = ($typesArray) ? ((isset($types[$param])) ? $types[$param] : 0) : $types;
                $target[$param] = $params[$param];
                $targetTypes[$param] = self::combineType($params[$param], $type);
            }
        }

        $cvars = substr_count($condition, '?');
        if ($cvars) {
            if ($cvars != count($params)) {
                throw new Except(Except::COND_VARS);
            }

            if ($typesArray && $cvars != count($types)) {
                throw new Except(Except::COND_TYPES);
            }

            $offset = 0;
            $i = 0;
            while (($pos = strpos($condition, '?', $offset)) !== false) {
                $pid++;
                $param = ':__param' . $pid;
                $type = ($typesArray) ? $types[$i] : $types;
                $target[$param] = $params[$i];
                $targetTypes[$param] = self::combineType($params[$i], $type);
                $condition = substr_replace($condition, $param, $pos, 1);
                $i++;
                $offset = $pos + 1;
            }

        } elseif (preg_match('/\$\d+/', $condition)) {
            $self = $this;
            $condition = preg_replace_callback('/\$(\d+)/', function($matches) use ($self, $params, $types, $typesArray, &$target, &$targetTypes, &$pid) {
                /* @var Select $self */
                $i = (int) $matches[1] - 1;
                if (!array_key_exists($i, $params) || ($typesArray && !array_key_exists($i, $types))) {
                    throw new Except(Except::COND_KEY);
                }

                $pid++;
                $param = ':__param' . $pid;
                $type = ($typesArray) ? $types[$i] : $types;
                $target[$param] = $params[$i];
                $targetTypes[$param] = $self::combineType($params[$i], $type);
                return $param;
            }, $condition);
        }

        return $condition;
    }

    /**
     * Получение типа аргумента.
     *
     * @static
     * @param mixed $value
     * @return int
     * @throws Except
     * @access protected
     */
    protected static function type($value) {
        $type = gettype($value);
        $dbtype = 0;

        switch ($type) {
            case 'integer':
                $dbtype = Db::PARAM_INT;
                break;

            case 'double':
                $dbtype = Db::PARAM_FLOAT;
                break;

            case 'NULL':
                $dbtype = Db::PARAM_NULL;
                break;

            case 'string':
                $dbtype = Db::PARAM_STR;
                break;

            case 'boolean':
                $dbtype = Db::PARAM_BOOL;
                break;

            case 'array':
                $dbtype = Db::PARAM_ARR;
                break;

            case 'object':
                if ($value instanceof Select) {
                    $dbtype = Db::PARAM_SELECT;

                } elseif ($value instanceof \Closure) {
                    $dbtype = Db::PARAM_EXPR;
                }
                break;
        }

        if (!$dbtype) {
            throw new Except(Except::TYPE_UNDEF);
        }

        return $dbtype;
    }

    /**
     * Преобразование типа для комбинированного параметра.
     *
     * @static
     * @param mixed $value
     * @param int $type
     * @return int
     * @access public
     */
    public static function combineType($value, $type) {
        $origType = self::type($value);

        switch ($origType) {
            case Db::PARAM_NULL: // @todo возможно для null лишнее
            case Db::PARAM_SELECT:
                $type = 0;
            case Db::PARAM_ARR:
                if ($type & Db::PARAM_DATAARR) {
                    $origType = 0;
                }
            case Db::PARAM_EXPR:
                if (!($type & $origType)) {
                    $type = ($type + $origType);
                }
        }

        return $type;
    }
}