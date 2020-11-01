<?php
/**
 * Date 21.08.12 16:56
 * @author anton
 * @package LSF2\Infr\Db
 * @copyright Lightsoft 2012
 */

namespace Lib\Infr\Db;
use \Lib\Infr\Db as Db;

/**
 * Event
 *
 */
final class Event {

    private $_binds = array();

    /**
     * Регистрация обработчика события.
     *
     * @param $event - название события
     * @param $callback - обработчик
     * @return Event
     * @throws Exception
     * @access public
     */
    public function bind($event, $callback) {
        if (!is_callable($callback)) {
            throw new Exception(Exception::NOT_CALLABLE);
        }

        if (empty($this->_binds[$event])) {
            $this->_binds[$event] = array();
        }

        $this->_binds[$event][] = $callback;
        return $this;
    }

    /**
     * Удаление обработчика события.
     *
     * @param $event - название события
     * @param $callback - обработчик
     * @return Event
     * @access public
     */
    public function unbind($event, $callback = null) {
        if (!empty($this->_binds[$event])) {
            if ($callback) {
                foreach ($this->_binds[$event] as $key => $_event) {
                    if ($callback == $_event) {
                        unset($this->_binds[$event][$key]);
                    }
                }

            } else {
                unset($this->_binds[$event]);
            }
        }

        return $this;
    }

    /**
     * Выполнение всех обработчиков событий.
     *
     * @param $event - название события
     * @param array $data
     * @return Event
     * @access public
     */
    public function trigger($event, array $data = array()) {
        if (!empty($this->_binds[$event])) {
            foreach ($this->_binds[$event] as $_event) {
                call_user_func_array($_event, $data);
            }
        }

        return $this;
    }
}