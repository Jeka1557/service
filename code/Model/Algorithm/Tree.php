<?php


namespace Model\Algorithm;

use Lib\Infr\DSN;

class Tree {

    public $algorithmId;

    /**
     * @var DSN
     */
    protected $dsn;

    /**
     * @var \Model\Algorithm\Node
     */

    protected $rootNode;

    /**
     * @var \Model\Algorithm\Node[]
     */

    protected $nodes = array();

    /**
     * @var \Model\Algorithm\Link[]
     */

    protected $links = array();

    protected $entitiesAdded = false;


    protected function __construct($algorithmId) {
        $this->algorithmId = $algorithmId;
    }

    /**
     * @return \Model\Algorithm\Node[]
     */

    public function getNodes() {
        return $this->nodes;
    }

    /**
     * @return array|\Model\Algorithm\Link[]
     */

    public function getLinks() {
        return $this->links;
    }

    /**
     * @return \Model\Algorithm\Node
     */

    public function getRootNode() {
        return $this->rootNode;
    }

    public function getName() {
        return $this->name;
    }


    /**
     * @return \Model\Algorithm\Node\Collection\Message
     */

    public function getNodeMessageCollection() {
        $collection = new \Model\Algorithm\Node\Collection\Message();

        foreach ($this->nodes as $node) {
            /* @var $node \Model\Algorithm\Node */
            if (is_a($node, '\Model\Algorithm\Node\Message')) {
                $collection[] = $node;
            }
        }

        return $collection;
    }

    /**
     * @return \Model\Algorithm\Node\Collection\Risk
     */

    public function getNodeRiskCollection() {
        $collection = new \Model\Algorithm\Node\Collection\Risk();

        foreach ($this->nodes as $node) {
            /* @var $node \Model\Algorithm\Node */
            if (is_a($node, '\Model\Algorithm\Node\Risk')) {
                $collection[] = $node;
            }
        }

        return $collection;
    }

    /**
     * @return \Model\Algorithm\Node\Collection\Algorithm
     */

    public function getNodeAlgorithmCollection() {
        $collection = new \Model\Algorithm\Node\Collection\Algorithm();

        foreach ($this->nodes as $node) {
            /* @var $node \Model\Algorithm\Node */
            if (is_a($node,'\Model\Algorithm\Node\Algorithm')) {
                $collection[] = $node;
            }
        }

        return $collection;
    }

    /**
     * @return \Model\Algorithm\Node\Collection\Question
     */

    public function getNodeQuestionCollection() {
        $collection = new \Model\Algorithm\Node\Collection\Question();

        foreach ($this->nodes as $node) {
            /* @var $node \Model\Algorithm\Node */
            if (is_a($node, '\Model\Algorithm\Node\Question') or is_a($node, '\Model\Algorithm\Node\ComplexQuestion')) {
                $collection[] = $node;
            }
        }

        return $collection;
    }


    /**
     * @return \Model\Algorithm\Node\Collection\Info
     */

    public function getNodeInfoCollection() {
        $collection = new \Model\Algorithm\Node\Collection\Info();

        foreach ($this->nodes as $node) {
            /* @var $node \Model\Algorithm\Node */
            if (is_a($node, '\Model\Algorithm\Node\Info')) {
                $collection[] = $node;
            }
        }

        return $collection;
    }


    /**
     * @return \Model\Algorithm\Node\Collection\Conclusion
     */

    public function getNodeConclusionCollection() {
        $collection = new \Model\Algorithm\Node\Collection\Conclusion();

        foreach ($this->nodes as $node) {
            /* @var $node \Model\Algorithm\Node */
            if (is_a($node, '\Model\Algorithm\Node\Conclusion')) {
                $collection[] = $node;
            }
        }

        return $collection;
    }


    /**
     * @return \Model\Algorithm\Node\Collection\Action
     */

    public function getNodeActionCollection() {
        $collection = new \Model\Algorithm\Node\Collection\Action();

        foreach ($this->nodes as $node) {
            /* @var $node \Model\Algorithm\Node */
            if (is_a($node, '\Model\Algorithm\Node\Action')) {
                $collection[] = $node;
            }
        }

        return $collection;
    }



