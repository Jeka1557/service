<?php


namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class Algorithm extends Node {
    public $algId;

    /**
     * @var \Model\Algorithm
     */
    public $alg;

    protected $inputEndPointId;

    public function getEndpoints() {
        $endpoints = array();

        foreach ($this->alg->endPoints as $endPoint) {
            /* @var $endPoint \Model\Algorithm\Node\EndPoint */
            $endpoints[$endPoint->id] = '';
        }

        return $endpoints;
    }
/*
    function toGraphArray() {
        $result = parent::toGraphArray();
        $result['alg_id'] = $this->algId;

        return $result;
    }
*/
    static function createFromArray($data) {
        $node = new self();

        $node->id = (int)$data['id'];
        $node->algId = (int)$data['alg_id'];

        /*
        $node->comment = $data['comment'];
        $node->x = (int)$data['x'];
        $node->y = (int)$data['y'];
        $node->width = (int)$data['width'];
        $node->height = (int)$data['height'];
        */

        return $node;
    }


    public function setEndPoint($id) {
        try {
            $this->findChild($id);
        } catch (\Exception $e) {
            throw new \Exception("Node Algorithm {$this->id} hasn't EndPoint {$id}");
        }

        $this->inputEndPointId = $id;
    }

    public function nextNode() {
        if (is_null($this->inputEndPointId))
            throw new \Exception("Node Algorithm {$this->id} EndPoint not set");

        return $this->findChild($this->inputEndPointId);
    }
}