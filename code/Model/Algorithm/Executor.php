<?php


namespace Model\Algorithm;

use PT;
use Lib\Infr\DSN;
use Lib\Infr\Db\Adapter;
use Infr\Config;
use Model\DictEntity;


class Executor {
    /**
     * @var DSN
     */
    protected $dsn;

    /**
     * @var TrackWalker
     */
    protected $trackWalker;

    /**
     * @var AnswerSubstitutor
     */
    protected $answerSubstitutor;

    protected $trackWalkerStack = array();
    protected $algIdStack = [];

    protected $answerSubstitutorStack = array();
    protected $complexQuestionStack = array();


    protected $isDone = false;

    protected $screenGroup = 0;

    protected $contextIdStack = [];


    /**
     * @var \Model\Algorithm
     */
    protected $algorithm;

    /**
     * @var \Model\Storage\Algorithm
     */
    protected $algorithmStorage;

    /**
     * @var \Model\Storage\Risk
     */
    protected $riskStorage;

    /**
     * @var \Model\Storage\RiskGeneral
     */
    protected $riskGeneralStorage;


    /**
     * @var \Model\Storage\Question
     */
    protected $questionStorage;

    /**
     * @var \Model\Storage\Info
     */
    protected $infoStorage;


    /**
     * @var \Model\Storage\Conclusion
     */
    protected $conclusionStorage;


    /**
     * @var \Model\Storage\Expression
     */
    protected $expressionStorage;

    /**
     * @var \Model\Storage\Action
     */
    protected $actionStorage;


    /**
     * @var \Model\Storage\Message
     */
    protected $messageStorage;




    protected $nodeSequence = array();

    /**
     * @var \Model\Algorithm\Result
     */
    protected $result;

    /**
     * @var  \Model\Algorithm\Logger
     */
    protected $logger;

    /*
     * @var \Model\Algorithm\Error
     */
    protected $error;

    protected $UID;


    protected function createAlgorithmTree($algorithmId, $dsn) {
        return \Model\Algorithm\Tree::create($algorithmId, $dsn);
    }

    public function __construct($algorithmId, DSN $dsn, $UID = null) {

        $algorithmTree = $this->createAlgorithmTree($algorithmId, $dsn);

        $this->trackWalker = new TrackWalker($algorithmTree);
        $this->dsn = $dsn;

        if (is_null($UID))
            $this->UID = $this->getNewUID($dsn);
        else
            $this->UID = $UID;

        $this->logger = new Logger(SERVICE_ROOT, $dsn, $this->UID);

        $this->result = new Result();

        $this->riskStorage = new \Model\Storage\Risk($dsn);
        $this->riskGeneralStorage = new \Model\Storage\RiskGeneral($dsn);
        $this->qstStorage = new \Model\Storage\Question($dsn);
        $this->infStorage = new \Model\Storage\Info($dsn);
        $this->conclusionStorage = new \Model\Storage\Conclusion($dsn);
        $this->expressionStorage = new \Model\Storage\Expression($dsn);
        $this->algorithmStorage = new \Model\Storage\Algorithm($dsn);
        $this->actionStorage = new \Model\Storage\Action($dsn);
        $this->messageStorage = new \Model\Storage\Message($dsn);


        $algorithm = $this->algorithmStorage->getById(new PT\AlgorithmId($algorithmId));
        $this->algorithm = $algorithm;

        $this->answerSubstitutor = new AnswerSubstitutor($algorithm->infoMap, $algorithm->questionMap, \Model\DictEntity::getContext(), $this->result);

        $this->logger->startAlgorithm($algorithm, \Model\DictEntity::getContext());
        $this->logger->nodeStartPoint($this->trackWalker->currentNode());

        array_push($this->algIdStack, $this->trackWalker->algorithmId());
    }

    public function getAlgorithm() {
        return $this->algorithm;
    }

    public function getUID() {
        return $this->UID;
    }

