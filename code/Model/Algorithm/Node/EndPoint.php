<?php


namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class EndPoint extends Node {

    public function nextNode() {
        throw new \Exception("Node EndPoint hasn't next node, but nextNode() method call");
    }
}