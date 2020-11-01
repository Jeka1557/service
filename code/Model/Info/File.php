<?php

namespace Model\Info;

use Infr;
use Infr\Config;
use Lib\Exception;
use Model\Info;

/**
 * Class File
 *
 * @property-read string $fileUID
 * @property-read string $fileWebID
 * @property-read string $fileName
 *
 */

class File extends Info {

    const TMPL_NAME = 'File';
    const TMPL_ROOT = SERVICE_TMPL_ROOT;

    const UID_PREFIX = 'info-file';
    const FILE_CACHE_ROOT = Config::FILE_CACHE_ROOT;

    const FILE_EXT = 'dat';
    const FILE_MIME_TYPE = 'application/octet-stream';


    protected $_fileUID;
    protected $_fileWebID;

    protected $_fileName;
    protected $_fileExt;

    protected $_errorMessage = 'Загрузите файл';


    static public function checkWebID($webID) {
        if (preg_match('~^'.static::UID_PREFIX.'-\d+-\d+(_\d+)?$~', $webID))
            return true;
        else
            return false;
    }

    static public function webID2UID($webID, $algUID) {
        $fileUID = str_replace(static::UID_PREFIX.'-', static::UID_PREFIX.'-'.$algUID.'-', $webID);

        if (!preg_match('~^'.static::UID_PREFIX.'-\d+-\d+-\d+(_\d+)?$~', $fileUID))
            throw new \Exception("Invalid fileUID {$fileUID}");

        return $fileUID;
    }


    static public function filePath($fileUID, $type) {
        switch ($type) {
            case 'main':
                return static::FILE_CACHE_ROOT.'/'.$fileUID.'.'.static::FILE_EXT;
            case 'json':
                return static::FILE_CACHE_ROOT.'/'.$fileUID.'.json';
        }
    }


    static public function uploadFile($algUID, $webID, $tmpName) {

        $fileUID = static::webID2UID($webID, $algUID);

        $jsonFile = static::filePath($fileUID, 'json');
        $mainFile = static::filePath($fileUID, 'main');

        if (file_exists($jsonFile))
            unlink($jsonFile);

        if (file_exists($mainFile))
            unlink($mainFile);


        if (!@rename($tmpName, $mainFile))
            throw new \Exception("Can't move uploaded file");
    }


    static public function downloadFile($algUID, $webID, $name = null) {

        $fileUID = static::webID2UID($webID, $algUID);
        $filePath = static::filePath($fileUID, 'main');

        if (!file_exists($filePath))
            throw new \Exception("File not found");

        $fileSize = filesize($filePath);

        header($_SERVER["SERVER_PROTOCOL"].' 200 OK');
        header('Cache-Control: public'); // needed for internet explorer
        header('Content-Type: '.static::FILE_MIME_TYPE);
        header('Content-Transfer-Encoding: Binary');
        header('Content-Length: '.$fileSize);

        header('Content-Disposition: attachment; filename="'.(isset($name)?$name:$webID).'"');

        readfile($filePath);
    }


    public function initUID($algUID, $nodeId) {
        $this->_fileUID = static::UID_PREFIX."-{$algUID}-{$nodeId}-{$this->_extId}";
        $this->_fileWebID = static::UID_PREFIX."-{$nodeId}-{$this->_extId}";
    }


    public function getFilePath() {
        return  static::filePath($this->_fileUID, 'main');
    }

    public function getAlert() {
        if ($this->_hasError)
            return 'error';
        else
            return 'success';
    }






    protected function clearData(&$data)
    {
        try {
            if (isset($data['file_source'])) {
                $this->copyFile($data['file_source']);
                $data['file_web_id'] = $this->_fileWebID;
            }

        } catch (\Exception $e) {}

        if (isset($data['file_web_id']))
            $data['file_web_id'] = trim($data['file_web_id']);

        if (isset($data['file_name']))
            $data['file_name'] = trim($data['file_name']);
    }

    protected function isEmptyData($data) {

        if (!isset($data['file_web_id']) or !strlen($data['file_web_id']))
            return true;

        if (!isset($data['file_name']) or !strlen($data['file_name']))
            return true;

        return false;
    }

    protected function applyData($data) {
        $this->_fileName = $data['file_name'];

        $pathParts = pathinfo($this->_fileName);
        $this->_fileExt =  isset($pathParts['extension'])?$pathParts['extension']:'';
    }

    protected function applyValue($value) {
        $this->_fileName = $value['file_name'];

        $pathParts = pathinfo($this->_fileName);
        $this->_fileExt =  $pathParts['extension'];

        $this->loadDocument();
    }

    protected function validate($data) {

        if ($data['file_web_id']!=$this->_fileWebID)
            return false;

        if (!strlen($data['file_name']))
            return false;


        if (!file_exists(self::filePath($this->_fileUID, 'main')))
            return false;

        return true;
    }

    protected function format() {
        return "{$this->_fileName} (WebID: {$this->_fileWebID})";
    }



    


    public function render($inGroup = false) {

        if (self::$renderMode==self::RENDER_MODE_BST4) {
            $tmplName = static::TMPL_NAME.'2';

        } elseif (self::$renderMode==self::RENDER_MODE_WC) {
            $tmplName = 'wc/'.static::TMPL_NAME;
        } else {
            $tmplName = static::TMPL_NAME;
        }

        return $this->renderTemplate('Info', $tmplName, [
            'info' => $this,
            'inGroup' => $inGroup,
        ]);
    }


    protected function loadDocument() { }


    public function copyFile($fileName) {

        $jsonFile = $this->filePath($this->_fileUID, 'json');
        $mainFile = $this->filePath($this->_fileUID, 'main');

        if (file_exists($jsonFile))
            unlink($jsonFile);

        if (file_exists($mainFile))
            unlink($mainFile);

        if (!@copy($fileName, $mainFile))
            throw new \Exception("Can't copy uploaded file");

    }


    static public function uploadFileGeneral($webID, $tmpFile, $algUID) {

        switch (true) {
            case (defined('EGRUL_PLUGIN') and Egrul::checkWebID($webID)):
                Egrul::uploadFile($algUID, $webID, $tmpFile);
            break;

            case (defined('EXL_PLUGIN') and Excel::checkWebID($webID)):
                Excel::uploadFile($algUID, $webID, $tmpFile);
            break;

            case (File\Exl::checkWebID($webID)):
                File\Exl::uploadFile($algUID, $webID, $tmpFile);
            break;

            case (File::checkWebID($webID)):
                File::uploadFile($algUID, $webID, $tmpFile);
            break;

            default:
                throw new \Exception("Invalid file type");
        }
    }


    static public function downloadFileGeneral($webID, $algUID, $name = null) {

        switch (true) {
            case (defined('EGRUL_PLUGIN') and Egrul::checkWebID($webID)):
                Egrul::downloadFile($algUID, $webID, $name);
            break;

            case (defined('EXL_PLUGIN') and Excel::checkWebID($webID)):
                Excel::downloadFile($algUID, $webID, $name);
            break;

            case (File\Exl::checkWebID($webID)):
                File\Exl::downloadFile($algUID, $webID, $name);
            break;

            case (File::checkWebID($webID)):
                File::downloadFile($algUID, $webID, $name);
            break;

            default:
                throw new \Exception("Invalid file type");
        }
    }
}