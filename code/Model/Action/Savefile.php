<?php

namespace Model\Action;

use Infr;
use \Infr\Config;



class Savefile extends \Model\Action {

    protected $_fileName;
    protected $_conclusionId;

    protected $_conclusionText = '';

    protected $_algorithmHeader;



    static public function newFromArray($data = []) {
        /* @var \Model\Action\Savefile $entity */
        $entity = parent::newFromArray($data);

        $entity->_conclusionId = new \PT\ConclusionId($data['settings']['conclusion_id']);

        $entity->_fileName = ltrim(trim($data['settings']['file_name']), '/');


        return $entity;
    }


    protected function initAction() {

        $conclusion = $this->_executor->getResult()->getConclusion($this->_conclusionId);

        if (!$conclusion)
            throw new \Exception("Conclusion id: {$this->_conclusionId} not found");

        $this->_executor->getResult()->assignTemplate();

        $conclusion->setStorage($this->_executor->getConclusionStorage());
        $this->_conclusionText = $conclusion->render();

        $this->_fileName = str_replace(['{$date}', '{$time}'], [date('Y-m-d'), date('H-i-s')], $this->_fileName);

        parent::initAction();
    }

    protected function doAction() {

        if (!Config::$conclusionSaveAllow)
            return true;

        try {

            $parts = pathinfo($this->_fileName);

            $this->mkDir($parts['dirname'], Config::SAVED_CONCLUSION_ROOT);

            $file = Config::SAVED_CONCLUSION_ROOT.'/'.$this->_fileName;


            if (!file_put_contents($file, $this->_conclusionText))
                throw new \Exception("Can't save file {$file}");

        } catch (\Exception $e) {
            return false;
        }

        return true;
    }


    protected function makeHash() {
        return md5($this->_conclusionText);
    }


    protected function mkDir($path, $rootDir) {
        if ($path =='.')
            return;

        $dirs = explode('/', $path);

        $currentDir = $rootDir;

        foreach ($dirs as $dir) {
            if ($dir=='..')
                throw new \Exception("Invalid directory pattern .. ");

            $currentDir .= '/'.$dir;

            if (!is_dir($currentDir)) {
                if (!mkdir($currentDir))
                    throw new \Exception("Can't create dir {$currentDir}");
            }
        }
    }

}