    protected function getNewUID(DSN $dsn) {
        $uid = 0;

        if (Config::$LOCAL_DB) {

            $SQLite = new \SQLite3(Config::DB_FILE_DIR.Config::UID_FILE_NAME, SQLITE3_OPEN_READWRITE);
            $SQLite->busyTimeout(5000);
            $SQLite->enableExceptions(true);

            $SQLite->query("INSERT INTO srv_uid (uid) VALUES(null);");

            $resSelect = $SQLite->query("SELECT last_insert_rowid() as uid;");
            $row = $resSelect->fetchArray(SQLITE3_ASSOC);
            $resSelect->finalize();

            $SQLite->close();

            $uid = (int)$row['uid'];

        } else {
            $dbAdapter = Adapter::create($dsn::DRIVER, $dsn);

            $st = $dbAdapter->execute("select nextval('seq_algorithm_run_uid'::regclass) as uid;");
            $row = $st->fetchRow();

            $uid = (int)$row['uid'];
        }

        if (!$uid)
            throw new \Exception("Algorithm\\Logger: can't get UID");

        return $uid;
    }

    /**
     * @return \Model\Algorithm\Error
     */
    public function getError() {
        return $this->error;
    }

    public function getResult() {
        return $this->result;
    }

    public function getConclusionStorage() {
        return $this->conclusionStorage;
    }



    /**
     * @return \Model\Conclusion
     */
    public function getConclusion(PT\ConclusionId $conclusionId = null) {

        if (!is_null($conclusionId))
            $conclusion = isset($this->result->conclusions[$conclusionId->val()])?$this->result->conclusions[$conclusionId->val()]:null;
        else
            $conclusion = $this->result->conclusion;


        return $conclusion;
    }


    public function isDone() {
        return $this->isDone;
    }

    public function currentInputNode() {
        return $this->trackWalker->currentNode();
    }


    /**
     * @return Node|Node\Algorithm|Node\EndPoint|Node\Info|Node\Question|Node\Risk|Node\StartPoint|null
     */

