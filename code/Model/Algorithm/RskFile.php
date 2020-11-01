<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 06.10.2018
 * Time: 20:33
 */

namespace Model\Algorithm;

use Infr\Config;
use Lib\Infr\Utility\Encoding;


class RskFile {

    const FORMAT = '1.1';

    const FILE_CACHE_ROOT = Config::CACHE_ROOT.'/rsk-files';

    protected $algUID;
    protected $contextId = -1;
    protected $algorithmId = 0;
    protected $answers = [];
    protected $infoData = [];
    protected $linkType = 1;

    protected $files = [];

    protected $fileName;


    public function __construct($algUID)
    {
        $this->algUID = $algUID;
    }


    public function getContextId() {
        return $this->contextId;
    }

    public function getAlgorithmId() {
        return $this->algorithmId;
    }

    public function getAnswers() {
        return $this->answers;
    }

    public function getInfoData() {
        return $this->infoData;
    }

    public function getLinkType() {
        return $this->linkType;
    }


    public function setContextId($id) {
        $this->contextId = $id;
    }

    public function setAlgorithmId($id) {
        $this->algorithmId = $id;
    }

    public function setLinkType($id) {
        $this->linkType = $id;
    }


    public function getFiles() {
        return $this->files;
    }


    public function setAnswers($answers) {
        foreach ($answers as $id => $answer) {
            $this->answers[$id] = $answer;
        }
    }

    public function setInfoData($infoData) {
        foreach ($infoData as $id => $info) {
            $this->infoData[$id] = $info;
        }
    }

    public function addFile($webId, $name, $path) {
        if (!file_exists($path))
            return false;

        $pathParts = pathinfo($path);

        $this->files[$webId] = [
            'name' => $name,
            'webId' => $webId,
            'path' => $path,
            'zipName' => $webId.'.'.$pathParts['extension'],
        ];

        return true;
    }



    public function outputs() {
        if (!$this->fileName)
            throw new \Exception("File wasn't created");

        $fileSize = filesize($this->fileName);

        header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
        header("Cache-Control: public"); // needed for internet explorer
        header("Content-Type: application/octet-stream");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: ".$fileSize);

        header("Content-Disposition: attachment; filename=expertise.rsk");

        readfile($this->fileName);
    }


    static public function fromUploadedFile($tmpName) {

        $rskFile = new RskFile(null);

        $name = "rsk-file-new-".uniqid().'.rsk';
        $rskFile->fileName = self::FILE_CACHE_ROOT.'/'.$name;

        if (!file_exists($tmpName))
            throw new \Exception("File not Exists {$tmpName}");

        if (!@rename($tmpName, $rskFile->fileName))
            throw new \Exception("Can't move uploaded file - ".error_get_last()['message']);


        $zip = new \ZipArchive();

        if ($zip->open($rskFile->fileName)!==TRUE)
            throw new \Exception("Can't open archive file");


        $format = $zip->getFromName('format.txt');

        if ($format!==self::FORMAT)
            throw new \Exception("Invalid file format");


        $json = $zip->getFromName('algorithm.json');
        $rskFile->loadAlgorithmJSON($json);

        $zip->close();

        return $rskFile;
    }


    public function extractFile($zipName) {
        $zip = new \ZipArchive();

        if ($zip->open($this->fileName)!==TRUE)
            throw new \Exception("Can't open archive file");

        $fileName = self::FILE_CACHE_ROOT.'/'.$zipName.'.'.uniqid();

        $fileBody = $zip->getFromName($zipName);
        $zip->close();

        file_put_contents($fileName, $fileBody);

        return $fileName;
    }


    public function create() {
        $name = "rsk-file-{$this->algUID}-".uniqid().'.rsk';
        $this->fileName = self::FILE_CACHE_ROOT.'/'.$name;

        $zip = new \ZipArchive();

        if ($zip->open($this->fileName, \ZipArchive::CREATE)!==TRUE)
            throw new \Exception("Can't create archive file");


        $zip->addFromString('format.txt', self::FORMAT);
        $zip->addFromString('algorithm.json', $this->getAlgorithmJSON());


        foreach ($this->files as $file) {
            $zip->addFile($file['path'], $file['zipName']);
        }

        $zip->close();
    }

    public function drop() {
        @unlink($this->fileName);
    }

    protected function getAlgorithmJSON() {
        $data = [];

        $data['contextId'] = $this->contextId;
        $data['answers'] = $this->answers;
        $data['infoData'] = $this->infoData;
        $data['algorithmId'] = $this->algorithmId;
        $data['linkType'] = $this->linkType;
        $data['files'] = [];

        foreach ($this->files as $file) {
            $data['files'][] = [
                'name' => $file['name'],
                'webId' => $file['webId'],
                'zipName' => $file['zipName'],
            ];
        }

        $result = json_encode($data);

        if (is_null($result))
            throw new \Exception("Can't encode algorithm data");

        return $result;
    }


    protected function loadAlgorithmJSON($json) {

        $data = json_decode($json, true);

        $this->contextId = $data['contextId'];
        $this->answers = $data['answers'];
        $this->infoData = $data['infoData'];
        $this->algorithmId = $data['algorithmId'];
        $this->linkType = $data['linkType'];

        foreach ($data['files'] as $file) {
            $this->files[$file['webId']] = [
                'name' => $file['name'],
                'webId' => $file['webId'],
                'zipName' => $file['zipName'],
            ];
        }
    }

}