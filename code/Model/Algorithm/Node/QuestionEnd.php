<?php


namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class QuestionEnd extends Node {

    public $questionId;
    public $questionNodeId;

    protected $_questionNode;

    static protected $loopNodes = [];


    public function getEndpoints() {
        return array(1 => '');
    }

    public function __set($name, $value) {
        switch ($name) {
            case 'questionNode':
                $this->_questionNode = static::castVar($value, '\Model\Algorithm\Node\Question');
                break;
            default:
                parent::__set($name, $value);
                break;
        }
    }


    /*
    function toGraphArray() {
        $result = parent::toGraphArray();
        $result['question_id'] = $this->questionId;
        $result['question_node_id'] = $this->questionNodeId;

        return $result;
    }
    */

    static function createFromArray($data) {
        $node = new self();

        $node->id = (int)$data['id'];
        $node->questionId = (int)$data['question_id'];
        $node->questionNodeId = (int)$data['question_node_id'];

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


    public function loopClone($loopId) {
        if (isset(self::$loopNodes[$this->id][$loopId]))
            return self::$loopNodes[$this->id][$loopId];

        $node = clone $this;
        $node->reset();
        $node->loopId = $loopId;

        self::$loopNodes[$node->id][$loopId] = $node;

        return $node;
    }
}