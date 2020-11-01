<?php

namespace Model\Info\File;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader;
use PhpOffice\PhpSpreadsheet\Writer;
use TP\Exception;

/**
 * Class Exl
 * @package Model\Info
 *
 * @property-read $fileUID
 * @property-read $fileName
 *
 * @property-read $hasValue;
 *
 * @property-read $docParsed;
 */


class Exl extends \Model\Info\File {

    const UID_PREFIX = 'info-file_exl';

    const FILE_EXT = 'xlsx';
    const FILE_MIME_TYPE = 'vnd.ms-excel';

    protected $_docParsed;

    /**
     * @var  Spreadsheet
     */

    protected $spreadSheet;

    const DOC_PARSED_OK = 1;
    const DOC_PARSED_FAIL = 4;


    public function getAlert() {
        if ($this->_hasError)
            return 'error';

        switch ($this->_docParsed) {
            case self::DOC_PARSED_FAIL:
                return 'error';

            case self::DOC_PARSED_OK:
                return 'success';

            default:
                return '';
        }
    }


    protected function loadDocument() {

        $this->spreadSheet = null;

        $xlsFile = self::filePath($this->_fileUID, 'main');


        try {
            if (!file_exists($xlsFile))
                throw new \Exception("Excel file not uploaded");


            switch ($this->_fileExt) {
                case 'xls':
                    $reader = new Reader\Xls();
                break;
                case 'xlsx':
                    $reader = new Reader\Xlsx();
                break;
                default:
                    throw new \Exception("Invalid file type {$this->_fileExt}");
            }

            $reader->setReadDataOnly(true);

            $this->spreadSheet = @$reader->load($xlsFile);


            if ($this->spreadSheet->getSheetCount()==0)
                throw new \Exception("No active spreadsheet found");



        } catch (\Exception $e) {
            $this->_docParsed = self::DOC_PARSED_FAIL;
            return ;
        }

        $this->_docParsed = self::DOC_PARSED_OK;
    }

    protected function format() {
        return '';
    }


    public function __get($name) {

        if ($name=='dataText') {
            if ($this->_dataText == '')
                $this->_dataText = $this->generateHTML();

            return $this->_dataText;

        } else {
            return parent::__get($name);
        }
    }

/*
    protected function generateHTML() {
        $writer = new Writer\Html($this->spreadSheet);

        $styles = $writer->generateStyles(false);
        $styles = preg_replace('~\s*(.*)\n~', "#{$this->_fileUID} $1\n", $styles);

        $styles = "
        #{$this->_fileUID} {
              all: initial;
              * {
                all: unset;
              }
            }
        ";

        $html = $writer->generateSheetData();
        $html = iconv('UTF-8', 'windows-1251//TRANSLIT', $html);

        $pattern = '</style>';
        $pos = strpos($html, $pattern);
        $html = substr($html, $pos+strlen($pattern));

        $html = "<div id=\"{$this->_fileUID}\"><style>{$styles}</style>".$html."</div>";


        return $html;
    }
*/

    protected function generateHTML() {

        if (is_null($this->spreadSheet))
            return "";

        $writer = new Writer\Html($this->spreadSheet);

        $html = $writer->generateSheetData();

        $pattern = '</style>';
        $pos = strpos($html, $pattern);
        $html = substr($html, $pos + strlen($pattern));

        $html = "<div id=\"{$this->_fileUID}\" class=\"info-exl-file\">" . $html . "</div>";


        return $html;
    }
}