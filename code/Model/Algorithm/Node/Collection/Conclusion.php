<?php


namespace Model\Algorithm\Node\Collection;
use Lib\Model\Collection;


class Conclusion extends Collection {
    protected $entityClass = '\Model\Algorithm\Node\Conclusion';
    protected $groupProperties = [
        'conclusionId' => []
    ];
}