    /**
     * @return \Model\Algorithm\Node\Collection\Expression
     */

    public function getNodeExpressionCollection() {
        $collection = new \Model\Algorithm\Node\Collection\Expression();

        foreach ($this->nodes as $node) {
            /* @var $node \Model\Algorithm\Node */
            if (is_a($node, '\Model\Algorithm\Node\Expression')) {
                $collection[] = $node;
            }
        }

        return $collection;
    }


    /**
     * @return \Model\Algorithm\Node\StartPoint
     */

    public function getNodeStartPoint() {
        return $this->rootNode;
    }

    protected function setRootNode(\Model\Algorithm\Node\StartPoint $node) {
       if (isset($this->rootNode))
           throw new \Exception("Root node already exists");

       $this->addNode($node);
       $this->rootNode = $node;
    }


    protected function addNode(\Model\Algorithm\Node $node) {
        $this->checkNode($node->id, false);
        $this->nodes[$node->id] = $node;
    }


    protected function addLink(\Model\Algorithm\Link $link) {
        $this->checkNode($link->childId);
        $this->checkNode($link->parentId);

        $link->child = $this->nodes[$link->childId];
        $link->parent = $this->nodes[$link->parentId];

        $link->parent->addLink($link);

        /**
         * @todo проверку на то что такой пока не существует
         */
        $this->links[$link->id] = $link;
    }


    protected function checkNode($id, $exists = true) {
        if ($exists) {
            if (!isset($this->nodes[$id]))
                throw new NodeNotExistsException("Node id: {$id} doesn't exists.");
        } else {
            if (isset($this->nodes[$id]))
                throw new NodeAlreadyExistsException("Node id: {$id} already exists.");
        }
    }


    /**
     * @static
     * @param $id
     * @return Tree
     */

    static public function create($id, DSN $dsn) {

        $tblMessage = static::createNodeTable('Message', $dsn);
        $tblQuestion = static::createNodeTable('Question', $dsn);
        $tblQuestionEnd = static::createNodeTable('QuestionEnd', $dsn);
        $tblRisk = static::createNodeTable('Risk', $dsn);
        $tblAlgorithm = static::createNodeTable('Algorithm', $dsn);
        $tblInfo = static::createNodeTable('Info', $dsn);
        $tblConclusion = static::createNodeTable('Conclusion', $dsn);
        $tblAction = static::createNodeTable('Action', $dsn);
        $tblExpression = static::createNodeTable('Expression', $dsn);
        $tblEndPoint = static::createNodeTable('EndPoint', $dsn);
        $tblStartPoint = static::createNodeTable('StartPoint', $dsn);
        $tblLink = static::createNodeTable('Link', $dsn);


        $result = new static($id);
        $result->dsn = $dsn;

        $row = $tblStartPoint->where("algorithm_id", $id)->execute()->fetchRow();
        $result->setRootNode(static::createNode('StartPoint', $row));


        $rows = $tblQuestion->where("algorithm_id", $id)->execute()->fetchAll();

        foreach ($rows as $row) {
            if ($row['end_node_id']>0)
                $result->addNode(static::createNode('ComplexQuestion', $row));
            else
                $result->addNode(static::createNode('Question', $row));
        }

        $rows = $tblQuestionEnd->where("algorithm_id", $id)->execute()->fetchAll();

        foreach ($rows as $row) {
            $result->addNode(static::createNode('QuestionEnd', $row));
        }

        $rows = $tblMessage->where("algorithm_id", $id)->execute()->fetchAll();

        foreach ($rows as $row) {
            $result->addNode(static::createNode('Message', $row));
        }

        $rows = $tblRisk->where("algorithm_id", $id)->execute()->fetchAll();

        foreach ($rows as $row) {
            $result->addNode(static::createNode('Risk', $row));
        }

        $rows = $tblAlgorithm->where("algorithm_id", $id)->execute()->fetchAll();

        foreach ($rows as $row) {
            $result->addNode(static::createNode('Algorithm', $row));
        }

        $rows = $tblInfo->where("algorithm_id", $id)->execute()->fetchAll();

        foreach ($rows as $row) {
            $result->addNode(static::createNode('Info', $row));
        }

        $rows = $tblConclusion->where("algorithm_id", $id)->execute()->fetchAll();

        foreach ($rows as $row) {
            $result->addNode(static::createNode('Conclusion', $row));
        }

        $rows = $tblAction->where("algorithm_id", $id)->execute()->fetchAll();

        foreach ($rows as $row) {
            $result->addNode(static::createNode('Action', $row));
        }

        $rows = $tblExpression->where("algorithm_id", $id)->execute()->fetchAll();

        foreach ($rows as $row) {
            $result->addNode(static::createNode('Expression', $row));
        }

        $rows = $tblEndPoint->where("algorithm_id", $id)->execute()->fetchAll();

        foreach ($rows as $row) {
            $result->addNode(static::createNode('EndPoint', $row));
        }

        $rows = $tblLink->where("algorithm_id", $id)->execute()->fetchAll();

        foreach ($rows as $row) {
            $result->addLink(static::createNode('Link', $row));
        }

        $result->setDependencies();

        return $result;
    }

