<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 23.10.2016
 * Time: 18:23
 */


namespace Model\Exception;

use PT;


class Expression extends \Exception {

    const ARGUMENT_IS_NOT_SET = 1;

}


class Question extends \Exception {

    const DEFAULT_ANSWER_IS_NOT_SET = 1;
    const DOCUMENT_IS_NOT_INITIALIZED = 2;
    const DOCUMENT_GENERAL_IS_NOT_INITIALIZED = 3;
    const ANSWER_NOT_FOUND = 4;

}


class Executor extends \Exception {

    const UNKNOWN_NODE_CLASS = 1;
    const ALGORITHM_LOOP_FOUND = 2;

}


class ExpressionAnswer extends \Exception {

    const CONDITION_EXPRESSION_IS_INVALID = 1;
    const FORMULA_EXPRESSION_IS_INVALID = 2;
    const CONDITION_ARGUMENT_NOT_DEFINED = 3;
    const FORMULA_ARGUMENT_NOT_DEFINED = 4;

    public $answerId;
    public $expressionId;
    public $formula;
    public $params;
    public $argumentNum;

    public function __construct($code, $expressionId, $answerId, $formula, $params, $argumentNum = 0) {

        $this->answerId = $answerId;
        $this->expressionId = $expressionId;
        $this->formula = $formula;
        $this->params = $params;
        $this->argumentNum = $argumentNum;

        parent::__construct("", $code);
    }

}


class Action extends \Exception {

    const RUN_FAILED = 1;

}



class NotExistsInContextsException extends \Exception {

}

class ContextNotFoundException extends \Exception {

    /**
     * @var PT\EntityType
     */
    public $entityType;
    public $entityId;
    public $contextId;

    public function __construct(PT\EntityType $entityType, $entityId, $contextId) {
        parent::__construct("Entity ".$entityType->name()." id: {$entityId} context {$contextId} not found");

        $this->entityType = $entityType;
        $this->entityId = $entityId;
        $this->contextId = $contextId;
    }

}