    public function nextInputNode($stopOnEndPoint = false) {
        try {

            $this->trackWalker->nextNode();

            while (!$this->isDone) {

                $prevNode = $this->trackWalker->previousNode();

                if (is_a($prevNode, '\Model\Algorithm\Node\Question'))
                    $this->addGrantedDocuments($prevNode);

                elseif (is_a($prevNode, '\Model\Algorithm\Node\Action')) {
                    $prevNode->action->run();
                    //if (!$prevNode->action->run())
                        //throw new \Model\Exception\Action("", \Model\Exception\Action::RUN_FAILED);
                }

                $node = $this->trackWalker->currentNode();
                $node->scrGroup = $this->screenGroup;

                if (is_a($node, '\Model\Algorithm\Node\Question')) {
                    /** @var \Model\Algorithm\Node\Question $node */
                    $this->logger->nodeQuestion($node);

                    // Объект может не существовать в контексте. В таком случае мы его пропускаем.
                    if ($this->nodeQuestion($node)) {
                        $this->nodeSequence[] = $node;
                        return $node;
                    }

                } elseif (is_a($node, '\Model\Algorithm\Node\Info')) {
                    /** @var \Model\Algorithm\Node\Info $node */
                    $this->logger->nodeInfo($node);

                    // Объект может не существовать в контексте. В таком случае мы его пропускаем.
                    if ($this->nodeInfo($node)) {
                        $this->nodeSequence[] = $node;
                        return $node;
                    }

                } elseif (is_a($node, '\Model\Algorithm\Node\StartPoint')) {
                    /** @var \Model\Algorithm\Node\StartPoint $node */
                    $this->logger->nodeStartPoint($node);
                    $this->nodeStartPoint();

                } elseif (is_a($node, '\Model\Algorithm\Node\EndPoint')) {
                    /** @var \Model\Algorithm\Node\EndPoint $node */
                    $this->logger->nodeEndPoint($node);
                    $this->nodeSequence[] = $node;
                    $this->nodeEndPoint($node);

                    if ($stopOnEndPoint)
                        return null;

                } elseif (is_a($node, '\Model\Algorithm\Node\Risk')) {
                    /** @var \Model\Algorithm\Node\Risk $node */
                    $this->logger->nodeRisk($node);
                    $this->nodeSequence[] = $node;
                    $this->nodeRisk($node);

                } elseif (is_a($node, '\Model\Algorithm\Node\Algorithm')) {
                    /** @var \Model\Algorithm\Node\Algorithm $node */
                    $this->logger->nodeAlgorithm($node);
                    $this->nodeSequence[] = $node;
                    $this->nodeAlgorithm($node);

                } elseif (is_a($node, '\Model\Algorithm\Node\QuestionEnd')) {
                    /** @var \Model\Algorithm\Node\QuestionEnd $node */
                    $this->logger->nodeQuestionEnd($node);
                    $this->nodeQuestionEnd($node);

                } elseif (is_a($node, '\Model\Algorithm\Node\Conclusion')) {
                    /** @var \Model\Algorithm\Node\Conclusion $node */
                    $this->logger->nodeConclusion($node);

                    // Объект может не существовать в контексте. В таком случае мы его пропускаем.
                    if ($this->nodeConclusion($node)) {
                        $this->nodeSequence[] = $node;
                    }

                } elseif (is_a($node, '\Model\Algorithm\Node\Expression')) {
                    /** @var \Model\Algorithm\Node\Expression $node */
                    $this->logger->nodeExpression($node);
                    $this->nodeSequence[] = $node;
                    $this->nodeExpression($node);

                } elseif (is_a($node, '\Model\Algorithm\Node\Action')) {
                    /** @var \Model\Algorithm\Node\Action $node */
                    $this->logger->nodeAction($node);
                    $this->nodeSequence[] = $node;
                    $this->nodeAction($node);

                    return $node;

                } elseif (is_a($node, '\Model\Algorithm\Node\Message')) {
                    /** @var \Model\Algorithm\Node\Message $node */
                    $this->logger->nodeMessage($node);
                    $this->nodeSequence[] = $node;
                    $this->nodeMessage($node);

                } else {
                    throw new \Model\Exception\Executor("class: ".get_class($node), \Model\Exception\Executor::UNKNOWN_NODE_CLASS);
                }
            }
        } catch (\Exception $e) {
            $node = new \Model\Algorithm\Node\Error($e);
            $this->logger->nodeError($node);

            array_pop($this->nodeSequence);
            $this->nodeSequence[] = $node;

            return $node;
        }

        return null;
    }


    public function runAlgorithm($answers, $infoData, $uniqueInputs = false, $actions = []) {

        $inputs = [];
        $screeGroup = new ScreenGroup();

        do {

            try {
                $stop = $screeGroup->stopOnEndPoint();
                $node = $this->nextInputNode($stop);

                if (is_null($node))
                    break;

                $screeGroup->nodeGroup($node->scrGroup);


                if (is_a($node, '\Model\Algorithm\Node\Question')) {
                    /** @var \Model\Algorithm\Node\Question $node */

                    $objectId = $node->question->extId;

                    if (isset($answers[$objectId])) {
                        $node->setAnswer($answers[$objectId]);
                    } elseif ($screeGroup->inGroup()) {
                        $node->setDefaultAnswer();
                    } else
                        $this->answerSubstitutor->answer($node);


                    $screeGroup->question(isset($answers[$objectId]));

                    if ($uniqueInputs) {
                        $key = 'q' . $objectId;

                        if (!isset($inputs[$key]))
                            $inputs[$key] = array('data' => $node->question->render(), 'type' => 'question');
                    } else
                        $inputs[] = array('data' => $node->question->render(), 'type' => 'question');


                    if (!$screeGroup->inGroup() and !$node->hasAnswer())
                        break;

                    $this->logger->setAnswer($node);


                } elseif (is_a($node, '\Model\Algorithm\Node\Info')) {
                    /** @var \Model\Algorithm\Node\Info $node */

                    $objectId = $node->info->extId;

                    if (isset($infoData[$objectId])) {
                        $node->setData($infoData[$objectId]);
                    } else
                        $this->answerSubstitutor->infoData($node);

                    $screeGroup->info($node->dataReady());

                    if ($uniqueInputs) {
                        $key = 'i' . $objectId;

                        if (!isset($inputs[$key]))
                            $inputs[$key] = array('data' => $node->info->render(), 'type' => 'infoData');
                    } else
                        $inputs[] = array('data' => $node->info->render(), 'type' => 'infoData');


                    if (!$screeGroup->inGroup() and !$node->dataReady())
                        break;

                    $this->logger->setInfoData($node);



                } elseif (is_a($node, '\Model\Algorithm\Node\Action')) {
                    /** @var \Model\Algorithm\Node\Action $node */

                    $objectId = $node->action->extId;

                    if (isset($actions[$objectId]))
                        $node->action->setDone($actions[$objectId]);

                    // $this->logger->setAction($node);

                } elseif (is_a($node, '\Model\Algorithm\Node\Error')) {
                    $this->logger->error($node->error);
                    $this->error = $node->error;
                    break;
                }


            } catch (\Exception $e) {
                $node = new \Model\Algorithm\Node\Error($e);
                $this->nodeSequence[] = $node;
                $this->logger->nodeError($node);
                $this->error = $node->error;
                break;
            }

        } while (!$this->isDone());


        $this->result->assignTemplate();

        if ($this->isDone())
            $this->logger->complete();

        return $inputs;
    }


