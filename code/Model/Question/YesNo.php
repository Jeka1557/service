<?php


namespace Model\Question;


class YesNo extends \Model\Question {

    /**
     * @var \Model\Collection\Answer
     */
    protected $_answers;

    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var YesNo $entity */
        $entity = parent::newFromArray($data);

        if (is_null($entity))
            return null;


        $answers = new \Model\Collection\Answer();
        $answers[1] = \Model\Answer::newFromArray(array(
                        'id' => 1,
                        'header' => 'Да',
                        'idx' => 1,
                        'excl' => false,
                        'contextData' => array(0 => array(
                            'contextId' => 0,
                            'text' => 'Да',
                            'notExists' => 0),
                        )
                     ));
        $answers[2] = \Model\Answer::newFromArray(array(
                        'id' => 2,
                        'header' => 'Нет',
                        'idx' => 2,
                        'excl' => false,
                        'contextData' => array(0 => array(
                            'contextId' => 0,
                            'text' => 'Нет',
                            'notExists' => 0)
                        )
                     ));

        $entity->_answers = $answers;

        $entity->_questionType = 'yesno';

        return $entity;
    }

    /**
     * @deprecated
     */
    public function renderPreview() {
        /*
        $templFile = new Infr\BlockTemplate('Question', 'YesNoPreview');
        $templFile->assign('question', $this);

        return $templFile->parse();
        */
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

        } elseif (self::$renderMode==self::RENDER_MODE_WC) {
            return $this->renderTemplate('Question', 'wc/YesNo', [
                'question' => $this,
                'inGroup' => $inGroup,
            ]);

        } elseif (self::$renderMode==self::RENDER_MODE_VTB) {
            return $this->renderTemplate('Question', 'vtb/YesNo', [
                'question' => $this,
                'inGroup' => $inGroup,
            ]);

        } else {
            return $this->renderTemplate('Question', 'YesNo', [
                'question' => $this,
                'inGroup' => $inGroup,
            ]);
        }
    }
}