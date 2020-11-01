<?php

namespace App\Controller;

use PT;
use Model;
use App\Controller;
use Lib\Infr\Db\Select;
use Model\Algorithm\Executor;
use Model\Storage;
use Model\Info\File;
use Infr\Config;
use Infr\Db\Content\Page;
use Infr\Db\Content\PageNode;
use Model\LinkRenderer;
use Model\Algorithm\RskFile;
use Infr\ErrorLogger;



class Service extends Controller {

    protected $frame = 'service';

    protected $UID = 0;


    function testAction() {
        return [
            'action' => isset($_GET['action'])?$_GET['action']:'question_list'
        ];
    }

    function redirectAction() {
        return [];
    }

    function sendFileAction() {
        return [];
    }

    function uplFileAction() {
        return [];
    }


    function downloadFileAction() {
        try {

            if (!isset($_POST['fileWebID']))
                throw new \Exception('fileWebID not set');
            if (!isset($_POST['UID']))
                throw new \Exception('UID not set');

            $fileWebID = $_POST['fileWebID'];
            $UID = (int)$_POST['UID'];

            File::downloadFileGeneral($fileWebID, $UID);

        } catch (\Exception $e) {
            header('HTTP/1.0 404 Not Found', true, 404);
            header("Warning: \"{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}\"");
        }

        exit();
    }


    function uploadFileAction() {
        header('Content-Type: application/json');

        if (!isset($_POST['fileWebID']))
            return $this->error('fileWebID not set');
        if (!isset($_POST['UID']))
            return $this->error('UID not set');

        if (!isset($_FILES['info-file']))
            return $this->error('Info file not found');

        if ($_FILES['info-file']['error']!==UPLOAD_ERR_OK)
            return $this->error('Info file upload error');

        if (!is_uploaded_file($_FILES['info-file']['tmp_name']))
            $this->error('Info file is not uploaded file');


        $fileWebID = $_POST['fileWebID'];
        $UID = (int)$_POST['UID'];
        $tmpFile = $_FILES['info-file']['tmp_name'];

        try {
            File::uploadFileGeneral($fileWebID, $tmpFile, $UID);

            $result = [];
            $result['result'] = 'ok';

            $result = json_encode($result, JSON_UNESCAPED_UNICODE);

            if ($result===false)
                throw new \Exception(json_last_error());

            return $result;

        } catch (\Exception $e) {
            return $this->error("{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}");
        }
    }


    function nodeListAction() {
        header('Content-Type: application/json');

        if (!isset($_POST['answers']))
            return $this->error('answers not set');
        if (!isset($_POST['infoData']))
            return $this->error('infoData not set');
        if (!isset($_POST['algorithmId']) or empty($_POST['algorithmId']))
            return $this->error('algorithmId not set');
        if (!isset($_POST['contextId']) or empty($_POST['contextId']))
            return $this->error('contextId not set');
        if (!isset($_POST['linkType']) or empty($_POST['linkType']))
            return $this->error('linkType not set');


        $answers = json_decode($_POST['answers'], true);
        $infoData = json_decode($_POST['infoData'], true);
        $contextId = (int)$_POST['contextId'];
        $algorithmId = (int)$_POST['algorithmId'];
        $linkType = (int)$_POST['linkType'];
        $UID = (isset($_POST['UID']) and (int)$_POST['UID']>0)?(int)$_POST['UID']:null;
        $renderMode = isset($_POST['renderMode'])?$_POST['renderMode']:'html';

        $actions = isset($_POST['actions'])?json_decode($_POST['actions'], true):[];


        if (!is_array($answers)) {
            return $this->error('Answers is incorrect');
        }

        if (!is_array($infoData)) {
            return $this->error('Info Data is incorrect');
        }

        try {
            $dsn = Config::getDSN();
            $result = array();

            $this->setEntitiesRenderMode($renderMode);
            $this->setLinksRenderMode($linkType);


            if ($contextId==-1)
                $contextId = $this->findContext($algorithmId, $dsn);

            Model\DictEntity::setContext($contextId);

            $algStorage = new Storage\Algorithm($dsn);
            $algorithm = $algStorage->getById(new PT\AlgorithmId($algorithmId));

            if (is_null($algorithm))
                return $this->error("Algorithm: {$algorithmId} not found");


            $executor = new Executor($algorithm->id, $dsn, $UID);
            $executor->runAlgorithm($answers, $infoData, true, $actions);

            $algResult = $executor->getResult();

            $result['nodes']  = $executor->getNodeSequence(true);
            $result['UID']    = $executor->getUID();
            $result['isDone'] = $executor->isDone();
            $result['doneActions'] = $executor->getResult()->getActionsDoneHash();

            $result['grantedDocumentsCount'] = $algResult->getGrantedDocumentGeneralCnt();

            $result['wrongDocumentsCount'] = $algResult->getWrongDocumentGeneralCnt();
            $result['wrongDocumentPropertiesCount'] = $algResult->getWrongDocumentCnt();

            $result['risksCount'] = $algResult->getRiskGeneralCnt();
            $result['riskReasonsCount'] = $algResult->getRiskCnt();

            $riskLevel  = $algResult->getRiskLevel();
            $result['riskLevel'] = $riskLevel?$riskLevel->val():0;

            $result['result'] = 'ok';

            $result = json_encode($result, JSON_UNESCAPED_UNICODE );

            if ($result===false)
                throw new \Exception(json_last_error());

            return $result;

        } catch (\Exception $e) {
            return $this->error("{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}");
        }
    }