    protected function addGrantedDocuments(\Model\Algorithm\Node\Question $node) {
        /** @var \Model\Question\Document $question */
        $question = $node->question;

        if (!$question instanceof \Model\Question\Document)
            return;

        if ($question->documentId>0) {
            $document = $question->getGrntDocument();

            if (!is_null($document)) {
                $this->result->addGrantedDocument($document);

                $documentGeneral = $question->getGrntDocumentGeneral();
                $documentGeneral->setAlgorithmIds($this->algIdStack);

                $this->result->addGrantedDocumentGeneral($documentGeneral);
            }

        } elseif ($question->documentGeneralId>0) {
            $documentGeneral = $question->getGrntDocumentGeneral();

            if (!is_null($documentGeneral)) {
                $documentGeneral->setAlgorithmIds($this->algIdStack);

                $this->result->addGrantedDocumentGeneral($documentGeneral);
            }
        }
    }


    public function getNodeSequence($uniqueObjects = false, $addNodes = []) {
        $nodes = array();

        $questionIds = array();
        $infoIds = array();
        $riskIds = array();
        $conclusionIds = array();

        $addExpression = in_array(Node::SRV_TYPE_EXPRESSION, $addNodes)?true:false;
        $addAction = in_array(Node::SRV_TYPE_ACTION, $addNodes)?true:false;
        $addAlgorithm = in_array(Node::SRV_TYPE_ALGORITHM, $addNodes)?true:false;
        $addEndPoint = in_array(Node::SRV_TYPE_END_POINT, $addNodes)?true:false;


        foreach ($this->nodeSequence as $node) {
            if (is_a($node, '\Model\Algorithm\Node\Question')) {
                /**  @var \Model\Algorithm\Node\Question $node */

                if ($uniqueObjects and isset($questionIds[$node->question->extId]))
                    continue;

                $answers = [];

                foreach ($node->question->answers->toArray(['id', 'header']) as $answer) {
                    $answers[$answer['id']] = $answer['header'];
                }

                $nodes[] = array(
                    'data' => $node->question->render($node->scrGroup),
                    'type' => Node::SRV_TYPE_QUESTION,
                    'question_id' => $node->question->extId,
                    'hidden' => $node->hidden,
                    'algIds' => $node->question->algIds,
                    'answer_id' => $node->question->isEmpty('answerId')?null:$node->question->answerId,
                    'answers' => $answers,
                    'loop_id' => $node->loopId,
                    'scr_group' => $node->scrGroup
                );

                $questionIds[$node->question->extId] = 1;

            } elseif (is_a($node, '\Model\Algorithm\Node\Info')) {
                /**  @var \Model\Algorithm\Node\Info $node */

                if ($uniqueObjects and isset($infoIds[$node->info->extId]))
                    continue;

                if (is_a($node->info, "\Model\Info\File"))
                    $nodes[] = [
                        'data' => $node->info->render(),
                        'type' => Node::SRV_TYPE_FILE,
                        'info_id' => $node->info->extId,
                        'hidden' => $node->hidden,
                        'alert' => $node->info->getAlert(),
                        'file_web_id' => $node->info->isEmpty('fileWebID')?null:$node->info->fileWebID,
                        'file_name' => $node->info->isEmpty('fileName')?null:$node->info->fileName,
                        'loop_id' => $node->loopId,
                        'scr_group' => $node->scrGroup
                    ];
                elseif (is_a($node->info, "\Model\Info\Hidden"))
                    $nodes[] = [
                        'data' => $node->info->render(),
                        'type' => Node::SRV_TYPE_HIDDEN,
                        'info_id' => $node->info->extId,
                        'loop_id' => $node->loopId,
                        'var_name' => $node->info->varName,
                        'var_source' => $node->info->varSource,
                        'scr_group' => $node->scrGroup
                    ];

                else
                    $nodes[] = [
                        'data' => $node->info->render($node->scrGroup),
                        'type' => Node::SRV_TYPE_INFO,
                        'info_id' => $node->info->extId,
                        'hidden' => $node->hidden,
                        'loop_id' => $node->loopId,
                        'scr_group' => $node->scrGroup
                    ];

                $infoIds[$node->info->extId] = 1;

            } elseif (is_a($node, '\Model\Algorithm\Node\Risk')) {
                if ($uniqueObjects and isset($riskIds[$node->risk->extId]))
                    continue;

                if ($node->risk->riskGeneralId>0) {
                    $nodes[] = array('data' => $node->risk->render(), 'risk_data' => isset($this->result->risksGeneral[$node->risk->riskGeneralExtId])?$this->result->risksGeneral[$node->risk->riskGeneralExtId]->render():'', 'type' => Node::SRV_TYPE_RISK, 'risk_id' => $node->risk->extId, 'loop_id' => $node->loopId, 'scr_group' => $node->scrGroup);
                } else
                    $nodes[] = array('data' => $node->risk->render(), 'type' => Node::SRV_TYPE_WARNING, 'risk_id' => $node->risk->extId, 'loop_id' => $node->loopId, 'scr_group' => $node->scrGroup, 'header' => $node->risk->header);

                $riskIds[$node->risk->extId] = 1;

            } elseif (is_a($node, '\Model\Algorithm\Node\Message')) {
                /**  @var \Model\Algorithm\Node\Message $node */
                $nodes[] = [
                    'data' => $node->message->render(),
                    'type' => Node::SRV_TYPE_MESSAGE,
                    'message_id' => $node->message->extId,
                    'hidden' => $node->message->hidden,
                    'loop_id' => $node->loopId,
                    'scr_group' => $node->scrGroup];

            } elseif (is_a($node, '\Model\Algorithm\Node\Conclusion')) {
                if ($uniqueObjects and isset($conclusionIds[$node->conclusion->extId]))
                    continue;

                $nodes[] = array('data' => array('id' => $node->conclusion->extId, 'header' => $node->conclusion->header), 'type' => Node::SRV_TYPE_CONCLUSION, 'conclusion_id' => $node->conclusion->extId, 'conclusion_type' => $node->conclusion->type, 'loop_id' => $node->loopId, 'scr_group' => $node->scrGroup);
                $conclusionIds[$node->conclusion->extId] = 1;

            } elseif (is_a($node, '\Model\Algorithm\Node\Error')) {
                $nodes[] = array('type' => Node::SRV_TYPE_ERROR, 'scr_group' => $node->scrGroup);

            } elseif ($addExpression and is_a($node, '\Model\Algorithm\Node\Expression')) {
                /**  @var \Model\Algorithm\Node\Expression $node */
                $nodes[] = array('type' => Node::SRV_TYPE_EXPRESSION, 'value' => $node->value , 'expression_id' => $node->expression->extId, 'loop_id' => $node->loopId, 'scr_group' => $node->scrGroup);

            } elseif ($addAction and is_a($node, '\Model\Algorithm\Node\Action')) {
                /**  @var \Model\Algorithm\Node\Action $node */
                $nodes[] = array('type' => Node::SRV_TYPE_ACTION, 'action_id' => $node->action->extId, 'done' => $node->action->done, 'message' => $node->action->message, 'loop_id' => $node->loopId, 'scr_group' => $node->scrGroup);

            } elseif ($addEndPoint and is_a($node, '\Model\Algorithm\Node\EndPoint')) {
                /**  @var \Model\Algorithm\Node\EndPoint $node */
                $nodes[] = array('type' => Node::SRV_TYPE_END_POINT, 'header' => isset($node->comment)?$node->comment:'', 'loop_id' => $node->loopId, 'scr_group' => $node->scrGroup);

            } elseif ($addAlgorithm and is_a($node, '\Model\Algorithm\Node\Algorithm')) {
                /**  @var \Model\Algorithm\Node\Algorithm $node */
                $nodes[] = array('type' => Node::SRV_TYPE_ALGORITHM, 'header' => $node->alg->header, 'algorithm_id' => $node->alg->extId, 'loop_id' => $node->loopId, 'scr_group' => $node->scrGroup);

            }

        }

        return $nodes;
    }


