<?php


namespace Model\Algorithm\Node\Collection;
use Lib\Model\Collection;


class Action extends Collection {
    protected $entityClass = '\Model\Algorithm\Node\Action';
    protected $groupProperties = [
        'actionId' => []
    ];
}