    function questionListAction() {
        header('Content-Type: application/json');

        if (!isset($_POST['answers']))
            return $this->error('answers not set');
        if (!isset($_POST['infoData']))
            return $this->error('infoData not set');
        if (!isset($_POST['algorithmId']) or empty($_POST['algorithmId']))
            return $this->error('algorithmId not set');
        if (!isset($_POST['contextId']) or empty($_POST['contextId']))
            return $this->error('contextId not set');
        if (!isset($_POST['linkType']) or empty($_POST['linkType']))
            return $this->error('linkType not set');


        $answers = json_decode($_POST['answers'], true);
        $infoData = json_decode($_POST['infoData'], true);
        $contextId = (int)$_POST['contextId'];
        $algorithmId = (int)$_POST['algorithmId'];
        $linkType = (int)$_POST['linkType'];
        $UID = (isset($_POST['UID']) and (int)$_POST['UID']>0)?(int)$_POST['UID']:null;

        $actions = isset($_POST['actions'])?json_decode($_POST['actions'], true):[];

        if (!is_array($answers)) {
            return $this->error('Answers is incorrect');
        }

        if (!is_array($infoData)) {
            return $this->error('Info Data is incorrect');
        }

        try {
            $dsn = Config::getDSN();
            $result = array();

            $this->setLinksRenderMode($linkType);

            if ($contextId==-1)
                $contextId = $this->findContext($algorithmId, $dsn);

            Model\DictEntity::setContext($contextId);

            $algStorage = new Storage\Algorithm($dsn);
            $algorithm = $algStorage->getById(new PT\AlgorithmId($algorithmId));

            if (is_null($algorithm))
                return $this->error("Algorithm: {$algorithmId} not found");


            $executor = new Executor($algorithm->id, $dsn, $UID);
            $algResult = $executor->getResult();

            $result['inputs'] = $executor->runAlgorithm($answers, $infoData, true, $actions);
            $result['isDone'] = $executor->isDone();

            $result['grantedDocumentsCount'] = $algResult->getGrantedDocumentGeneralCnt();

            $result['wrongDocumentsCount'] = $algResult->getWrongDocumentGeneralCnt();
            $result['wrongDocumentPropertiesCount'] = $algResult->getWrongDocumentCnt();

            $result['risksCount'] = $algResult->getRiskGeneralCnt();
            $result['riskReasonsCount'] = $algResult->getRisksCnt();

            $riskLevel  = $algResult->getRiskLevel();
            $result['riskLevel'] = $riskLevel?$riskLevel->val():0;

            $result['result'] = 'ok';
            $result['algorithmError'] = is_null($executor->getError())?false:true;
            $result['UID']    = $executor->getUID();

            $result = json_encode($result, JSON_UNESCAPED_UNICODE);

            if ($result===false)
                throw new \Exception(json_last_error());

            return $result;

        } catch (\Exception $e) {
            return $this->error("{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}");
        }
    }

