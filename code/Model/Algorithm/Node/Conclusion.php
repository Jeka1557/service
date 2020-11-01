<?php

namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class Conclusion extends Node {
    public $conclusionId;
    /**
     * @var \Model\Conclusion
     */
    public $conclusion;

    public function getEndpoints() {
        return array(1 => '');
    }

    /*
    function toGraphArray() {
        $result = parent::toGraphArray();
        $result['conclusion_id'] = $this->conclusionId;

        return $result;
    }
    */

    static function createFromArray($data) {
        $node = new self();

        $node->id = (int)$data['id'];
        $node->conclusionId = (int)$data['conclusion_id'];

        /*
        $node->comment = $data['comment'];
        $node->x = (int)$data['x'];
        $node->y = (int)$data['y'];
        $node->width = (int)$data['width'];
        $node->height = (int)$data['height'];
        */

        return $node;
    }


    public function nextNode() {
        return $this->findChild(1);
    }

}