    public function getObjectSequence() {
        $objects = array();

        foreach ($this->nodeSequence as $node) {
            if (is_a($node, '\Model\Algorithm\Node\Question')) {
                /** @var \Model\Algorithm\Node\Question $node */
                $objects[] = $node->question;

            } elseif (is_a($node, '\Model\Algorithm\Node\Info')) {
                /** @var \Model\Algorithm\Node\Info $node */
                $objects[] = $node->info;

            } elseif (is_a($node, '\Model\Algorithm\Node\Risk')) {
                /** @var \Model\Algorithm\Node\Risk $node */
                $objects[] = $node->risk;

            } elseif (is_a($node, '\Model\Algorithm\Node\Conclusion')) {
                /** @var \Model\Algorithm\Node\Conclusion $node */
                $objects[] = $node->conclusion;
            }
        }

        return $objects;
    }



    protected function nodeStartPoint() {
        $this->trackWalker->nextNode();
    }

    protected function nodeQuestion(\Model\Algorithm\Node\Question $node) {
        /** @var \Model\Question $question */
        $this->applyContext($node);

        $question = $this->qstStorage->getById(new PT\QuestionId($node->questionId), array('contextData', 'document' => array('contextData'), 'documentGeneral' => array('contextData')), null, $node->loopId);

        if (is_null($question)) {
            $question = $this->qstStorage->getById(new PT\QuestionId($node->questionId), array('document' => array('contextData'), 'documentGeneral' => array('contextData')), null, $node->loopId);
            $node->question = $question;
            $question->setAlgorithmIds($this->algIdStack);

            $node->setAnswer($question->defaultAnswerId);

            $this->trackWalker->nextNode();

            $this->restoreContext($node);

            return false;

        }

        $question->setAlgorithmIds($this->algIdStack);
        $this->result->addQuestion($question);

        $node->question = $question;
        $this->logger->question($question);

        $this->restoreContext($node);

        return true;

    }


