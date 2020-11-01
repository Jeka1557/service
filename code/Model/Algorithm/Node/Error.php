<?php


namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class Error extends Node {

    public $error;

    public function __construct(\Exception $e)
    {
        parent::__construct();
        $this->error = new \Model\Algorithm\Error($e);
    }

    public function nextNode() { }
}