    function conclusionAction() {
        header('Content-Type: application/json');

        if (!isset($_POST['answers']))
            return $this->error('answers not set');
        if (!isset($_POST['infoData']))
            return $this->error('infoData not set');
        if (!isset($_POST['algorithmId']) or empty($_POST['algorithmId']))
            return $this->error('algorithmId not set');
        if (!isset($_POST['contextId']) or empty($_POST['contextId']))
            return $this->error('contextId not set');
        if (!isset($_POST['linkType']) or empty($_POST['linkType']))
            return $this->error('linkType not set');

        $answers = json_decode($_POST['answers'], true);
        $infoData = json_decode($_POST['infoData'], true);
        $algorithmId = (int)$_POST['algorithmId'];
        $linkType = (int)$_POST['linkType'];
        $contextId = (int)$_POST['contextId'];
        $UID = (isset($_POST['UID']) and (int)$_POST['UID']>0)?(int)$_POST['UID']:null;

        $actions = isset($_POST['actions'])?json_decode($_POST['actions'], true):[];

        if (!is_array($answers)) {
            return $this->error('Answers is incorrect');
        }

        if (!is_array($infoData)) {
            return $this->error('info Data is incorrect');
        }

        try {
            $dsn = Config::getDSN();
            $result = array();

            $this->setLinksRenderMode($linkType);

            if ($contextId==-1)
                $contextId = $this->findContext($algorithmId, $dsn);

            Model\DictEntity::setContext($contextId);

            $algStorage = new Storage\Algorithm($dsn);
            $algorithm = $algStorage->getById(new PT\AlgorithmId($algorithmId));

            if (is_null($algorithm))
                return $this->error("Algorithm: {$algorithmId} not found");


            $executor = new Executor($algorithm->id, $dsn, $UID);
            $executor->runAlgorithm($answers, $infoData, false, $actions);

            $algResult = $executor->getResult();

            $risks = $algResult->risksGeneral;
            $documents = $algResult->grantedDocumentsGeneral;
            $wrongDocuments = $algResult->wrongDocumentsGeneral;
            $warnings = $algResult->warnings;
            $infoBlocks = $algResult->info;


            $result['risks'] = $risks->export();
            $result['documents'] = $documents->export();
            $result['wrongDocuments'] = $wrongDocuments->export();
            $result['warnings'] = $warnings->export();
            $result['extraInfo'] = $infoBlocks->export();


            $riskLevel  = $algResult->getRiskLevel();
            $result['riskLevel'] = $riskLevel?$riskLevel->val():0;


            $result['result'] = 'ok';
            $result['UID']    = $executor->getUID();


            $result = json_encode($result, JSON_UNESCAPED_UNICODE);

            if ($result===false)
                throw new \Exception(json_last_error());

            return $result;

        } catch (\Exception $e) {
            return $this->error("{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}");
        }
    }



    function textPageAction() {
        $loadById = false;

        if (isset($_POST['id']) and !empty($_POST['id'])) {
            $loadById = true;
        }

        if (!$loadById and !isset($_POST['url']))
            return $this->error('url not set');

        if (!isset($_POST['linkType']) or empty($_POST['linkType']))
            return $this->error('linkType not set');

        try {
            $linkType = (int)$_POST['linkType'];
            $result = array();

            $this->setLinksRenderMode($linkType);


            $select = new Page(Config::getDSN());
            $select->reset(Select::COLUMNS)
                ->columns(array(
                    'path' => 'path',
                    'title' => 'title',
                    'keywords' => 'keywords',
                    'description' => 'description',
                    'content' => 'content'
                ))
                ->where('trash', 0);


            if ($loadById) {
                $id = (int)$_POST['id'];
                $select->where('id', $id);

            } else {
                $url = $_POST['url'];
                $select->where('path', $url);
            }

            $page = $select->execute()
            ->fetchRow();


            if (is_null($page))
                $result['page'] = null;
            else {
                $result['page']['content'] = LinkRenderer::instance()->renderLinks($page['content']);
                $result['page']['title'] = $page['title'];
                $result['page']['keywords'] = $page['keywords'];
                $result['page']['description'] = $page['description'];
            }

            $result['result'] = 'ok';

            $result = json_encode($result, JSON_UNESCAPED_UNICODE);

            if ($result===false)
                throw new \Exception(json_last_error());

            return $result;

        } catch (\Exception $e) {
            return $this->error("{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}");
        }
    }


