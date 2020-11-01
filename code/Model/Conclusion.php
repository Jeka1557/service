<?php


namespace Model;

use Infr\Template;
use Model\Conclusion\Word;
use PT\ConclusionId;
use PT\ConclusionType;
use Model\Conclusion\Text;
use Model\Conclusion\Excel;
use Model\Exception;


/**
 * Class Conclusion
 *
 * @property-read $id
 * @property-read $header
 * @property-read $text
 *
 */

class Conclusion extends DictEntity {

    protected $_id;
    protected $_header;
    protected $_text;
    protected $_type;

    protected $_textUpdated;

    /**
     * @var \Model\Storage\Conclusion
     */
    protected $storage;


    protected $fileCacheDir;
    protected $tempFileDir;

    protected $fileExt;

    protected $templateFile;
    protected $xmlFile;

    protected $xmlPath;



    protected function __construct() {}


    static public function newFromArray($data = []) {
        /* @var Risk $entity */
        $entity = parent::newFromArray();

        $entity->_id = static::castVar($data['id'],'PT\ConclusionId');
        $entity->_header = static::castVar($data['header'],'TP\Text\Plain');
        $entity->_type = static::castVar($data['type'],'PT\ConclusionType');

        $entity->_entityType = \PT\EntityType::CONCLUSION();

        $entity->_extId =  "{$entity->_id}".($data['copyId']>0?"_{$data['copyId']}":'');

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        return $entity;
    }


    protected function applyContext($contextData) {
        $context = parent::applyContext($contextData);

        if (!is_null($context))
            $this->_textUpdated = $context['updated'];

        return $context;
    }


    /**
     * Возвращает шаблон заключения
     *
     * @return Template\DB
     *
     */
    protected function getTemplate()
    {
        return new Template\DB('Conclusion', $this->_id, $this->_textUpdated, $this->_text);
    }


    public function render($inGroup = false) {
        $templFile = $this->getTemplate();

        $html = $templFile->parse();

        $html = preg_replace('~<\!--[^>]*-->~', '', $html);

        if (self::$renderMode==self::RENDER_MODE_ARRAY) {
            $result = [
                'text' => $html,
            ];

            return $result;

        } else {
            return $html;
        }
    }


    public static function newEntity(ConclusionType $type, $data) {
        $data['conclusionType'] = $type;

        switch ($type->val()) {
            case ConclusionType::TEXT:
                return Text::newFromArray($data);
            case ConclusionType::EXCEL:
                return Excel::newFromArray($data);
            case ConclusionType::WORD:
                return Word::newFromArray($data);
            default:
                throw new \Exception("Storage: unknown conclusion type");
        }
    }


    public function outputPDF() {
        $content = $this->render();

        ob_start();
        require(SERVICE_TMPL_ROOT."/conclusion-pdf.phtml");
        $html = ob_get_clean();

        $mpdf = new \Mpdf\Mpdf();

        $mpdf->WriteHTML($html);
        $mpdf->Output('conclusion.pdf', \Mpdf\Output\Destination::INLINE);
        $mpdf->cleanup();
    }

    public function outputDocx() {
        $fileName = $this->_header.'.docx';

        $content = $this->render();
        $content = str_replace('https://', 'http://', $content);  // У libreoffice есть проблемы с загрузкой картинок по https на одном из серверов, по этому обрезаем до http
        $content = str_replace('src="/fcache/', 'src="http://expert.riskover.ru/fcache/', $content); // заменяем внутренние ссылки на картинки

        ob_start();
        require(SERVICE_TMPL_ROOT."/conclusion-pdf.phtml");
        $html = ob_get_clean();

        $doc = \HTML2DOCX::convert($html);
        $fileSize = strlen($doc);

        header($_SERVER["SERVER_PROTOCOL"].' 200 OK');
        header('Cache-Control: public'); // needed for internet explorer
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Length: '.$fileSize);
        header('Content-Disposition: attachment; filename="'.$fileName.'"');

        echo $doc;
    }

    public function outputExcel() {
        throw new \Exception("Excel output not implemented");
    }


    public function setStorage(\Model\Storage\Conclusion $storage) {
        $this->storage = $storage;
    }


    protected function removeTemplateFiles()
    {
        $files = glob($this->fileCacheDir.'/*'.$this->fileExt);
        $files = array_merge($files, glob($this->fileCacheDir.'/*.xml'));

        foreach ($files as $file) {
            if (is_file($file))
                unlink($file);
        }
    }


    /**
     * @throws \Exception
     */
    protected function cacheTemplateFile() {
        if (!isset($this->storage))
            throw new \Exception("Conclusion storage is not set");

        $updated = $this->storage->getFileUpdated(new ConclusionId($this->_id));

        $this->templateFile = $this->fileCacheDir.'/'.$this->_id.'_' .$updated.'.'.$this->fileExt;
        $this->xmlFile = $this->fileCacheDir.'/'.$this->_id.'_' .$updated.'.xml';


        if (!file_exists($this->templateFile)) {
            $this->removeTemplateFiles();

            $fileBody = $this->storage->getFileBody(new ConclusionId($this->_id));
            file_put_contents($this->templateFile, $fileBody);
        }


        if (!file_exists($this->xmlFile)) {
            $zip = new \ZipArchive;

            if ($zip->open($this->templateFile) === TRUE) {
                $xmlFileSource = $zip->getFromName($this->xmlPath);

                $zip->close();
                file_put_contents($this->xmlFile, $xmlFileSource);

            } else {
                throw new \Exception('Can\'t read template file: '.$this->templateFile);
            }
        }
    }


    /**
     * @return string
     * @throws \Exception
     */
    protected function renderFile() {
        set_time_limit(120);
        ini_set('memory_limit','256M');

        $this->cacheTemplateFile();
        $xmlfileBody =  self::render();


        $tempFile = $this->tempFileDir.'/'.$this->_id.'_'.uniqid().'.'.$this->fileExt;

        if (!copy($this->templateFile ,$tempFile))
            throw new \Exception("Can't copy template file from {$this->templateFile} to {$tempFile}");

        $zip = new \ZipArchive;
        if ($zip->open($tempFile) === TRUE) {
            $zip->addFromString ($this->xmlPath, $xmlfileBody);
            $zip->close();

        } else {
            unlink($tempFile);
            throw new \Exception('Can\'t read template file: '.$tempFile);
        }

        $fileBody = file_get_contents($tempFile);
        unlink($tempFile);

        return $fileBody;
    }
}