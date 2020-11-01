<?php


namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class Question extends Node {

    public $questionId;

    /**
     * @var \Model\Question
     */
    public $question;
    public $hidden = false;

    protected $answer;


    public function getEndpoints() {
        $endpoints = array();

        if (!is_null($this->question)) {
            foreach ($this->question->answers as $answer) {
                /* @var \Model\Answer $answer */
                $endpoints[$answer->id] = (string)$answer->header; // json не правильно воспринимает null, надо пустую строку
            }
        }

        return $endpoints;
    }


/*
    function toGraphArray() {
        $result = parent::toGraphArray();
        $result['question_id'] = $this->questionId;
        $result['type'] = 'single';


        if ($this->question instanceof \Model\Question\Document) {
            $result['document_id'] = $this->question->documentId;
        } else
            $result['document_id'] = 0;

        return $result;
    }
*/

    static function createFromArray($data) {
        $node = new self();

        $node->id = (int)$data['id'];
        $node->questionId = (int)$data['question_id'];
        $node->applyComment($data['comment']);

        /*
        $node->x = (int)$data['x'];
        $node->y = (int)$data['y'];
        $node->width = (int)$data['width'];
        $node->height = (int)$data['height'];
        */

        return $node;
    }


    public function setAnswer($id) {
        $this->answer = $id;
        /**
         * @todo перенести эту проверку в класс Question
         */
        try {
            $this->findChild($id);
        } catch (\Exception $e) {
            throw new \Model\Exception\Question("question: {$this->_id} answerId: {$id}", \Model\Exception\Question::ANSWER_NOT_FOUND);
        }

        $this->question->setAnswerId($id);
    }

    public function setDefaultAnswer() {

        if (!$this->question->isEmpty('defaultAnswerId')) {
            $this->answer = $this->question->defaultAnswerId;
            $this->question->setAnswerId($this->question->defaultAnswerId);

        } else {
            foreach ($this->links as $l)
                $link = $l;

            if (!isset($link))
                throw new \Model\Exception\Question("question: {$this->_id} ", \Model\Exception\Question::ANSWER_NOT_FOUND);

            $this->answer = $link->answerId;
            $this->question->setAnswerId($link->answerId);
        }
    }


    public function hasAnswer() {
        return is_null($this->answer)?false:true;
    }

    public function getAnswer() {
        return $this->answer;
    }

    public function setHidden($hidden) {
        $this->hidden = $hidden;
    }

    public function nextNode() {
        return $this->findChild($this->question->answerId);
    }

    public function reset() {
        $this->answer = null;
    }
}
