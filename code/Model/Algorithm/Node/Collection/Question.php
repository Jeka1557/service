<?php


namespace Model\Algorithm\Node\Collection;
use Lib\Model\Collection;


class Question extends Collection {
    protected $entityClass = '\Model\Algorithm\Node\Question';
    protected $groupProperties = [
        'questionId' => []
    ];
}
