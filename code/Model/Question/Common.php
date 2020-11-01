<?php


namespace Model\Question;


class Common extends \Model\Question {

    /**
     * @var \Model\Collection\Answer
     */
    protected $_answers;

    protected $_listView = false;

    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var Common $entity */
        $entity = parent::newFromArray($data);

        $entity->_questionType = 'common';

        if (isset($data['settings']['list_view']) and $data['settings']['list_view'])
            $entity->_listView = true;

        /*
        if (in_array((int)$entity->id,[7634]))
            $entity->_listView = true;
        else
            $entity->_listView = false;
        */

        return $entity;
    }

    public function __set($name, $value) {
        switch ($name) {
            case 'answers':
                $this->_answers = static::castVar($value, '\Model\Collection\Answer');
                break;
            default:
                parent::__set($name, $value);
                break;
        }
    }

    public function getSelectedAnswerId() {
        if (!count($this->_answers))
            return null;

        if (!is_null($this->_answerId))
            return $this->_answerId;

        if (!is_null($this->_defaultAnswerId))
            return $this->_defaultAnswerId;

        $answer = $this->_answers->rewind();
        return $answer->id;
    }



    public function render($inGroup = false) {
        if (self::$renderMode==self::RENDER_MODE_ARRAY) {
            $result = [
                'text' => $this->text,
                'answers' => [],
                'type' => $this->_questionType,
            ];

            foreach ($this->answers as $answer) {
                $result['answers'][$answer->id] = $answer->text;
            }

            return $result;

        }  elseif (self::$renderMode==self::RENDER_MODE_BST4) {
            return $this->renderTemplate('Question', 'Common2', [
                'question' => $this,
                'inGroup' => $inGroup,
            ]);

        } elseif (self::$renderMode==self::RENDER_MODE_WC) {
            return $this->renderTemplate('Question', 'wc/Common', [
                'question' => $this,
                'inGroup' => $inGroup,
            ]);

        } elseif (self::$renderMode==self::RENDER_MODE_VTB) {
            return $this->renderTemplate('Question', 'vtb/Common', [
                'question' => $this,
                'inGroup' => $inGroup,
            ]);

        } else {
            return $this->renderTemplate('Question', 'Common', [
                'question' => $this,
                'inGroup' => $inGroup,
            ]);
        }
    }
}