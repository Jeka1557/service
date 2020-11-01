<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 11.12.2017
 * Time: 02:39
 */

namespace Model\Collection\Question;
use Lib\Model\Collection;


class Document extends Collection {
    protected $entityClass = '\Model\Question\Document';
    protected $groupProperties = [
        'documentId' => [],
        'documentGeneralId' => []
    ];
}