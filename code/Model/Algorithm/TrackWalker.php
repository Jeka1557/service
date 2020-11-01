<?php

namespace Model\Algorithm;




class TrackWalker {
    /**
     * @var \Model\Algorithm\Tree
     */
    protected $algorithm;

    /**
     * @var \Model\Algorithm\Node
     */
    protected $currentNode;

    /**
     * @var \Model\Algorithm\Node
     */
    protected $previousNode;


    protected $nodeIds = array();


    protected $loopId;


    public function __construct(\Model\Algorithm\Tree $algorithm, $loopId = 0) {
        $this->algorithm = $algorithm;
        $this->currentNode = $algorithm->getRootNode();
        $this->loopId = $loopId;
    }

    public function algorithmId() {
        return $this->algorithm->algorithmId;
    }

    /**
     * @return \Model\Algorithm\Node
     */
    public function currentNode() {
        return $this->currentNode;
    }

    /**
     * @return \Model\Algorithm\Node
     */
    public function previousNode() {
        return $this->previousNode;
    }


    public function goToNode(\Model\Algorithm\Node $node) {
        $node = $this->loop($node);

        $this->previousNode = $this->currentNode;
        $this->currentNode = $node;
    }

    /**
     * @return \Model\Algorithm\Node
     */
    public function nextNode() {
        $node = $this->currentNode->nextNode();

        $node = $this->loop($node);

        $this->previousNode = $this->currentNode;
        $this->currentNode = $node;

        return $this->currentNode;
    }

    protected function loop($node) {

        $nodeId = $node->id;
        $loopId = $this->loopId;

        if (is_a($node, '\Model\Algorithm\Node\QuestionEnd') and isset($this->nodeIds[$node->questionNode->id])) {
            // Если в $this->nodeIds нет вопроса, значит его алгоритм не проходил и пришли в конец вопроса в обход. В этом случае обрабатываем как обычный узел.
            /** @var \Model\Algorithm\Node\QuestionEnd $node  */
            $loopId = $this->nodeIds[$node->questionNode->id];

        } else {
            if (isset($this->nodeIds[$nodeId]))
                $loopId = $this->nodeIds[$nodeId] + 1;
        }


        if ($loopId>$node->loopId)
            $node = $node->loopClone($loopId);

        $this->nodeIds[$nodeId] = $loopId;

        return $node;
    }

}