    function textIndexAction() {

        if (!isset($_POST['url']))
            return $this->error('url not set');

        try {
            $result = array();

            $url = $_POST['url'];

            $url = preg_replace('~^/content~','', $url);

            $result['index'] = $this->getChildPages($url);
            $result['result'] = 'ok';

            $result = json_encode($result, JSON_UNESCAPED_UNICODE);

            if ($result===false)
                throw new \Exception(json_last_error());

            return $result;

        } catch (\Exception $e) {
            return $this->error("{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}");
        }
    }


    function textFullIndexAction() {

        try {
            $result = array();
            $index = array(
                'path' => '/',
            );

            $this->fillFullIndex($index);

            $result['index'] = $index;
            $result['result'] = 'ok';

            $result = json_encode($result, JSON_UNESCAPED_UNICODE);

            if ($result===false)
                throw new \Exception(json_last_error());

            return $result;

        } catch (\Exception $e) {
            return $this->error("{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}");
        }
    }

    protected function fillFullIndex(&$index) {
        $index['childs'] = $this->getChildPages($index['path']);

        foreach ($index['childs'] as &$page) {
            $this->fillFullIndex($page);
        }
    }


    protected function getChildPages($url) {

        $select = new Page(Config::getDSN());
        $result = $select->reset(Select::COLUMNS)
            ->columns(array(
                'path' => 'path',
                'title' => 'title'
            ))
            ->where('path_parent', $url)
            ->where('trash',0)
            ->where('workspace_id', 1)
            ->order('title')
            ->execute()
            ->fetchAll();


       return $result;
    }


    function textBreadcrumbsAction() {
        header('Content-Type: application/json');

        if (!isset($_POST['url']))
            return $this->error('url not set');

        try {
            $result = array();

            $url = $_POST['url'];

            $url = preg_replace('~^/content~','', $url);


            $select = new PageNode(Config::getDSN());
            $rows = $select->reset(Select::COLUMNS)
                ->columns(array(
                    'id' => 'node_path',
                    'parentId' => 'parent_path',
                    'name' => 'node_name',
                ))
                ->order(array('node_path','node_name'))
                ->where('workspace_id', [1,2])
                ->execute()
                ->fetchAll();


            $tree = $this->getFilterTree($rows, null);

            $breadcrumbs = array();
            $urlParts = explode('/',$url);
            $path = array_shift($urlParts);

            $this->makeBreadcrumbs($breadcrumbs, $path, $urlParts, $tree['/']);

            array_shift($breadcrumbs);

            $result['breadcrumbs'] = $breadcrumbs;
            $result['result'] = 'ok';

            $result = json_encode($result, JSON_UNESCAPED_UNICODE);

            if ($result===false)
                throw new \Exception(json_last_error());

            return $result;

        } catch (\Exception $e) {
            return $this->error("{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}");
        }
    }

    protected function makeBreadcrumbs(&$breadcrumbs, &$path, &$urlParts, $tree) {

        $breadcrumbs[] = array(
            'title' => $tree['row']['name'],
            'path' => $tree['row']['id'],
        );

        if (!count($urlParts))
            return;

        $path .= '/'.array_shift($urlParts);

        if (count($tree['childs']) and isset($tree['childs'][$path])) {
            $this->makeBreadcrumbs($breadcrumbs, $path, $urlParts, $tree['childs'][$path]);
        }
    }


