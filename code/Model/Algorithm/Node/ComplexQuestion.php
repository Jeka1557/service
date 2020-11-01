<?php


namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class ComplexQuestion extends Question {

    public  $endNodeId;

    protected $_endNode;

    protected $passedAnswers = array();


    public function __set($name, $value) {
        switch ($name) {
            case 'endNode':
                $this->_endNode = static::castVar($value, '\Model\Algorithm\Node\QuestionEnd');
                break;
            default:
                parent::__set($name, $value);
                break;
        }
    }

/*
    function toGraphArray() {
        $result = parent::toGraphArray();
        $result['end_node_id'] = $this->endNodeId;
        $result['type'] = 'complex';

        return $result;
    }
*/
    static function createFromArray($data) {
        $node = new self();

        $node->id = (int)$data['id'];
        $node->questionId = (int)$data['question_id'];
        $node->endNodeId = (int)$data['end_node_id'];

        /*
        $node->comment = $data['comment'];
        $node->x = (int)$data['x'];
        $node->y = (int)$data['y'];
        $node->width = (int)$data['width'];
        $node->height = (int)$data['height'];
        */

        return $node;
    }


    public function setAnswer($ids) {
        $this->answer = $ids;

        if (!is_array($ids))
            $ids = array($ids);

        try {
            foreach ($ids as $id)
                $this->findChild($id);
        } catch (\Exception $e) {
            throw new \Model\Exception\Question("question: {$this->_id} answerId: {$id}", \Model\Exception\Question::ANSWER_NOT_FOUND);
        }

        $this->question->setAnswerId($ids);
    }

    public function setDefaultAnswer() {

        /*
        foreach ($this->links as $l)
            $link = $l;

        if (!isset($link))
            throw new \Model\Exception\Question("question: {$this->_id} ", \Model\Exception\Question::ANSWER_NOT_FOUND);

        $this->answer = $link->answerId;
        $this->question->setAnswerId([$link->answerId]);
        */

        $this->question->setAnswerId([]);
    }


    public function nextNode() {

        if (is_null($this->question))
        // Эта ситуация может возникнуть если пришли в конец вопроса, но не проходили начало, т.е стрелки идут минуя его.
            return $this->_endNode->nextNode();

        $answerIds = $this->question->answers->getGroupProperty('id');

        if ($this->question->inverted)
            $answerIds = array_diff($answerIds, $this->question->answerId);
        else
            $answerIds = array_intersect($answerIds, $this->question->answerId) ;


        foreach ($answerIds as $id) {
            if (!in_array($id, $this->passedAnswers)) {
                $this->passedAnswers[] = $id;
                return $this->findChild($id);
            }
        }

        return $this->_endNode->nextNode();
    }


    public function loopClone($loopId) {
        $node = clone $this;
        $node->reset();
        $node->loopId = $loopId;

        $node->_endNode = $node->_endNode->loopClone($loopId);
        $node->_endNode->questionNode = $node;

        return $node;
    }

    public function reset() {
        $this->answer = null;
        $this->passedAnswers = [];
    }

}