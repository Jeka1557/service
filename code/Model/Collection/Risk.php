<?php


namespace Model\Collection;
use Lib\Model\Collection;


class Risk extends Collection {
    protected $entityClass = '\Model\Risk';
    protected $groupProperties = [
        'documentId' => [],
        'documentGeneralId' => []
    ];
}
