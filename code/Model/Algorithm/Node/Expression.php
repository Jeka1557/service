<?php

namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class Expression extends Node {
    public $expressionId;
    /**
     * @var \Model\Expression
     */
    public $expression;

    public $comment;

    public $value;

    public function getEndpoints() {
        return is_null($this->expression)?array():$this->expression->getNodeAnswers();
    }
    

    static function createFromArray($data) {
        $node = new self();

        $node->id = (int)$data['id'];
        $node->expressionId = (int)$data['expression_id'];
        $node->comment = $data['comment'];

        return $node;
    }


    public function nextNode() {
        return $this->findChild($this->expression->getNodeAnswerId());
    }

}