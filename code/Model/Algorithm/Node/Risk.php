<?php


namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class Risk extends Node {
    public $riskId;
    /**
     * @var \Model\Risk
     */
    public $risk;

    public function getEndpoints() {
        return array(1 => '');
    }

    /*
    function toGraphArray() {
        $result = parent::toGraphArray();
        $result['risk_id'] = $this->riskId;
        $result['document_id'] = $this->risk->documentId;

        return $result;
    }
    */

    static function createFromArray($data) {
        $node = new self();

        $node->id = (int)$data['id'];
        $node->riskId = (int)$data['risk_id'];

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