    protected function nodeInfo(\Model\Algorithm\Node\Info $node) {
        $info = $this->infStorage->getById(new PT\InfoId($node->infoId), array('contextData'), null, $node->loopId);

        if (is_null($info)) {
            $info = $this->infStorage->getById(new PT\InfoId($node->infoId), array(), null, $node->loopId);
            $node->info = $info;
            $info->setAlgorithmIds($this->algIdStack);

            $this->trackWalker->nextNode();

            return false;
        }

        $info->setAlgorithmIds($this->algIdStack);
        $this->result->addInfo($info);

        $node->info = $info;
        $this->logger->info($info);

        if (is_a($info, '\Model\Info\File'))
            $info->initUID($this->UID, $node->id);

        return true;
    }


    protected function nodeEndPoint(\Model\Algorithm\Node\EndPoint $node) {

        $this->screenGroup = 0;

        if (count($this->trackWalkerStack)) {
            $this->trackWalker = array_pop($this->trackWalkerStack);
            $this->answerSubstitutor = array_pop($this->answerSubstitutorStack);
            array_pop($this->algIdStack);

            /** @var \Model\Algorithm\Node\Algorithm $nodeAlg */
            $nodeAlg = $this->trackWalker->currentNode();
            $nodeAlg->setEndPoint($node->id);

            $this->trackWalker->nextNode();

        } else
            $this->isDone = true;
    }


