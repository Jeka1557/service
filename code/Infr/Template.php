<?php

namespace Infr;

use \Lib\DOKTemplates\DOKTemplate;
use Lib\DOKTemplates\DOKTemplateCompiler;

require_once(SERVICE_ROOT . '/templ/text-functions.phtml');


class Template extends DOKTemplate {

    public static $infoBlocks = [];
    public static $infoLoopBlocks = [];
    public static $infoSet = [];
    public static $infoLoop = [];
    
    public static $questionBlocks = [];
    public static $questionLoopBlocks = [];
    public static $questionSet = [];
    public static $questionLoop = [];

    public static $expressionBlocks = [];
    public static $expressionLoopBlocks = [];
    public static $expressionSet = [];
    public static $expressionLoop = [];

    public static $warningBlocks = [];
    public static $warningLoopBlocks = [];
    public static $warningSet = [];
    public static $warningLoop = [];

    public static $riskBlocks = [];
    public static $riskLoopBlocks = [];
    public static $riskSet = [];
    public static $riskLoop = [];

    public static $riskReasonBlocks = [];
    public static $riskReasonLoopBlocks = [];
    public static $riskReasonSet = [];
    public static $riskReasonLoop = [];


    public static $documentLoopBlocks = [];
    public static $documentSet = [];
    public static $documentLoop = [];

    public static $wrongDocumentLoopBlocks = [];
    public static $wrongDocumentSet = [];
    public static $wrongDocumentLoop = [];

    public static $UTF8 = false;
    public static $ALLOW_HTML = true;

    public static $loadImages = [];


    /**
     * @var DOKTemplateCompiler;
     */
    public $_compilerObject;

    function __construct($templFile){
        parent::__construct($templFile);

        if(empty($this->_compile_dir))
            $this->_compile_dir = SERVICE_ROOT.'/cache/templ-c';

        if(empty($this->_template_dir))
            $this->_template_dir = SERVICE_TMPL_ROOT;

        $this->assignResults();
    }

    public function validate() {
        $this->recompile = true;
        $this->_validation = true;

        return $this->parse();
    }

    public function errors() {
        return $this->_compilerObject->_errors;
    }


    /**
     * Используетя для совместимости со старыми шаблонами, написанными с приминением старого синтаксиса
     */
    protected function assignResults() {
        $this->assign('warningBlocks', self::$warningBlocks);
        $this->assign('riskBlocks', self::$riskBlocks);
        $this->assign('riskReasonBlocks', self::$riskReasonBlocks);
        $this->assign('expressionBlocks', self::$expressionBlocks);

        $this->assign('infoBlocks', self::$infoBlocks);
        $this->assign('infoMultiBlocks', self::$infoLoopBlocks);
        $this->assign('infoSet', self::blockSetOld(self::$infoLoopBlocks));

        $this->assign('questionBlocks', self::$questionBlocks);
        $this->assign('questionMultiBlocks', self::$questionLoopBlocks);
        $this->assign('questionSet', self::blockSetOld(self::$questionLoopBlocks));
    }



    static public function setInfo($info) {
        self::$infoLoopBlocks =  self::loopBlocks($info);
        self::$infoSet = self::blockSet(self::$infoLoopBlocks);
        self::$infoLoop = self::blockLoop(self::$infoLoopBlocks);
        self::$infoBlocks = $info;
    }

    static public function setQuestion($question) {
        self::$questionLoopBlocks =  self::loopBlocks($question);
        self::$questionSet = self::blockSet(self::$questionLoopBlocks);
        self::$questionLoop = self::blockLoop(self::$questionLoopBlocks);
        self::$questionBlocks = $question;
    }

    static public function setExpression($expression) {
        self::$expressionLoopBlocks =  self::loopBlocks($expression);
        self::$expressionSet = self::blockSet(self::$expressionLoopBlocks);
        self::$expressionLoop = self::blockLoop(self::$expressionLoopBlocks);
        self::$expressionBlocks = $expression;
    }

    /**
     * Здесь весь набор RiskReason + Warning
     * @param $risk
     */
    static public function setRisk($risk) {
        $loopBlocks = self::loopBlocks($risk);
        self::$riskReasonBlocks = $risk;
    }

    static public function setRiskReason($riskReason) {
        self::$riskReasonLoopBlocks =  self::loopBlocks($riskReason);
        self::$riskReasonSet = self::blockSet(self::$riskReasonLoopBlocks);
        self::$riskReasonLoop = self::blockLoop(self::$riskReasonLoopBlocks);
    }

    static public function setRiskGeneral($riskGeneral) {
        self::$riskLoopBlocks =  self::loopBlocks($riskGeneral);
        self::$riskSet = self::blockSet(self::$riskLoopBlocks);
        self::$riskLoop = self::blockLoop(self::$riskLoopBlocks);
        self::$riskBlocks = $riskGeneral;
    }

    static public function setWarning($warning) {
        self::$warningLoopBlocks =  self::loopBlocks($warning);
        self::$warningSet = self::blockSet(self::$warningLoopBlocks);
        self::$warningLoop = self::blockLoop(self::$warningLoopBlocks);
        self::$warningBlocks = $warning;
    }

    static public function setDocumentGeneral($documentGeneral) {
        self::$documentLoopBlocks =  self::loopBlocks($documentGeneral);
        self::$documentSet = self::blockSet(self::$documentLoopBlocks);
        self::$documentLoop = self::blockLoop(self::$documentLoopBlocks);
    }

    static public function setWrongDocumentGeneral($documentGeneral) {
        self::$wrongDocumentLoopBlocks =  self::loopBlocks($documentGeneral);
        self::$wrongDocumentSet = self::blockSet(self::$wrongDocumentLoopBlocks);
        self::$wrongDocumentLoop = self::blockLoop(self::$wrongDocumentLoopBlocks);
    }


    static public function prepareVar($var) {
        if (!self::$ALLOW_HTML) {
            $var = strip_tags($var);
            $var = html_entity_decode($var);
        }
        return $var;
    }

    static public function algIdFilter($items, $algorithmId) {
        $result = [];

        foreach ($items as $item) {
            if (in_array($algorithmId, $item['algIds']))
                $result[] = $item;
        }

        return $result;
    }


    static protected function loopBlocks($blocks) {
        $result = [];

        foreach ($blocks as $block) {
            $parts = explode('_', $block['extId']);

            $id = (int)$parts[0];
            $num = isset($parts[1])?(int)$parts[1]:0;

            if (!isset($result[$id]))
                $result[$id] = [];

            $result[$id][$num] = $block;
        }

        return $result;
    }


    static protected function blockSet($loopBlocks) {
        $result = [];

        foreach ($loopBlocks as $id => $loops) {
            foreach (array_keys($loops) as $loop) {
                $result[$loop][] = ['id' => $id, 'loop' => $loop, 'algIds' => $loopBlocks[$id][$loop]['algIds']];
            }
        }

        return $result;
    }


    static protected function blockLoop($loopBlocks) {
        $result = [];

        foreach ($loopBlocks as $id => $loops) {
            foreach (array_keys($loops) as $loop) {
                $result[$id][] = ['loop' => $loop];
            }
        }

        return $result;
    }

    /**
     * Только для совместимости со старым синтаксисом
     * @param $blocks
     * @return array
     */

    static protected function blockSetOld($blocks) {
        $result = [];

        foreach ($blocks as $id => $block) {
            $result[$id] = array_keys($block);
        }

        return $result;
    }

    function setup_compiler(&$compiler){
        $this->_compilerObject = $compiler;

        if ($this->_validation)
            $compiler->_log_errors = true;
    }
}