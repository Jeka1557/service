<?php


namespace Model\Algorithm\Node\Collection;
use Lib\Model\Collection;


class Algorithm extends Collection {
    protected $entityClass = '\Model\Algorithm\Node\Algorithm';
    protected $groupProperties = [
        'algId' => []
    ];
}