    protected function setDependencies() {
        foreach ($this->nodes as $node) {
            if (is_a($node, '\Model\Algorithm\Node\ComplexQuestion')) {
                $node->endNode = $this->nodes[$node->endNodeId];
            }
            if (is_a($node, '\Model\Algorithm\Node\QuestionEnd')) {
                $node->questionNode = $this->nodes[$node->questionNodeId];
            }
        }
    }


    static protected function createNodeTable($name, $dsn) {

        switch ($name) {
            case 'Message': return new \Infr\Db\Node\Message($dsn); break;
            case 'Question': return new \Infr\Db\Node\Question($dsn); break;
            case 'QuestionEnd': return new \Infr\Db\Node\QuestionEnd($dsn); break;
            case 'Risk': return new \Infr\Db\Node\Risk($dsn); break;
            case 'Algorithm': return new \Infr\Db\Node\Algorithm($dsn); break;
            case 'Info': return new \Infr\Db\Node\Info($dsn); break;
            case 'Conclusion': return new \Infr\Db\Node\Conclusion($dsn); break;
            case 'Action': return new \Infr\Db\Node\Action($dsn); break;
            case 'Expression': return new \Infr\Db\Node\Expression($dsn); break;
            case 'EndPoint': return new \Infr\Db\Node\EndPoint($dsn); break;
            case 'StartPoint': return new \Infr\Db\Node\StartPoint($dsn); break;
            case 'Link': return new \Infr\Db\Link($dsn); break;
            default:
                throw new \Exception("Unknown node table {$name}");
        }
    }


    static protected function createNode($name, $item) {

        switch ($name) {
            case 'StartPoint': return \Model\Algorithm\Node\StartPoint::createFromArray($item); break;
            case 'Message': return \Model\Algorithm\Node\Message::createFromArray($item); break;
            case 'ComplexQuestion': return \Model\Algorithm\Node\ComplexQuestion::createFromArray($item); break;
            case 'Question': return \Model\Algorithm\Node\Question::createFromArray($item); break;
            case 'QuestionEnd': return \Model\Algorithm\Node\QuestionEnd::createFromArray($item); break;
            case 'Risk': return \Model\Algorithm\Node\Risk::createFromArray($item); break;
            case 'Algorithm': return \Model\Algorithm\Node\Algorithm::createFromArray($item); break;
            case 'Info': return \Model\Algorithm\Node\Info::createFromArray($item); break;
            case 'Conclusion': return \Model\Algorithm\Node\Conclusion::createFromArray($item); break;
            case 'Action': return \Model\Algorithm\Node\Action::createFromArray($item); break;
            case 'Expression': return \Model\Algorithm\Node\Expression::createFromArray($item); break;
            case 'EndPoint': return \Model\Algorithm\Node\EndPoint::createFromArray($item); break;
            case 'Link': return \Model\Algorithm\Link::createFromArray($item); break;

            default:
                throw new \Exception("Unknown node {$name}");
        }
    }

}

class NodeNotExistsException extends \Exception {}

class NodeAlreadyExistsException extends \Exception {}