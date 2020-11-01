<?php


namespace Model\Algorithm\Node\Collection;
use Lib\Model\Collection;


class Message extends Collection {
    protected $entityClass = '\Model\Algorithm\Node\Message';
    protected $groupProperties = [
        'messageId' => []
    ];
}
