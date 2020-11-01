<?php


namespace Model\Storage;
use Lib\Infr\Utility\Encoding;
use Model\Storage as Storage;
use Lib\Model\Collection;


class Question extends Storage {


    protected $tableName = 'question';
    protected $contextTableName = 'question_context';

   // protected $relContextTableName = 'dict.rel_question_context';

    protected $collectionClass = '\Model\Collection\Question';
    protected $defaultFieldName = 'question';


    function createEntity($row, $contextData, $className = null, $copyId = 0) {
        switch ($row['type']) {
            case 'doc':
                return \Model\Question\Document::newFromArray([
                    'id' => $row['id'],
                    'header' => $row['header'],
                    'documentId' => $row['document_id'],
                    'documentGeneralId' => $row['document_general_id'],
                    'defaultAnswerId' => $row['default_answer_id'],
                    'contextData' => $contextData,
                    'copyId' => $copyId,
                    'settings' => json_decode($row['settings'], true),
                ]);
            case 'yesno':
                return \Model\Question\YesNo::newFromArray([
                    'id' => $row['id'],
                    'header' => $row['header'],
                    'defaultAnswerId' => $row['default_answer_id'],
                    'contextData' => $contextData,
                    'copyId' => $copyId,
                    'settings' => json_decode($row['settings'], true),
                ]);
            case 'common':
                if (!$row['sngl_answer'])
                    throw new \Exception("Question with multiple answers unsupported");

                $question = \Model\Question\Common::newFromArray([
                    'id' => $row['id'],
                    'header' => $row['header'],
                    'defaultAnswerId' => $row['default_answer_id'],
                    'contextData' => $contextData,
                    'copyId' => $copyId,
                    'settings' => json_decode($row['settings'], true),
                ]);

                if (!is_null($question)) {
                    $storage = new \Model\Storage\Answer($this->dsn);
                    $question->answers = $storage->getByQuestion(new \TP\UInt2($question->id));
                    $question->answers->sortByIdx();
                }

                return $question;

            case 'complex':

                $question = \Model\Question\Complex::newFromArray([
                    'id' => $row['id'],
                    'header' => $row['header'],
                    'defaultAnswerId' => $row['default_answer_id'],
                    'contextData' => $contextData,
                    'inverted' => ($row['inverted']=='t')?true:false,
                    'copyId' => $copyId,
                    'settings' => json_decode($row['settings'], true),
                ]);

                if (!is_null($question)) {
                    $storage = new \Model\Storage\Answer($this->dsn);
                    $question->answers = $storage->getByQuestion(new \TP\UInt2($question->id));
                    $question->answers->sortByIdx();
                }

                return $question;


            default:
                throw new \Exception("Storage: unknown question type - ".$row['type']);
        }
    }

    function addExtraEntities(Collection $collection, $entities, $copyId = 0) {
        if ((in_array('document', $entities) or array_key_exists('document', $entities)) or
            (in_array('documentGeneral', $entities) or array_key_exists('documentGeneral', $entities))) {

            $docStorage = new Document($this->dsn);
            $docGenStorage = new DocumentGeneral($this->dsn);

            $docQstCollection = new \Model\Collection\Question\Document();

            foreach ($collection as $item) {
                if ($item instanceof \Model\Question\Document) {
                    $docQstCollection[] = $item;
                }
            }

            if (in_array('document', $entities) or array_key_exists('document', $entities))
                $docStorage->setToCollection($docQstCollection, null,  isset($entities['document'])?$entities['document']:array(), $copyId);

            if (in_array('documentGeneral', $entities) or array_key_exists('documentGeneral', $entities))
                $docGenStorage->setToCollection($docQstCollection, null, isset($entities['documentGeneral'])?$entities['documentGeneral']:array(), $copyId);
        }
    }

/*
    public function getById(\TP\Type $id, array $entities = array(), $className = null) {

        $select = new \LSF2\Infr\Db\Select($this->dbAdapter);
        $row = $select->from("dict.question")
            ->where("id", $id->val())
            ->execute()
            ->fetchRow();

        if (is_null($row))
            return null;

        $result = Model_Question::newFromArray(array(
            'id' => $row['id'],
            'header' => $row['header'],
            'text' => $row['text'],
        ));


        $storage = new Model_Storage_Answer($this->dsn);
        $result->answers = $storage->getByIds(new TP\Arr\UInt2Arr($this->yesNoAnsIds));

        return $result;
    }
*/
}