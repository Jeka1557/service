<?php


namespace Model\Question;


class Complex extends \Model\Question {

    /**
     * @var \Model\Collection\Answer
     */
    protected $_answers;

    protected $_inverted;

    protected $_exclAnswerId = 0;

    public function setAnswerId($id) {
        $this->_answerId = array_map('intval', $id);
    }


    public function __set($name, $value) {
        switch ($name) {
            case 'answers':
                $this->_answers = static::castVar($value, '\Model\Collection\Answer');

                foreach ($this->_answers as $answer) {
                    if ($answer->excl) {
                        $this->_exclAnswerId = $answer->id;
                        break;
                    }
                }

                break;
            default:
                parent::__set($name, $value);
                break;
        }
    }


    static public function newFromArray($data = []) {
        /* @var Complex $entity */
        $entity = parent::newFromArray($data);
        $entity->_inverted = $data['inverted'];

        $entity->_questionType = 'complex';

        return $entity;
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

        } elseif (self::$renderMode==self::RENDER_MODE_BST4) {
            return $this->renderTemplate('Question', 'Complex2', [
                'question' => $this,
                'inGroup' => $inGroup,
            ]);

        } elseif (self::$renderMode==self::RENDER_MODE_WC) {
            return $this->renderTemplate('Question', 'wc/Complex', [
                'question' => $this,
                'inGroup' => $inGroup,
            ]);

        } elseif (self::$renderMode==self::RENDER_MODE_VTB) {
            return $this->renderTemplate('Question', 'vtb/Complex', [
                'question' => $this,
                'inGroup' => $inGroup,
            ]);

        } else {
            return $this->renderTemplate('Question', 'Complex', [
                'question' => $this,
                'inGroup' => $inGroup,
            ]);
        }
    }
}