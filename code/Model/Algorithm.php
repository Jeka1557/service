<?php


namespace Model;
use Lib\Model\Entity;

/*
 * @property-read $id
 * @property-read $header
 * @property-read $screenGroup
 */

class Algorithm extends Entity {

    protected $_id;
    protected $_extId;

    protected $_name;
    protected $_header;
    protected $_isActive = true;

    protected $_screenGroup = false;

    public  $endPoints;

    public  $infoMap = [];
    public  $questionMap = [];

    public  $hasMap  = false;


    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var Algorithm $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'PT\AlgorithmId');
        $entity->_name = static::castVar($data['name'],'TP\Text\Plain');
        $entity->_header = $entity->_name;

        $entity->_extId =  "{$entity->_id}".($data['copyId']>0?"_{$data['copyId']}":'');
        $entity->_screenGroup = static::castVar($data['screenGroup'],'TP\TBool');


        $entity->hasMap = $data['hasMap'];

        //2082
        /*
        if (in_array((int)$entity->id,[2105, 2001, 2023, 2117, 2153, 2148, 2149, 2037, 2151, 2164, 2170, 2171, 2180, 2201]))
            $entity->_screenGroup = true;
        else
            $entity->_screenGroup = false;

        */

        return $entity;
    }

}



