<?php


namespace Model\Algorithm\Node\Collection;
use Lib\Model\Collection;


class Risk extends Collection {
    protected $entityClass = '\Model\Algorithm\Node\Risk';
    protected $groupProperties = [
        'riskId' => []
    ];
}