    protected function findContext($algorithmId, $dsn) {
        $algStorage = new Storage\Algorithm($dsn);
        $contexts = $algStorage->getEntityContexts(new PT\AlgorithmId($algorithmId));

        if (!count($contexts))
            throw new \Exception('Context not found');

        $contexts->rewind();
        return $contexts->current()->id;
    }

    protected function setLinksRenderMode($linkType) {
        switch ($linkType) {
            case 3: LinkRenderer::instance()->setMode(LinkRenderer::MODE_GARANT); break;
            case 2: LinkRenderer::instance()->setMode(LinkRenderer::MODE_CONSULTANT); break;
            default: LinkRenderer::instance()->setMode(LinkRenderer::MODE_WEB); break;
        }
    }

    protected function setEntitiesRenderMode($mode) {
        switch ($mode) {
            case 'array':
                Model\DictEntity::setRenderMode(Model\DictEntity::RENDER_MODE_ARRAY);
            break;
            case 'webclient':
                Model\DictEntity::setRenderMode(Model\DictEntity::RENDER_MODE_WC);
            break;
            case 'vtb':
                Model\DictEntity::setRenderMode(Model\DictEntity::RENDER_MODE_VTB);
                break;
            case 'html':
            default:
                Model\DictEntity::setRenderMode(Model\DictEntity::RENDER_MODE_HTML);
            break;
        }
    }


