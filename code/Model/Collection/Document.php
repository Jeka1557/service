<?php


namespace Model\Collection;
use Lib\Model\Collection;


class Document extends Collection {
    protected $entityClass = '\Model\Document';

    protected $groupProperties = [
        'documentGeneralId' => []
    ];
}
