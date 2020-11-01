<?php


namespace Model;
use TP;

use Model\Exception;

/**
 * @property $id
 * @property TP\Text\Plain $header
 * @property TP\Text\HTML $text
 * @property \Model\Collection\Answer $answers
 *
 * @property $answerId
 * @property $defaultAnswerId
 */

class Question extends DictEntity {

    protected $_id;
    protected $_header;

    protected $_defaultAnswerId;

    protected $_answerId;

    protected $_questionType;

    /**
     * @var \Model\Collection\Answer
     */
    protected $_answers;

    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var Question $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'PT\QuestionId');
        $entity->_header = static::castVar($data['header'],'TP\Text\Plain');

        $entity->_extId =  "{$entity->_id}".($data['copyId']>0?"_{$data['copyId']}":'');

        $entity->_defaultAnswerId = ($data['defaultAnswerId']>0)?static::castVar($data['defaultAnswerId'],'PT\AnswerId'):null;

        $entity->_entityType = \PT\EntityType::QUESTION();

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        return $entity;
    }

    public function setAnswerId($id) {
        $this->_answerId = (int)$id;
    }

    public function render($inGroup = false) {
        return '';
    }


    public function __get($name) {
        try {
            return parent::__get($name);

        } catch (\Exception $e) {
            $m = array();

            if (preg_match('~->(\w+) is not initialized\.$~', $e->getMessage(), $m)) {
                switch ($m[1]) {
                    case 'defaultAnswerId':
                        throw new \Model\Exception\Question("question: {$this->_id}", \Model\Exception\Question::DEFAULT_ANSWER_IS_NOT_SET);
                    case 'document':
                        throw new \Model\Exception\Question("question: {$this->_id} property: {$this->_documentId}", \Model\Exception\Question::DOCUMENT_IS_NOT_INITIALIZED);
                    case 'documentGeneral':
                        throw new \Model\Exception\Question("question: {$this->_id} document: {$this->_documentGeneralId}", \Model\Exception\Question::DOCUMENT_GENERAL_IS_NOT_INITIALIZED);
                    default:
                        throw $e;
                }
            } else
                throw $e;

        }
    }
}