    protected function error($message) {
        ErrorLogger::serviceError($message);

        ob_clean();
        return json_encode(['result' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE );
    }


    protected function getFilterTree($rows, $parentId = 0) {

        $result = array();

        foreach ($rows as $row) {
            if ($row['parentId']==$parentId) {
                $result[$row['id']] = array(
                    'id' => $row['id'],
                    'row' => $row,
                    'childs' => $this->getFilterTree($rows, $row['id'])
                );
            }
        }

        return $result;
    }



    function getRskFileAction() {
        try {
            if (!isset($_POST['answers']))
                throw new \Exception('answers not set');
            if (!isset($_POST['infoData']))
                throw new \Exception('infoData not set');
            if (!isset($_POST['algorithmId']) or empty($_POST['algorithmId']))
                throw new \Exception('algorithmId not set');
            if (!isset($_POST['contextId']) or empty($_POST['contextId']))
                throw new \Exception('contextId not set');
            if (!isset($_POST['linkType']) or empty($_POST['linkType']))
                throw new \Exception('linkType not set');
            if (!isset($_POST['UID']) or empty($_POST['UID']))
                throw new \Exception('UID not set');


            $answers = json_decode($_POST['answers'], true);
            $infoData = json_decode($_POST['infoData'], true);
            $contextId = (int)$_POST['contextId'];
            $algorithmId = (int)$_POST['algorithmId'];
            $linkType = (int)$_POST['linkType'];
            $UID = (int)$_POST['UID'];

            $actions = isset($_POST['actions'])?json_decode($_POST['actions'], true):[];


            if (!is_array($answers))
                throw new \Exception('Answers is incorrect');

            if (!is_array($infoData))
                throw new \Exception('Answers is incorrect');


            $dsn = Config::getDSN();
            $result = array();

            $this->setEntitiesRenderMode('array');
            $this->setLinksRenderMode($linkType);


            if ($contextId==-1)
                $contextId = $this->findContext($algorithmId, $dsn);

            Model\DictEntity::setContext($contextId);

            $algStorage = new Storage\Algorithm($dsn);
            $algorithm = $algStorage->getById(new PT\AlgorithmId($algorithmId));

            if (is_null($algorithm))
                return $this->error("Algorithm: {$algorithmId} not found");


            $executor = new Executor($algorithm->id, $dsn, $UID);
            $executor->runAlgorithm($answers, $infoData, true, $actions);
            // $result['nodes']  = $executor->getNodeSequence(true);

            $infoFiles = $executor->getResult()->infoFiles;
            $result['isDone'] = $executor->isDone();


            $rskFile = new RskFile($executor->getUID());

            $rskFile->setAlgorithmId($algorithmId);
            $rskFile->setContextId($contextId);
            $rskFile->setLinkType($linkType);
            $rskFile->setAnswers($answers);
            $rskFile->setInfoData($infoData);


            foreach ($infoFiles as $file) {
                /** @var \Model\Info\File $file **/
                if (!$file->hasValue)
                    continue;

                $rskFile->addFile($file->fileWebID, $file->fileName, $file->getFilePath());
            }

            $rskFile->create();


            $rskFile->outputs();
            $rskFile->drop();

        } catch (\Exception $e) {
            header('HTTP/1.0 404 Not Found', true, 404);
            header("Warning: \"{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}\"");
        }

        exit();
    }


    function putRskFileAction() {
        header('Content-Type: application/json');

        if (!isset($_FILES['rsk-file']))
            $this->error('Rsk file is not found');

        if ($_FILES['rsk-file']['error']!==UPLOAD_ERR_OK)
            $this->error('Rsk file upload error');

        if (!is_uploaded_file($_FILES['rsk-file']['tmp_name']))
            $this->error('Rsk file is not uploaded file');


        try {

            $rskFile = RskFile::fromUploadedFile($_FILES['rsk-file']['tmp_name']);

            $algorithmId = $rskFile->getAlgorithmId();
            $contextId = $rskFile->getContextId();
            $linkType = $rskFile->getLinkType();

            $answers = $rskFile->getAnswers();
            $infoData = $rskFile->getInfoData();
            $files = $rskFile->getFiles();


            $dsn = Config::getDSN();
            $result = array();

            $this->setEntitiesRenderMode('array');
            $this->setLinksRenderMode($linkType);


            if ($contextId==-1)
                $contextId = $this->findContext($algorithmId, $dsn);

            Model\DictEntity::setContext($contextId);

            $algStorage = new Storage\Algorithm($dsn);
            $algorithm = $algStorage->getById(new PT\AlgorithmId($algorithmId));

            if (is_null($algorithm))
                return $this->error("Algorithm: {$algorithmId} not found");


            $executor = new Executor($algorithm->id, $dsn);
            $UID = $executor->getUID();

            foreach ($files as $webId => $file) {
                $fileName = $rskFile->extractFile($file['zipName']);
                File::uploadFileGeneral($webId, $fileName, $UID);
            }

            $executor->runAlgorithm($answers, $infoData, true);

            //$result['nodes']  = $executor->getNodeSequence(true);

            $result['isDone'] = $executor->isDone();
            $result['UID']    = $UID;

            $result['algorithmId'] = $algorithmId;
            $result['contextId'] = $contextId;
            $result['linkType'] = $linkType;

            $result['answers'] = $answers;
            $result['infoData'] = $infoData;

            $result['result'] = 'ok';

            $rskFile->drop();

            $result = json_encode($result, JSON_UNESCAPED_UNICODE );

            if ($result===false)
                throw new \Exception(json_last_error());

            return $result;

        } catch (\Exception $e) {
            return $this->error("{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}");
        }
    }


    function conclusionTextAction() {
        header('Content-Type: application/json');

        if (!isset($_POST['answers']))
            return $this->error('answers not set');
        if (!isset($_POST['infoData']))
            return $this->error('infoData not set');
        if (!isset($_POST['algorithmId']) or empty($_POST['algorithmId']))
            return $this->error('algorithmId not set');
        if (!isset($_POST['contextId']) or empty($_POST['contextId']))
            return $this->error('contextId not set');
        if (!isset($_POST['linkType']) or empty($_POST['linkType']))
            return $this->error('linkType not set');

        $answers = json_decode($_POST['answers'], true);
        $infoData = json_decode($_POST['infoData'], true);
        $algorithmId = (int)$_POST['algorithmId'];
        $linkType = (int)$_POST['linkType'];
        $contextId = (int)$_POST['contextId'];
        $conclusionId = (isset($_POST['conclusionId']) and (int)$_POST['conclusionId']>0)?new PT\ConclusionId($_POST['conclusionId']):null;
        $UID = (isset($_POST['UID']) and (int)$_POST['UID']>0)?(int)$_POST['UID']:null;

        $actions = isset($_POST['actions'])?json_decode($_POST['actions'], true):[];

        if (!is_array($answers)) {
            return $this->error('Answers is incorrect');
        }

        if (!is_array($infoData)) {
            return $this->error('info Data is incorrect');
        }

        try {
            $conclusion = $this->conclusion($algorithmId, $answers, $infoData, $contextId, $linkType, $conclusionId, $UID, $actions);

            $result = [];

            $result['conclusion'] = $conclusion->render();
            $result['conclusion_id'] = $conclusion->id;
            $result['conclusion_header'] = $conclusion->header;
            $result['conclusion_type'] = $conclusion->type;

            $result['result'] = 'ok';
            $result['UID']    = $this->UID;

            $result = json_encode($result, JSON_UNESCAPED_UNICODE );

            if ($result===false)
                throw new \Exception(json_last_error());

            return $result;

        } catch (\Exception $e) {
            return $this->error("{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}");
        }
    }



    function conclusionPdfAction() {

        if (!isset($_POST['answers']))
            return $this->error('answers not set');
        if (!isset($_POST['infoData']))
            return $this->error('infoData not set');
        if (!isset($_POST['algorithmId']) or empty($_POST['algorithmId']))
            return $this->error('algorithmId not set');
        if (!isset($_POST['contextId']) or empty($_POST['contextId']))
            return $this->error('contextId not set');
        if (!isset($_POST['linkType']) or empty($_POST['linkType']))
            return $this->error('linkType not set');

        $answers = json_decode($_POST['answers'], true);
        $infoData = json_decode($_POST['infoData'], true);
        $algorithmId = (int)$_POST['algorithmId'];
        $linkType = (int)$_POST['linkType'];
        $contextId = (int)$_POST['contextId'];
        $conclusionId = (isset($_POST['conclusionId']) and (int)$_POST['conclusionId']>0)?new PT\ConclusionId($_POST['conclusionId']):null;
        $UID = (isset($_POST['UID']) and (int)$_POST['UID']>0)?(int)$_POST['UID']:null;

        $actions = isset($_POST['actions'])?json_decode($_POST['actions'], true):[];

        $dsn = Config::getDSN();

        if (!is_array($answers)) {
            return $this->error('Answers is incorrect');
        }

        if (!is_array($infoData)) {
            return $this->error('info Data is incorrect');
        }

        try {
            $conclusion = $this->conclusion($algorithmId, $answers, $infoData, $contextId, $linkType, $conclusionId, $UID, $actions);
            $conclusion->setStorage(new \Model\Storage\Conclusion($dsn));
            $conclusion->outputPDF();

        } catch (\Exception $e) {
            $logKey = $this->logUIDKey($this->UID);
            $this->fileNotFound($e, $this->UID, $logKey);
        }

        return '';
    }


    function conclusionDocxAction() {

        if (!isset($_POST['answers']))
            return $this->error('answers not set');
        if (!isset($_POST['infoData']))
            return $this->error('infoData not set');
        if (!isset($_POST['algorithmId']) or empty($_POST['algorithmId']))
            return $this->error('algorithmId not set');
        if (!isset($_POST['contextId']) or empty($_POST['contextId']))
            return $this->error('contextId not set');
        if (!isset($_POST['linkType']) or empty($_POST['linkType']))
            return $this->error('linkType not set');

        $answers = json_decode($_POST['answers'], true);
        $infoData = json_decode($_POST['infoData'], true);
        $algorithmId = (int)$_POST['algorithmId'];
        $linkType = (int)$_POST['linkType'];
        $contextId = (int)$_POST['contextId'];
        $conclusionId = (isset($_POST['conclusionId']) and (int)$_POST['conclusionId']>0)?new PT\ConclusionId($_POST['conclusionId']):null;
        $UID = (isset($_POST['UID']) and (int)$_POST['UID']>0)?(int)$_POST['UID']:null;

        $actions = isset($_POST['actions'])?json_decode($_POST['actions'], true):[];

        $dsn = Config::getDSN();

        if (!is_array($answers)) {
            return $this->error('Answers is incorrect');
        }

        if (!is_array($infoData)) {
            return $this->error('info Data is incorrect');
        }

        try {
            $conclusion = $this->conclusion($algorithmId, $answers, $infoData, $contextId, $linkType, $conclusionId, $UID, $actions);
            $conclusion->setStorage(new \Model\Storage\Conclusion($dsn));
            $conclusion->outputDocx();

        } catch (\Exception $e) {
            $logKey = $this->logUIDKey($this->UID);
            $this->fileNotFound($e, $this->UID, $logKey);
        }

        return '';
    }

    public function conclusionXlsxAction() {
        if (!isset($_POST['answers']))
            return $this->error('answers not set');
        if (!isset($_POST['infoData']))
            return $this->error('infoData not set');
        if (!isset($_POST['algorithmId']) or empty($_POST['algorithmId']))
            return $this->error('algorithmId not set');
        if (!isset($_POST['contextId']) or empty($_POST['contextId']))
            return $this->error('contextId not set');
        if (!isset($_POST['linkType']) or empty($_POST['linkType']))
            return $this->error('linkType not set');

        $answers = json_decode($_POST['answers'], true);
        $infoData = json_decode($_POST['infoData'], true);
        $algorithmId = (int)$_POST['algorithmId'];
        $linkType = (int)$_POST['linkType'];
        $contextId = (int)$_POST['contextId'];
        $conclusionId = (isset($_POST['conclusionId']) and (int)$_POST['conclusionId']>0)?new PT\ConclusionId($_POST['conclusionId']):null;
        $UID = (isset($_POST['UID']) and (int)$_POST['UID']>0)?(int)$_POST['UID']:null;

        $actions = isset($_POST['actions'])?json_decode($_POST['actions'], true):[];

        $dsn = Config::getDSN();

        if (!is_array($answers)) {
            return $this->error('Answers is incorrect');
        }

        if (!is_array($infoData)) {
            return $this->error('info Data is incorrect');
        }

        try {
            $conclusion = $this->conclusion($algorithmId, $answers, $infoData, $contextId, $linkType, $conclusionId, $UID, $actions);
            $conclusion->setStorage(new \Model\Storage\Conclusion($dsn));
            $conclusion->outputExcel();

        } catch (\Exception $e) {
            $logKey = $this->logUIDKey($this->UID);
            $this->fileNotFound($e, $this->UID, $logKey);
        }

        return '';
    }


    /**
     * @param $algorithmId
     * @param $answers
     * @param $infoData
     * @param $contextId
     * @param $linkType
     * @param $conclusionId
     * @param $UID
     * @return Model\Conclusion
     * @throws \Exception
     */

    protected function conclusion($algorithmId, $answers, $infoData, $contextId, $linkType, $conclusionId, $UID, $actions) {
        $dsn = Config::getDSN();

        $this->setLinksRenderMode($linkType);

        if ($contextId==-1)
            $contextId = $this->findContext($algorithmId, $dsn);

        Model\DictEntity::setContext($contextId);

        $algStorage = new Storage\Algorithm($dsn);
        $algorithm = $algStorage->getById(new PT\AlgorithmId($algorithmId));

        if (is_null($algorithm))
            return $this->error("Algorithm: {$algorithmId} not found");


        $executor = new Executor($algorithm->id, $dsn, $UID);
        $this->UID = $executor->getUID();

        $executor->runAlgorithm($answers, $infoData, false, $actions);
        $conclusion = $executor->getResult()->getConclusion($conclusionId);

        if (is_null($conclusion))
            throw new \Exception("Conclusion id: {$conclusionId} not found");


        return $conclusion;
    }


    protected function logUIDKey($UID) {
        return md5("b7e65bc7-{$UID}-445e-a350-84bdc06a1e9a");
    }

    protected function fileNotFound(\Exception $e, $UID, $logKey) {
        $message = "{$e->getMessage()} File: {$e->getFile()} Line: {$e->getLine()}";

        ErrorLogger::serviceError($message);

        header('HTTP/1.0 404 Not Found', true, 404);
        header("Content-Type: application/octet-stream", true);
        header("Warning: \"{$message}\"");
        header("Log: \"http://{$_SERVER['HTTP_HOST']}/api/log-file?UID={$UID}&logKey={$logKey}\"");
    }

}

