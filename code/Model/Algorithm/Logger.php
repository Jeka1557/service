<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 15.10.2016
 * Time: 23:12
 */


namespace Model\Algorithm;
use PT;
use Lib\Infr\DSN;
use Infr\ErrorLogger;

class Logger {

    const DUMP_FREQUENCY = 10;
    const USER_DATA_HASH = '97fee2c5bf04cfa77136322a7ce8f501';

    protected $fHandle;
    protected $lines = array();
    protected $step = 1;
    protected $error = false;
    protected $complete = false;

    protected $uid;


    static protected $enable = false;

    static protected $userData = false;


    public static function enable($yes = true) {
        self::$enable = $yes;
    }

    public static function userData($key) {
        if (md5($key.'_UserDataSalt')==self::USER_DATA_HASH)
            self::$userData = true;
        else
            self::$userData = false;
    }

    public function __construct($appRoot, DSN $dsn, $uid)
    {
        $this->uid = $uid;
        $this->fHandle = $this->openLogFile($appRoot, $this->uid);
    }

    public function __destruct()
    {
        if (!self::$enable)
            return;

        $this->dump();
        $this->result();
        if ($this->fHandle)
            fclose($this->fHandle);
    }

    public function complete() {
        $this->complete = true;
        $this->log("\nALGORITHM COMPLETE\n");
    }

    public function nodeError(\Model\Algorithm\Node\Error $error) {
        ErrorLogger::nodeError($error->error->getMessage(), $this->uid);

        $this->error = true;
        $this->log("\nALGORITHM ERROR");
    }

    public function error(\Model\Algorithm\Error $error) {
        $lines = explode("\n", $error->getMessage());

        foreach ($lines as $line) {
            $this->log(" {$line}");
        }
    }

    public function riskReason(\Model\Risk $risk) {
        $this->log(" Risk reason (id: {$risk->id})");
        $this->log(" Header: {$risk->header} ");
    }

    public function message(\Model\Message $message) {
        $this->log(" Message (id: {$message->id})");
        $this->log(" Header: {$message->header} ");
    }

    public function risk(\Model\RiskGeneral $riskGeneral) {
        $this->log(" Risk (id: {$riskGeneral->id})");
        $this->log(" Header: {$riskGeneral->header} ");
    }

    public function question(\Model\Question $question) {
        $this->log(" Question (id: {$question->id})");
        $this->log(" Type: ".get_class($question));
        $this->log(" Header: {$question->header} ");
    }

    public function expression(\Model\Expression $expression) {
        $this->log(" Expression (id: {$expression->id})");
        $this->log(" Type: ".get_class($expression));
        $this->log(" Header: {$expression->header} ");

        if (is_a($expression,'\Model\Expression\Condition'))
            $this->log(" AnswerId: {$expression->answerId}");
        else
            $this->log(" Value: {$expression->textValue}");
    }

    public function info(\Model\Info $info) {
        $this->log(" Info (id: {$info->id})");
        $this->log(" Type: ".get_class($info));
        $this->log(" Header: {$info->header} ");
    }

    public function conclusion(\Model\Conclusion $conclusion) {
        $this->log(" Conclusion (id: {$conclusion->id})");
        $this->log(" Header: {$conclusion->header} ");
    }

    public function action(\Model\Action $action) {
        $this->log(" Action (id: {$action->id})");
        $this->log(" Header: {$action->header} ");
    }

    public function algorithm(\Model\Algorithm $algorithm) {
        $this->log(" Algorithm (id: {$algorithm->id})");
        $this->log(" Header: {$algorithm->header} ");
    }

    public function setAnswer(\Model\Algorithm\Node\Question $node) {
        $answerId = $node->getAnswer();
        $endpoints = $node->getEndpoints();

        if (is_array($answerId)) {
            foreach ($answerId as $k=>$id) {
                $this->log("->Answer {$k}: \"{$id}\" (".(isset($endpoints[$id])?$endpoints[$id]:'!!End point not found!!').")");
            }
        } else {
            $this->log("->Answer: \"{$answerId}\" (".(isset($endpoints[$answerId])?$endpoints[$answerId]:'!!End point not found!!').")");
        }

    }

