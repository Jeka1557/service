<?php

namespace Model\Algorithm;

use Model\Collection;
use Infr\Template;
use PT;


class Result {

    public $riskLevel = 0;

    /**
     * @var Collection\Info
     */
    public $info;

    /**
     * @var Collection\Info
     */
    public $infoText;

    /**
     * @var Collection\Info
     */
    public $infoFiles;

    /**
     * @var Collection\Question
     */
    public $questions;

    /**
     * @var Collection\Expression
     */
    public $expressions;

    /**
     * @var Collection\Expression
     */
    public $variables = [];



    /**
     * @var Collection\Risk
     */
    public $warnings;


    /**
     * @var Collection\Message
     */
    public $messages;


    /**
     * @var Collection\Risk
     */
    public $risks;

    /**
     * @var Collection\Risk
     */
    public $riskReasons;

    /**
     * @var Collection\RiskGeneral
     */
    public $risksGeneral;



    /**
     * @var \Model\Conclusion
     */
    public $conclusion;

    /**
     * @var Collection\Conclusion
     */
    public $conclusions;


    /**
     * @var Collection\Action
     */
    public $actions;


    /**
     * @var \Model\Collection\Document
     */
    public $grantedDocuments;
    /**
     * @var \Model\Collection\DocumentGeneral
     */
    public $grantedDocumentsGeneral;

    /**
     * @var \Model\Collection\Document
     */
    public $wrongDocuments;

    /**
     * @var \Model\Collection\DocumentGeneral
     */
    public $wrongDocumentsGeneral;




    public function __construct()
    {
        $this->info = new Collection\Info();
        $this->infoFiles = new Collection\Info();

        $this->questions = new Collection\Question();

        $this->expressions = new Collection\Expression();
        $this->variables = new Collection\Expression();

        $this->warnings = new Collection\Risk();
        $this->riskReasons = new Collection\Risk();

        $this->messages = new Collection\Message();

        $this->risks = new Collection\Risk();
        $this->risksGeneral = new Collection\RiskGeneral();

        $this->conclusions = new Collection\Conclusion();
        $this->actions = new Collection\Action();

        $this->grantedDocuments = new Collection\Document();
        $this->grantedDocumentsGeneral = new Collection\DocumentGeneral();

        $this->wrongDocuments = new Collection\Document();
        $this->wrongDocumentsGeneral = new Collection\DocumentGeneral();
    }


    public function addInfo(\Model\Info $info) {
        $this->info[$info->extId] = $info;

        if (is_a($info, '\Model\Info\File'))
            $this->infoFiles[$info->extId] = $info;
        else
            $this->infoText[$info->extId] = $info;
    }


    public function addQuestion(\Model\Question $question) {
        $this->questions[$question->extId] = $question;
    }


    public function addExpression(\Model\Expression $expression) {

        if (is_a($expression, '\Model\Expression\Variable') ) {
            $this->variables[$expression->extId] = $expression;
        }

        $this->expressions[$expression->extId] = $expression;
    }


    public function addRisk(\Model\Risk $risk) {
        $this->risks[$risk->extId] = $risk;

        if ($risk->riskGeneralId>0)
            $this->riskReasons[$risk->extId] = $risk;
        else
            $this->warnings[$risk->extId] = $risk;
    }

    public function addRiskGeneral(\Model\RiskGeneral $riskGeneral) {
        $this->risksGeneral[$riskGeneral->extId] = $riskGeneral;

        if ($this->riskLevel==0)
            $this->riskLevel = $riskGeneral->level;
        elseif ($riskGeneral->level<$this->riskLevel)
            $this->riskLevel = $riskGeneral->level;
    }

    public function addConclusion(\Model\Conclusion $conclusion) {
        $this->conclusions[$conclusion->extId] = $conclusion;
        $this->conclusion = $conclusion;
    }


    public function addAction(\Model\Action $action) {
        $this->actions[$action->extId] = $action;
    }

    public function addMessage(\Model\Message $message) {
        $this->messages[$message->extId] = $message;
    }


    public function addGrantedDocument(\Model\Document $document) {
        $this->grantedDocuments[$document->extId] = $document;
    }

    public function addGrantedDocumentGeneral(\Model\DocumentGeneral $documentGeneral) {
        $this->grantedDocumentsGeneral[$documentGeneral->extId] = $documentGeneral;
    }


    public function addWrongDocument(\Model\Document $document) {
        $this->wrongDocuments[$document->extId] = $document;
    }

    public function addWrongDocumentGeneral(\Model\DocumentGeneral $documentGeneral) {
        $this->wrongDocumentsGeneral[$documentGeneral->extId] = $documentGeneral;
    }


    public function assignTemplate() {
        Template::setInfo($this->info->toArray());
        Template::setQuestion($this->questions->toArray());
        Template::setExpression($this->expressions->toArray());

        Template::setWarning($this->warnings->toArray());
        Template::setRisk($this->risks->toArray());
        Template::setRiskReason($this->riskReasons->toArray());
        Template::setRiskGeneral($this->getRiskGeneral()->toArray());


        Template::setDocumentGeneral($this->grantedDocumentsGeneral->toArray());
        Template::setWrongDocumentGeneral($this->wrongDocumentsGeneral->toArray());
    }


    public function getRiskGeneral() {
        foreach ($this->risksGeneral as $risk) {
            $risk->reasons = new \Model\Collection\Risk();

            foreach ($this->risks as $reason) {
                if ($reason->riskGeneralExtId==$risk->extId)
                    $risk->reasons[$reason->id] = $reason;
            }
        }

        return $this->risksGeneral;
    }


    public function getRiskGeneralCnt() {
        return count($this->risksGeneral);
    }

    public function getRiskCnt() {
        return count($this->risks);
    }

    public function getGrantedDocumentGeneralCnt() {
        return count($this->grantedDocumentsGeneral);
    }

    public function getWrongDocumentGeneralCnt() {
        return count($this->wrongDocumentsGeneral);
    }

    public function getWrongDocumentCnt() {
        return count($this->wrongDocuments);
    }



    /**
     * @return array
     */
    public function getActionsDoneHash() {
        $result = [];

        foreach ($this->actions as $action) {
            /** @var \Model\Action $action */

            if ($action->doneHash)
                $result[$action->extId] = $action->doneHash;
        }

        return $result;
    }


    public function getRiskLevel() {
        return $this->riskLevel>0?(new PT\RiskLevel($this->riskLevel)):null;
    }


    /**
     * @return \Model\Conclusion
     */
    public function getConclusion(PT\ConclusionId $conclusionId = null) {

        if (!is_null($conclusionId))
            $conclusion = isset($this->conclusions[$conclusionId->val()])?$this->conclusions[$conclusionId->val()]:null;
        else
            $conclusion = $this->conclusion;


        return $conclusion;
    }


    /**
     * @return \Model\Info
     */
    public function getInfo(PT\InfoId $infoId) {

        return isset($this->info[$infoId->val()])?$this->info[$infoId->val()]:null;
    }


    /**
     * @return \Model\Info
     */
    public function getFiles() {
        $result = [];

        foreach ($this->infoFiles as $file) {
            /** @var \Model\Info\File $file */

            if ($file->hasValue)
                $result[$file->extId] = $file;
        }

        return $result;
    }
}