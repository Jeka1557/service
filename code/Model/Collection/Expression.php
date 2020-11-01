<?php


namespace Model\Collection;
use Lib\Model\Collection;


class Expression extends Collection {
    protected $entityClass = '\Model\Expression';

    public function keys() {
        return array_keys($this->array);
    }

}