    protected function nodeQuestionEnd(\Model\Algorithm\Node\QuestionEnd $node) {
        $this->trackWalker->goToNode($node->questionNode->nextNode());
    }

    protected function nodeRisk(\Model\Algorithm\Node\Risk $node) {
        $risk = $this->riskStorage->getById(new PT\RiskId($node->riskId), array('contextData', 'document' => array('contextData'), 'documentGeneral' => array('contextData')), null, $node->loopId);

        if (!is_null($risk)) {
            $risk->setAlgorithmIds($this->algIdStack);
            $this->result->addRisk($risk);
            $node->risk = $risk;
            $this->logger->riskReason($risk);

            if ($risk->riskGeneralId>0 and !isset($this->result->risksGeneral[$risk->riskGeneralExtId])) {
                $riskGeneral  = $this->riskGeneralStorage->getById(new PT\RiskGeneralId($risk->riskGeneralId), array('contextData'), null, $node->loopId);

                if (!is_null($riskGeneral)) {
                    $riskGeneral->setAlgorithmIds($this->algIdStack);
                    $this->result->addRiskGeneral($riskGeneral);
                    $this->logger->risk($riskGeneral);
                }
            }

            $this->addWrongDocuments($risk);
        }

        $this->trackWalker->nextNode();
    }



    protected function addWrongDocuments(\Model\Risk $riskReason) {

        if ($riskReason->documentGeneralId>0 and !$riskReason->isEmpty('documentGeneral')
            and !isset($this->result->wrongDocumentsGeneral[$riskReason->documentGeneralExtId])) {

            $riskReason->documentGeneral->properties = new \Model\Collection\Document();
            $riskReason->documentGeneral->setAlgorithmIds($this->algIdStack);

            $this->result->addWrongDocumentGeneral($riskReason->documentGeneral);
        }

        if ($riskReason->documentId>0  and !$riskReason->isEmpty('document')) {
            $this->result->addWrongDocument($riskReason->document);

            if (isset($this->result->wrongDocumentsGeneral[$riskReason->documentGeneralExtId]))
                $riskReason->document->setAlgorithmIds($this->algIdStack);
                $this->result->wrongDocumentsGeneral[$riskReason->documentGeneralExtId]->properties[$riskReason->documentId] = $riskReason->document;
        }
    }



    protected function nodeConclusion(\Model\Algorithm\Node\Conclusion $node) {
        $conclusion = $this->conclusionStorage->getById(new PT\ConclusionId($node->conclusionId), ['contextData'], null, $node->loopId);

        if (is_null($conclusion)) {
            $conclusion = $this->conclusionStorage->getById(new PT\ConclusionId($node->conclusionId), [], null, $node->loopId);
            $conclusion->setAlgorithmIds($this->algIdStack);

            $node->conclusion = $conclusion;
            $this->trackWalker->nextNode();

            return false;
        }

        $conclusion->setAlgorithmIds($this->algIdStack);

        $node->conclusion = $conclusion;
        $this->result->addConclusion($node->conclusion);
        $this->logger->conclusion($node->conclusion);

        $this->trackWalker->nextNode();

        return true;
    }


    protected function nodeExpression(\Model\Algorithm\Node\Expression $node) {

        $extId = \Model\DictEntity::extId($node->expressionId, $node->loopId);


        if (isset($this->result->variables[$extId]))
            $node->expression = $this->result->variables[$extId];
        else
            $node->expression = $this->expressionStorage->getById(new PT\ExpressionId($node->expressionId), array('contextData'), null, $node->loopId);


        $this->result->addExpression($node->expression);


        $this->initExpression($node->expression, $node->comment);
        $node->expression->setAlgorithmIds($this->algIdStack);

        $this->logger->expression($node->expression);

        if (!is_a($node->expression, '\Model\Expression\Condition') ) {
            $node->value = $node->expression->textValue;
        }

        $this->trackWalker->nextNode();
    }


