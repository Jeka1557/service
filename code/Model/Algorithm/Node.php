<?php


namespace Model\Algorithm;
use Lib\Model\Entity;


abstract class Node extends Entity {

    const START_POINT = 'START_POINT';
    const RISK        = 'RISK';
    const QUESTION    = 'QUESTION';
    const INFO        = 'INFO';
    const END_POINT   = 'END_POINT';
    const ALGORITHM   = 'ALGORITHM';
    const QUESTION_END    = 'QUESTION_END';
    const CONCLUSION    = 'CONCLUSION';
    const EXPRESSION    = 'EXPRESSION';
    const ERROR         = 'ERROR';
    const MESSAGE       = 'MESSAGE';

    const SRV_TYPE_QUESTION = 'question';
    const SRV_TYPE_INFO = 'infoData';
    const SRV_TYPE_FILE = 'infoFile';
    const SRV_TYPE_HIDDEN = 'infoHidden';
    const SRV_TYPE_RISK = 'risk';
    const SRV_TYPE_WARNING = 'warning';
    const SRV_TYPE_CONCLUSION = 'conclusion';
    const SRV_TYPE_EXPRESSION = 'expression';
    const SRV_TYPE_ERROR = 'error';
    const SRV_TYPE_END_POINT = 'endpoint';
    const SRV_TYPE_ALGORITHM = 'algorithm';
    const SRV_TYPE_ACTION = 'action';
    const SRV_TYPE_MESSAGE = 'message';


    public $id;
    public $loopId = 0;
    public $links = [];

    public $type;

    public $scrGroup = 0;

    public $contextId = 0;


    public function __construct() {
        if (is_a($this, '\Model\Algorithm\Node\Question')) {
            $this->type = self::QUESTION;
        } elseif (is_a($this, '\Model\Algorithm\Node\ComplexQuestion')) {
            $this->type = self::QUESTION;
        } elseif (is_a($this, '\Model\Algorithm\Node\QuestionEnd')) {
            $this->type = self::QUESTION_END;
        } elseif (is_a($this, '\Model\Algorithm\Node\Info')) {
            $this->type = self::INFO;
        } elseif (is_a($this, '\Model\Algorithm\Node\StartPoint')) {
            $this->type = self::START_POINT;
        } elseif (is_a($this, '\Model\Algorithm\Node\EndPoint')) {
            $this->type = self::END_POINT;
        } elseif (is_a($this, '\Model\Algorithm\Node\Risk')) {
            $this->type = self::RISK;
        } elseif (is_a($this, '\Model\Algorithm\Node\Message')) {
            $this->type = self::MESSAGE;
        } elseif (is_a($this, '\Model\Algorithm\Node\Algorithm')) {
            $this->type = self::ALGORITHM;
        } elseif (is_a($this, '\Model\Algorithm\Node\Conclusion')) {
            $this->type = self::CONCLUSION;
        } elseif (is_a($this, '\Model\Algorithm\Node\Expression')) {
            $this->type = self::EXPRESSION;
        } elseif (is_a($this, '\Model\Algorithm\Node\Error')) {
            $this->type = self::ERROR;
        }
    }

    /**
     * @return Node
     */

    abstract public function nextNode() ;

    public function loopClone($loopId) {
        $node = clone $this;
        $node->reset();
        $node->loopId = $loopId;

        return $node;
    }

    public function reset() {
    }

    /**
     * @param $answerId
     * @return Node
     * @throws \Exception
     */

    protected function findChild($answerId) {
        foreach($this->links as $link) {
            /** @var \Model\Algorithm\Link $link */
            if ($link->answerId==$answerId)
                return $link->child;
        }

        throw new \Exception("Node: link with answerId: {$answerId} not found.");
    }


    public function addLink(\Model\Algorithm\Link $link) {
        if (isset($this->links[$link->id]))
            throw new \Exception("Link id: {$link->id} already exists.");

        $this->links[$link->id] = $link;
    }

    public function getEndpoints() {
        return array();
    }


    static function createFromArray($data) {
        $node = new static();

        $node->id = (int)$data['id'];

        return $node;
    }

    protected function applyComment($comment) {
        $comment = trim($comment);
        $m = [];

        if (preg_match('~^\$contextId=(\d+)$~', $comment, $m)) {
            $this->contextId = (int)$m[1];
        }
    }

}