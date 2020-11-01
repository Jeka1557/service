<?php


namespace Model\Algorithm;

class Link {

    public $id;

    public $parentId;
    public $childId;
    public $answerId = 0;

    /**
     * @var \Model\Algorithm\Node
     */
    public $parent;
    /**
     * @var \Model\Algorithm\Node
     */
    public $child;

    public function toGraphArray() {
        return array(
            'id' => $this->id,
            'parent_id' => $this->parentId,
            'child_id' => $this->childId,
            'answer_id' => $this->answerId,
        );
    }

    static function createFromArray($data) {
        $node = new static();

        $node->id = (int)$data['id'];
        $node->parentId = (int)$data['parent_id'];
        $node->childId = (int)$data['child_id'];
        $node->answerId = (int)$data['answer_id'];

        return $node;
    }
}