    public function setInfoData(\Model\Algorithm\Node\Info $node) {

        if (self::$userData) {
            $data = $node->getData();

            $data = var_export($data, true);
            $data = str_replace("\n", '', $data);
            $data = str_replace('array (  ', 'array(', $data);

        } else {
            $data = '*****';
        }

        $this->log("->InfoData: ".$data);
    }

    public function nodeInfo(\Model\Algorithm\Node\Info $node) {
        $this->log("Node Info (NodeId: {$node->id} LoopId: {$node->loopId} InfoId: {$node->infoId})", true);
    }

    public function startAlgorithm($algorithm, $contextId) {
        $this->log("ALGORITHM START");
        $this->log(" Algorithm (id: {$algorithm->id})");
        $this->log(" Header: {$algorithm->header} ");
        $this->log(" ContextId: {$contextId} ");
    }

    public function nodeStartPoint(\Model\Algorithm\Node\StartPoint $node) {
        $this->log("Node StartPoint (NodeId: {$node->id}  LoopId: {$node->loopId})", true);
    }

    public function nodeQuestion(\Model\Algorithm\Node\Question $node) {
        $this->log("Node Question (NodeId: {$node->id}  LoopId: {$node->loopId} QuestionId: {$node->questionId})", true);
    }

    public function nodeEndPoint(\Model\Algorithm\Node\EndPoint $node) {
        $this->log("Node EndPoint (NodeId: {$node->id}  LoopId: {$node->loopId})", true);
    }

    public function nodeRisk(\Model\Algorithm\Node\Risk $node) {
        $this->log("Node Risk (NodeId: {$node->id}  LoopId: {$node->loopId} RiskId: {$node->riskId})", true);
    }

    public function nodeQuestionEnd(\Model\Algorithm\Node\QuestionEnd $node) {
        $this->log("Node QuestionEnd (NodeId: {$node->id}  LoopId: {$node->loopId} QuestionId: {$node->questionId} QuestionNodeId: {$node->questionNodeId})", true);
    }

    public function nodeAlgorithm(\Model\Algorithm\Node\Algorithm $node) {
        $this->log("Node Algorithm (NodeId: {$node->id} LoopId: {$node->loopId} AlgorithmId: {$node->algId})", true);
    }

    public function nodeConclusion(\Model\Algorithm\Node\Conclusion $node) {
        $this->log("Node Conclusion (NodeId: {$node->id} LoopId: {$node->loopId})", true);
    }

    public function nodeExpression(\Model\Algorithm\Node\Expression $node) {
        $this->log("Node Expression (NodeId: {$node->id} LoopId: {$node->loopId})", true);
    }

    public function nodeAction(\Model\Algorithm\Node\Action $node) {
        $this->log("Node Action (NodeId: {$node->id} LoopId: {$node->loopId})", true);
    }

    public function nodeMessage(\Model\Algorithm\Node\Message $node) {
        $this->log("Node Message (NodeId: {$node->id} LoopId: {$node->loopId})", true);
    }

    protected function result() {
        if (!self::$enable)
            return;

        $line = "\nResult (steps:{$this->step} complete:".($this->complete?'true':'false')." error:".($this->error?'true':'false').")";
        fwrite($this->fHandle, $line);
    }


    protected function log($line, $nextStep = false) {
        if ($nextStep) {
            $this->lines[] = "\n{$this->step}: {$line}\n";
            $this->step++;
        } else {
            $this->lines[] = "{$line}\n";
        }

        if (count($this->lines)>=self::DUMP_FREQUENCY);
            $this->dump();
    }

    protected function dump() {

        if (!self::$enable)
            return;

        foreach ($this->lines as $line) {
            fwrite($this->fHandle, $line);
        }
        $this->lines = array();
    }

    protected function openLogFile($appRoot, $uid) {

        if (!self::$enable)
            return null;

        $logDir = $appRoot.'/logs/algorithms/'.date('Y-m-d').'/';

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777);
            @chmod($logDir, 0777);
        }

        $handle = fopen("{$logDir}/{$uid}.log",'w');

        if (!$handle)
            throw new \Exception("Algorithm\\Logger: can't create log file");

        return $handle;
    }

}