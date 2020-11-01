<?php


namespace Model\Algorithm\Node\Collection;
use Lib\Model\Collection;


class Expression extends Collection {
    protected $entityClass = '\Model\Algorithm\Node\Expression';
    protected $groupProperties = [
        'expressionId' => []
    ];
}
