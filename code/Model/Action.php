<?php


namespace Model;

/**
 * Class Action
 *
 * @property-read $id
 * @property-read $header
 *
 * @property-read $doneHash
 */

class Action extends DictEntity {

    const NOT_DONE = 1;
    const DONE_NOW = 2;
    const DONE_BEFORE = 3;

    protected $_id;
    protected $_header;

    protected $_actionHash;
    protected $_doneHash;

    protected $_done;

    protected $_message = '';


    /**
     * @var \Model\Algorithm\Executor
     */
    protected $_executor;



    protected function __construct() {}


    static public function newFromArray($data = []) {
        /* @var \Model\Action $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'PT\ActionId');
        $entity->_header = static::castVar($data['header'],'TP\Text\Plain');

        $entity->_extId =  "{$entity->_id}".($data['copyId']>0?"_{$data['copyId']}":'');

        return $entity;
    }


    public function run() {
        $this->initAction();

        if ($this->isDoneAction()) {
            $this->_done = self::DONE_BEFORE;
            return true;
        }

        $result = $this->doAction();

        if ($result) {
            $this->_doneHash = $this->_actionHash;
            $this->_done = self::DONE_NOW;
            return true;
        } else {
            $this->_doneHash = '';
            $this->_done = self::NOT_DONE;
            return false;
        }
    }


    public function setDone($hash) {
        $this->_doneHash = $hash;
    }

    protected function doAction() {
        return true;
    }

    protected function initAction() {
        $this->_actionHash = $this->makeHash();
    }

    protected function isDoneAction() {
        if ($this->_doneHash == $this->_actionHash)
            return true;
        else
            return false;
    }


    /**
     *  Хеш генерится от текущих входных параметров.
     *  Действие выполняется по набору входных параметров.
     *  Если они изменились - считается что такое действие не выполнялось.
     *  Для этого и нужен хеш.
     */

    protected function makeHash() {
       $this->_actionHash = md5((int)(time()/60*2));
    }


    public function setExecutor(\Model\Algorithm\Executor $executor) {
        $this->_executor = $executor;
    }

}