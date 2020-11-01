<?php

namespace Model\Conclusion;

use Infr\Template;
use Infr\WordRenderer;
use Model\Conclusion;
use PT\ConclusionId;

class Word extends Conclusion
{
    protected $fileCacheDir = SERVICE_ROOT.'/cache/files/conclusion/docx';

    protected $xmlPath = 'word/document.xml';
    protected $fileExt = 'docx';



    protected function getTemplate()
    {
        return new Template\XML($this->xmlFile);
    }

    public function outputPDF() {

        $fileName = $this->_header.'.pdf';
        $content = $this->render();

        $doc = \HTML2DOCX::convDocx2PDF($content);

        ob_clean();
        $fileSize = strlen($doc);

        header($_SERVER["SERVER_PROTOCOL"].' 200 OK');
        header('Cache-Control: public'); // needed for internet explorer
        header('Content-Transfer-Encoding: Binary');
        header('Content-Length: '.$fileSize);
        header('Content-Disposition: inline; filename="'.$fileName.'"');
        header('Content-Type: application/pdf');

        echo $doc;
    }

    public function outputExcel() {
        throw new \Exception("Excel output not implemented");
    }

    public function outputDocx() {
        $content = $this->render();

        $fileName = $this->_header.'.docx';
        $fileSize = strlen($content);

        ob_clean();
        header($_SERVER["SERVER_PROTOCOL"].' 200 OK');
        header('Cache-Control: public');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Length: '.$fileSize);
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        echo $content;
    }


    static public function  prepareWordFile($filePath) {
        return WordRenderer::prepareWordFile($filePath);
    }


    /**
     * @param bool $inGroup
     * @return array|bool|false|string|string[]|null
     * @throws \Exception
     */

    public function render($inGroup = false) {
        if (!isset($this->storage))
            throw new \Exception("Conclusion storage is not set");

        $updated = $this->storage->getFileUpdated(new ConclusionId($this->_id));


        $renderer = new WordRenderer($this->_id, $updated, $this->fileCacheDir);

        if (!$renderer->isCached()) {
            $fileBody = $this->storage->getFileBody(new ConclusionId($this->_id));
            $renderer->cache($fileBody);
        }

        return  $renderer->render();
    }

}