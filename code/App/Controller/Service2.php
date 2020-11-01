<?php

namespace App\Controller;

use PT;
use Model;
use App\Controller;
use Lib\Infr\Utility\Encoding;
use Lib\Infr\Db\Select;
use Model\Algorithm\Executor;
use Model\Storage;
use Model\Info\File;
use Infr\Config;
use Infr\Db\Content\Page;
use Infr\Db\Content\PageNode;
use Model\LinkRenderer;
use Model\Algorithm\RskFile;



class Service2 extends Controller {

    protected $frame = 'service';

    protected $UID = 0;


    function indexAction() {
        return [];
    }


    function objectsListAction() {
        if (!isset($_POST['answers']))
            return $this->error('answers not set');
        if (!isset($_POST['infoData']))
            return $this->error('infoData not set');
        if (!isset($_POST['algorithmId']) or empty($_POST['algorithmId']))
            return $this->error('algorithmId not set');
        if (!isset($_POST['contextId']) or empty($_POST['contextId']))
            return $this->error('contextId not set');
        //if (!isset($_POST['linkType']) or empty($_POST['linkType']))
        //    return $this->error('linkType not set');


        $answers = json_decode($_POST['answers'], true);
        $infoData = json_decode($_POST['infoData'], true);
        $contextId = (int)$_POST['contextId'];
        $algorithmId = (int)$_POST['algorithmId'];
        //$linkType = (int)$_POST['linkType'];

        $extraContextIds = isset($_POST['extraContextIds'])?$this->decodeExtraContextIds($_POST['extraContextIds']):array();


        $linkType = (int)1;

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

            \Model\DictEntity::setContext($contextId);
            \Model\DictEntity::setExtraContexts($extraContextIds);

            $algStorage = new \Model\Storage\Algorithm($dsn);
            $algorithm = $algStorage->getById(new \PT\AlgorithmId($algorithmId));

            $executor = new Model\Algorithm\Executor($algorithm->id, $dsn);

            $executor->runAlgorithm($answers, $infoData, true);
            $objects = $executor->getObjectSequence();

            $result['objects'] =  $this->formatObjects($objects, $extraContextIds);
            $result['isDone'] = $executor->isDone();


            $result['result'] = 'ok';

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

        return json_encode($result);
    }

    protected function findContext($algorithmId, $dsn) {
        $algStorage = new \Model\Storage\Algorithm($dsn);
        $contexts = $algStorage->getEntityContexts(new \PT\AlgorithmId($algorithmId));

        if (!count($contexts))
            throw new \Exception('Context not found');

        $contexts->rewind();
        return $contexts->current()->id;
    }


    protected function formatObjects($objects, $contextIds) {
        $result = array();

        foreach ($objects as $object) {
            if (is_a($object, "\Model\Question")) {
                /** @var \Model\Question $object */
                $result[] = $this->formatQuestionObject($object, $contextIds);

            } elseif (is_a($object, "\Model\Info")) {
                /** @var \Model\Info $object */
                $result[] = $this->formatInfoObject($object, $contextIds);

            } elseif (is_a($object, "\Model\Risk")) {
                /** @var \Model\Risk $object */
                $result[] = $this->formatWarningObject($object, $contextIds);
            }
        }

        return $result;
    }



    protected function formatQuestionObject(\Model\Question $object, $contextIds) {
        $result = array();

        $result['type'] = 'question';
        $result['id'] = $object->id;
        $result['header'] = $object->header;
        $result['text'] = $object->text;
        $result['contextTexts'] = array();

        $result['answerId'] = $object->isEmpty('answerId')?null:$object->answerId;

        $result['answers'] = array();

        foreach ($object->answers as $answer) {
            $result['answers'][] = $this->formatAnswerObject($answer, $contextIds);
        }

        foreach ($contextIds as $id) {
            $result['contextTexts'][$id] = $object->contextTexts[$id];
        }

        return $result;
    }


    protected function formatAnswerObject(\Model\Answer $object, $contextIds) {
        $result = array();

        $result['id'] = $object->id;
        $result['header'] = $object->header;
        $result['text'] = $object->text;
        $result['contextTexts'] = array();

        foreach ($contextIds as $id) {
            $result['contextTexts'][$id] = $object->contextTexts[$id];
        }

        return $result;
    }


    protected function formatInfoObject(\Model\Info $object, $contextIds) {
        $result = array();

        $result['type'] = 'info';
        $result['id'] = $object->id;
        $result['header'] = $object->header;
        $result['text'] = $object->text;
        $result['contextTexts'] = array();

        foreach ($contextIds as $id) {
            $result['contextTexts'][$id] = $object->contextTexts[$id];
        }

        return $result;
    }


    protected function formatWarningObject(\Model\Risk $object, $contextIds) {
        $result = array();

        $result['type'] = 'warning';
        $result['id'] = $object->id;
        $result['header'] = $object->header;
        $result['text'] = $object->text;
        $result['contextTexts'] = array();

        foreach ($contextIds as $id) {
            $result['contextTexts'][$id] = $object->contextTexts[$id];
        }

        return $result;
    }


    protected function decodeExtraContextIds($ids) {
        $result = array();

        $ids = json_decode($ids, true);

        foreach ($ids as $id) {
            if ((int)$id>0)
                $result[] = (int)$id;
        }

        return $result;
    }


    protected function setLinksRenderMode($linkType) {
        switch ($linkType) {
            case 3: \Model\LinkRenderer::instance()->setMode(\Model\LinkRenderer::MODE_GARANT); break;
            case 2: \Model\LinkRenderer::instance()->setMode(\Model\LinkRenderer::MODE_CONSULTANT); break;
            default: \Model\LinkRenderer::instance()->setMode(\Model\LinkRenderer::MODE_WEB); break;
        }
    }

    protected function error($message) {
        ob_clean();
        return json_encode(array('result' => 'error', 'message' => $message));
    }
}

