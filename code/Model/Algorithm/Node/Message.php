<?php


namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class Message extends Node {

    public $messageId;

    /**
     * @var \Model\Message
     */
    public $message;


    public function getEndpoints() {
        return [ 1 => '' ];
    }


    static function createFromArray($data) {
        $node = new self();

        $node->id = (int)$data['id'];
        $node->messageId = (int)$data['message_id'];

        return $node;
    }

    public function nextNode() {
        return $this->findChild(1);
    }
}