    protected function nodeAction(\Model\Algorithm\Node\Action $node) {
        $node->action = $this->actionStorage->getById(new PT\ActionId($node->actionId), [], null, $node->loopId);

        $this->result->addAction($node->action);
        $node->action->setExecutor($this);

        // $this->initAction($node->action);
        $this->logger->action($node->action);

        // $this->trackWalker->nextNode();
    }


    protected function nodeMessage(\Model\Algorithm\Node\Message $node) {
        $node->message = $this->messageStorage->getById(new PT\MessageId($node->messageId), ['contextData'], null, $node->loopId);

        if (is_a($node->message, '\Model\Message\Chart')) {
            $node->message->setInfoCollection($this->result->info);
            $node->message->setExpressionCollection($this->result->expressions);
        }


        $this->result->addMessage($node->message);

        $this->logger->message($node->message);
        $this->trackWalker->nextNode();
    }



    protected function nodeAlgorithm(\Model\Algorithm\Node\Algorithm $node) {
        $algorithm = $this->algorithmStorage->getById(new PT\AlgorithmId($node->algId), array(), null, $node->loopId);

        if ($algorithm->screenGroup)
            $this->screenGroup = $algorithm->id;

        $node->alg = $algorithm;
        $this->logger->algorithm($algorithm);

        array_push($this->trackWalkerStack, $this->trackWalker);
        array_push($this->answerSubstitutorStack, $this->answerSubstitutor);

        $this->answerSubstitutor = new AnswerSubstitutor($algorithm->infoMap, $algorithm->questionMap, \Model\DictEntity::getContext(), $this->result, $this->answerSubstitutor);

        $algorithmTree = $this->createAlgorithmTree($node->algId, $this->dsn);
        $this->trackWalker = new TrackWalker($algorithmTree, $node->loopId);

        array_push($this->algIdStack, $this->trackWalker->algorithmId());
    }


    protected function initExpression(\Model\Expression $expression, $comment) {

        $expression->setInfoCollection($this->result->info);
        $expression->setExpressionCollection($this->result->expressions);

        if (is_a($expression, '\Model\Expression\Variable')) {
            /** @var \Model\Expression\Variable $expression */
            $expression->setFormula($comment);
        }

        $expression->calculate();
    }


    protected function applyContext(\Model\Algorithm\Node $node) {
        if ($node->contextId>0) {
            DictEntity::setContext($node->contextId);
            array_push($this->contextIdStack, $node->contextId);
        }
    }

    protected function restoreContext(\Model\Algorithm\Node $node) {
        if ($node->contextId>0) {
            DictEntity::setContext(array_pop($this->contextIdStack));
        }
    }

}


class ScreenGroup {

    public $group = 0;

    protected $hasQuestion = false;
    protected $hasInfo = false;

    protected $questionAllAnswers = true;
    protected $infoAllAnswers = true;

    public function inGroup() {
        return $this->group>0?true:false;
    }

    public function nodeGroup($group) {
        if ($group>0) {
            if ($group !== $this->group)
                $this->group = $group;
        } else {
            $this->group = 0;
        }
    }

    public function stopOnEndPoint() {
        if ($this->group>0)
            if ($this->hasInfo) {
                return $this->infoAllAnswers?false:true;
            } elseif ($this->hasQuestion) {
                return $this->questionAllAnswers?false:true;
            } else
                return true;
        else
            return false;
    }

    public function question($hasAnswer) {
        $this->hasQuestion = true;

        if (!$hasAnswer)
            $this->questionAllAnswers = false;
    }

    public function info($dataReady) {
        $this->hasInfo = true;

        if (!$dataReady)
            $this->infoAllAnswers = false;
    }

}