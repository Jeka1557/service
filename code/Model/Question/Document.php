<?php


namespace Model\Question;
use TP;

/**
 * @property-read TP\UInt2 documentId
 * @property-read TP\UInt2 documentGeneralId
 * @property \Model\Document document
 * @property \Model\DocumentGeneral documentGeneral
 */

class Document extends YesNo {

    protected $_documentId;
    protected $_documentGeneralId;

    protected $_document;
    protected $_documentGeneral;

    protected $docGranted = false;

    protected function __construct() {}

    static public function newFromArray($data = []) {
        /* @var \Model\Question\Document $entity */
        $entity = parent::newFromArray($data);

        if (is_null($entity))
            return null;

        $entity->_documentId = static::castVar($data['documentId'],'TP\UInt2', false);
        $entity->_documentGeneralId = static::castVar($data['documentGeneralId'],'TP\UInt2');

        $entity->_questionType = 'document';

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


    public function setAnswerId($id) {
        $this->_answerId = $id;
        $this->docGranted = ($id==1)?true:false;
    }


    /**
     * @return null|\Model\DocumentGeneral
     * @throws \Exception
     */

    public function getGrntDocumentGeneral() {
        if (!$this->docGranted)
            return null;

        return $this->documentGeneral;
    }


    /**
     * @return null|\Model\Document
     * @throws \Exception
     */

    public function getGrntDocument() {
        if (!$this->docGranted or !isset($this->_document))
            return null;

        return $this->document;
    }
}