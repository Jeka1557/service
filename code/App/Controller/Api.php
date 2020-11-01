<?php

namespace App\Controller;

use App\Controller;

class Api extends Controller\Service {

    protected $frame = 'main';

    /**
     * https://guidgenerator.com/online-guid-generator.aspx
     */

    protected $accessKeys = [
        '75ccc64f-547c-43fb-a816-c7c29c483a18',

        '57821bd2-94c0-4291-a8c5-ab0d81f22c80', // 19. Система для брендинга

        '3b97669d-48ac-4744-9a82-90bd20d812b1' //  20. Карма
    ];

    function indexAction() {
        return [
            'serverName' => $_SERVER['SERVER_NAME'],
        ];
    }


    public function algorithmNodesAction() {
        if (!isset($_POST['accessKey']))
            return $this->error('Access key not set');


        if (!in_array($_POST['accessKey'], $this->accessKeys))
            return $this->error('Access key is invalid');

        $_POST['linkType'] = 1;

        $result = parent::nodeListAction();

        header('Content-Type: application/json');
        return $result;
    }


    public function nodeListAction() {
        return '';
    }


    public function textPageAction() {
        if (!isset($_POST['accessKey']))
            return $this->error('Access key not set');


        if (!in_array($_POST['accessKey'], $this->accessKeys))
            return $this->error('Access key is invalid');

        $_POST['linkType'] = 1;

        $result = parent::textPageAction();

        header('Content-Type: application/json');
        return $result;
    }


    public function conclusionAction() {
        if (!isset($_POST['accessKey']))
            return $this->error('Access key not set');


        if (!in_array($_POST['accessKey'], $this->accessKeys))
            return $this->error('Access key is invalid');

        $_POST['linkType'] = 1;

        $result = parent::conclusionTextAction();

        header('Content-Type: application/json');
        return $result;
    }


    public function conclusionPdfAction() {
        if (!isset($_POST['accessKey']))
            return 'Access key not set';


        if (!in_array($_POST['accessKey'], $this->accessKeys))
            return 'Access key is invalid';

        $_POST['linkType'] = 1;

        parent::conclusionPdfAction();

        return '';
    }


    public function conclusionDocxAction() {
        if (!isset($_POST['accessKey']))
            return 'Access key not set';


        if (!in_array($_POST['accessKey'], $this->accessKeys))
            return 'Access key is invalid';

        $_POST['linkType'] = 1;

        parent::conclusionDocxAction();

        return '';
    }


    public function logFileAction() {
        if (!isset($_GET['UID']))
            return 'UID not set';

        $UID = (int)$_GET['UID'];

        if (!isset($_GET['logKey']) or ($this->logUIDKey($UID)!=$_GET['logKey']))
            return 'Log key is invalid';


        $logsController = new \App\Controller\Logs();

        $_GET['dir'] = date('Y-m-d');
        $_GET['name'] = "{$UID}.log";

        return $logsController->fileAction();
    }
}

