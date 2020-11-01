<?php


namespace Model;

use Infr\Template;
use Model\Exception;


/**
 * Class Model_Risk
 *
 * @property-read $id
 * @property-read $header
 * @property-read $text
 * @property-read $documentId
 * @property-read $riskGeneralId
 *
 * @property Document $document
 * @property DocumentGeneral $documentGeneral
 * @property-read \Model\Collection\Risk $reasons
 */

class Risk extends DictEntity {

    protected $_id;
    protected $_header;
    protected $_text;
    protected $_documentId;
    protected $_documentGeneralId;
    protected $_riskGeneralId;

    protected $_document;
    protected $_documentGeneral;

    protected $_documentExtId;
    protected $_documentGeneralExtId;
    protected $_riskGeneralExtId;

    protected $_textUpdated;


    protected $textRendered = false;

    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var \Model\Risk $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'PT\RiskId');
        $entity->_documentId = static::castVar($data['documentId'],'PT\DocumentId', false);
        $entity->_documentGeneralId = static::castVar($data['documentGeneralId'],'PT\DocumentGeneralId', false);
        $entity->_header = static::castVar($data['header'],'TP\Text\Plain');
        $entity->_riskGeneralId = static::castVar($data['riskGeneralId'],'PT\RiskGeneralId');

        $entity->_extId =  "{$entity->_id}".($data['copyId']>0?"_{$data['copyId']}":'');
        $entity->_documentExtId = "{$entity->_documentId}".($data['copyId']>0?"_{$data['copyId']}":'');
        $entity->_documentGeneralExtId = "{$entity->_documentGeneralId}".($data['copyId']>0?"_{$data['copyId']}":'');
        $entity->_riskGeneralExtId = "{$entity->_riskGeneralId}".($data['copyId']>0?"_{$data['copyId']}":'');


        $entity->_entityType = \PT\EntityType::RISK();

        if ($entity->_documentId==0) {
            $entity->_documentId = '';
            $entity->_documentExtId = '';
        }

        if ($entity->_documentGeneralId==0) {
            $entity->_documentGeneralId = '';
            $entity->_documentGeneralExtId = '';
        }

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        return $entity;
    }

    public function __set($name, $value) {
        switch ($name) {
            case 'document':
                $this->_document = static::castVar($value, '\Model\Document');
                break;
            case 'documentGeneral':
                $this->_documentGeneral = static::castVar($value, '\Model\DocumentGeneral');
                break;
            default:
                parent::__set($name, $value);
                break;
        }
    }

    /**
     * @return null|\Model\Document
     * @throws \Exception
     */

    public function getReqDocument() {
        if (!$this->documentId>0)
            return null;

        return $this->document;
    }


    public function __get($name) {
        if ($name=='text')
            $this->renderText();

        return parent::__get($name);
    }

    public function render($inGroup = false) {
        $this->renderText();
        return parent::render($inGroup);
    }


    protected function applyContext($contextData) {
        $context = parent::applyContext($contextData);

        if (!is_null($context))
            $this->_textUpdated = $context['updated'];

        return $context;
    }


    protected function renderText() {
        if ($this->textRendered)
            return;

        if (strpos($this->_text, '{')!==false) {
            $templ = new Template\DB('Risk', $this->_id, $this->_textUpdated, $this->_text);
            $this->_text = $templ->parse();
        }

        $this->textRendered = true;
    }

}