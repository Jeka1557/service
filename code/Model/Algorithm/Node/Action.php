<?php

namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class Action extends Node {
    public $actionId;
    /**
     * @var \Model\Action
     */
    public $action;

    protected $doneHash;


    public function getEndpoints() {
        return array(1 => '');
    }

    static function createFromArray($data) {
        $node = new self();

        $node->id = (int)$data['id'];
        $node->actionId = (int)$data['action_id'];

        return $node;
    }


    public function nextNode() {
        return $this->findChild(1);
    }


    public function setDone($hash) {
        $this->doneHash = $hash;
        $this->action->setDone($hash);
    }


    public function reset() {
        $this->doneHash = null;

        /** @todo Разобраться нужно ли сбрасывать hash в action */
    }
}