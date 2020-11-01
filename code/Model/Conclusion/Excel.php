<?php

namespace Model\Conclusion;

use Infr\Config;
use Infr\Template;
use Model\Conclusion;
use PT\ConclusionId;

class Excel extends Conclusion
{
    protected $fileCacheDir = SERVICE_ROOT.'/cache/files/conclusion/xlsx';
    protected $tempFileDir = SERVICE_ROOT.'/cache/temp';

    protected $xmlPath = 'xl/sharedStrings.xml';
    protected $fileExt = 'xlsx';


    public function render($inGroup = false)
    {
        return parent::renderFile();
    }

    protected function getTemplate()
    {
        return new Template\XML($this->xmlFile);
    }


    public function outputExcel() {
        $content = $this->render();

        $fileName = $this->_header.'.xlsx';
        $fileSize = strlen($content);

        ob_clean();
        header($_SERVER["SERVER_PROTOCOL"].' 200 OK');
        header('Cache-Control: public');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Length: '.$fileSize);
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        echo $content;
    }


    public function outputPDF() {
        throw new \Exception("PDF output not implemented");
    }

    public function outputDocx() {
        throw new \Exception("Docx output not implemented");
    }

}