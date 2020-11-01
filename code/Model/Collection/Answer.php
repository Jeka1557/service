<?php


namespace Model\Collection;
use Lib\Model\Collection;


class Answer extends Collection {
    protected $entityClass = '\Model\Answer';

    protected $groupProperties = [
        'id' => []
    ];

    public function sortByIdx($desc = false) {
        usort($this->array, function($a, $b) use ($desc) {
            if ($a->idx == $b->idx)
                return 0;
            else if ($a->idx > $b->idx)
                return $desc?-1:1;
            else
                return $desc?1:-1;
        });
    }
}
