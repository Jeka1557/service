<?php


namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class StartPoint extends Node {
    /**
     * @var \Model\Algorithm
     */
    public $alg;

    public function getEndpoints() {
        return array(1 => '');
    }

    /*
    function toGraphArray() {
        $result = parent::toGraphArray();
        $result['comment'] = $this->alg->name;

        return $result;
    }
    */

    public function nextNode() {
        return $this->findChild(1);
    }
}