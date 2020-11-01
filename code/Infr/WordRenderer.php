<?php

namespace Infr;

set_time_limit(120);
ini_set('memory_limit','256M');

if (!defined('IMAGETYPE_WEBP'))
    define('IMAGETYPE_WEBP', 18);



class WordRenderer {

    static protected $tags;

    protected $tempDir = SERVICE_ROOT.'/cache/temp';

    /**
     * @var WordRendererImage[]
     */
    protected $imageMap = [];

    protected $id;

    protected $cacheDir;

    protected $templateDir;
    protected $templateFile;

    protected $cmplDir;
    protected $imgDir;
    protected $xmlDir;


    protected $clipImage = false;
    protected $stretchSmallImage = false;
    protected $imageMatchSize = true;
    protected $imageFillColor = [255, 255, 255];


    static public function  prepareWordFile($filePath) {

        try {
            $zip = new \ZipArchive;

            if ($zip->open($filePath) === TRUE) {
                $num = $zip->numFiles;

                for ($i=0; $i<$num; $i++) {
                    $arcName = $zip->getNameIndex($i);

                    if (($arcName=='word/document.xml')
                        or (substr_compare($arcName, 'word/header', 0, 11)==0)
                        or (substr_compare($arcName, 'word/footer', 0, 11)==0)) {


                        $str = $zip->getFromName($arcName);
                        $str = self::arrangeTags($str);
                        $zip->addFromString($arcName, $str);
                    }
                }

                $zip->close();

            } else {
                throw new \Exception('Can\'t open archive');
            }

        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    static protected function arrangeTags($text) {

        $text = preg_replace_callback('~\{[^\}]+\}~',
            function ($matches) {
                $struct = $matches[0];
                self::$tags = '';

                $struct = preg_replace_callback('~\<[^\>]+\>~',
                    function ($m) {
                        global $tags;
                        self::$tags .= $m[0];
                        return '';
                    },
                    $struct
                );

                $struct = html_entity_decode($struct, ENT_QUOTES | ENT_HTML5);
                $struct .= self::$tags;

                return $struct;
            },
            $text
        );

        return $text;
    }




    public function __construct($id, $updated, $cacheDir)
    {
        $this->id = $id;
        $this->cacheDir = $cacheDir;

        $this->templateDir = $this->cacheDir.'/'.$id.'_' .$updated;
        $this->templateFile = $this->templateDir.'/template.docx';

        $this->xmlDir = $this->templateDir.'/xml';
        $this->cmplDir = $this->templateDir.'/cmpl';
        $this->imgDir = $this->templateDir.'/img';

    }



    public function isCached() {
        return is_dir($this->templateDir)?true:false;
    }

    /**
     * @param $fileBody
     * @throws \Exception
     */

    public function cache($fileBody) {
        if ($this->isCached())
            return;


        $this->removePreviousTemplates();

        mkdir($this->templateDir, 0777);
        @chmod($this->templateDir, 0777);

        mkdir($this->xmlDir, 0777);
        @chmod($this->xmlDir, 0777);

        mkdir($this->cmplDir, 0777);
        @chmod($this->cmplDir, 0777);

        mkdir($this->imgDir, 0777);
        @chmod($this->imgDir, 0777);


        file_put_contents($this->templateFile, $fileBody);



        $zip = new \ZipArchive;

        if ($zip->open($this->templateFile) === TRUE) {
            $num = $zip->numFiles;

            for ($i=0; $i<$num; $i++) {
                $arcName = $zip->getNameIndex($i);

                if (($arcName=='word/document.xml')
                    or (substr_compare($arcName, 'word/header', 0, 11)==0)
                    or (substr_compare($arcName, 'word/footer', 0, 11)==0)) {

                    $fileName =  $this->xmlDir.'/'.substr($arcName, 5);
                    $fileBody =  $zip->getFromName($arcName);

                    file_put_contents($fileName, $fileBody);
                }
            }

            $zip->close();

        } else {
            throw new \Exception('Can\'t read template file: '.$this->templateFile);
        }

    }



    protected function removePreviousTemplates()
    {
        $dirs = glob($this->cacheDir.'/'.$this->id.'_*');

        foreach ($dirs as $dir) {
            $this->deleteDir($dir);
        }
    }


    protected function deleteDir($dirName) {
        if (is_dir($dirName))
            $dirHandle = opendir($dirName);

        if (!$dirHandle)
            return false;

        while($file = readdir($dirHandle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirName."/".$file))
                    unlink($dirName."/".$file);
                else
                    $this->deleteDir($dirName.'/'.$file);
            }
        }
        closedir($dirHandle);
        rmdir($dirName);

        return true;
    }





    /**
     * @return string
     * @throws \Exception
     */
    public function render() {

        $resultFile = $this->tempDir.'/wr_result_'.$this->id.'_'.uniqid().'.docx';

        if (!copy($this->templateFile ,$resultFile))
            throw new \Exception("Can't copy template file from {$this->templateFile} to {$resultFile}");


        $zip = new \ZipArchive;

        if ($zip->open($resultFile) !== TRUE) {
            unlink($resultFile);
            throw new \Exception('Can\'t read template file: '.$resultFile);
        }

        $files = scandir($this->xmlDir);

        foreach ($files as $file) {
            $filePath = $this->xmlDir.'/'.$file;

            if (!is_file($filePath))
                continue;

            $fileBody = $this->xmlRender($filePath);
            $zip->addFromString('word/'.$file, $fileBody);
        }

        if (count($this->imageMap)) {
            $relXml = $zip->getFromName('word/_rels/document.xml.rels');

            $this->xmlExtractImageNames($relXml);

            foreach ($this->imageMap as $rel=>$image) {
                if (!$image->download($this->imgDir))
                    continue;

                $srcName = 'word/'.$image->sourceName;

                if (!$image->setSize($zip->getFromName($srcName)))
                    continue;

                if (!$image->resize($this->clipImage, $this->stretchSmallImage, $this->imageMatchSize, $this->imageFillColor))
                    continue;

                if ($image->sourceName != $image->targetName)
                    str_replace($image->sourceName, $image->targetName, $relXml);


                $zip->addFile($image->fileName, $srcName);
            }

            $zip->addFromString('word/_rels/document.xml.rels', $relXml);
        }

        $zip->close();

        foreach ($this->imageMap as $rel=>$image)
            $image->delete();


        $fileBody = file_get_contents($resultFile);
        unlink($resultFile);

        return $fileBody;
    }



    protected function xmlRender($xmlFile) {

        $templ = new Template\XML($xmlFile);
        $templ->_compile_dir = $this->cmplDir;

        $xml = $templ->parse();
        $xml = preg_replace('~<\!--[^>]*-->~', '', $xml);

        if ($templ->needLoadImage()) {
            $xml = $this->xmlExtractImageRel($xml, $templ->loadImages());
        }

        return $xml;

    }


    protected function xmlExtractImageRel($xml, $urls) {
        $images = [];

        preg_match_all('~\<w\:drawing\>.*\</w\:drawing\>~U', $xml, $images);

        foreach ($images[0] as $image) {
            $m = [];

            if (!preg_match('~\{\@loadImage\((\d+)\)\}~', $image, $m))
                continue;

            $infoId = (int)$m[1];

            if (!preg_match('~r\:embed\=\"(\w+)\"~', $image, $m))
                continue;

            $map = new WordRendererImage();
            $map->relId = $m[1];
            $map->infoId = $infoId;
            $map->setUrl($urls[$infoId]);

            $this->imageMap[$map->relId] = $map;
        }

        $xml = preg_replace('~\{\@loadImage\((\d+)\)\}~', '', $xml);
        return $xml;
    }


    protected function xmlExtractImageNames($xml)
    {
        $relations = [];

        preg_match_all('~\<Relationship\s+Id\=\"(\w+)\".*Target\=\"([\w./]+)\".*\/\>~U', $xml, $relations);

        foreach ($relations[1] as $k=>$rel) {

            if (isset($this->imageMap[$rel]))
                $this->imageMap[$rel]->sourceName = $relations[2][$k];
        }
    }



}


class WordRendererImage {

    const JPEG_QUALITY = 90;

    public $relId;
    public $infoId;

    public $url;
    public $fileName;
    public $downloaded = false;

    public $sourceName;
    public $targetName;

    public $width;
    public $height;

    public $type;

    public function setSize($sourceBody) {
        $size = getimagesizefromstring($sourceBody);

        if ($size===false)
            return false;

        $this->width = $size[0];
        $this->height = $size[1];

        return true;
    }


    public function setUrl($url) {
        if (!strlen($url))
            return;

        $url = parse_url($url);
        $url['host'] = idn_to_ascii($url['host'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        $url = (isset($url['scheme'])?$url['scheme'].'://':'//').$url['host'].(isset($url['path'])?$url['path']:'/').(isset($url['query'])?'?'.$url['query']:'').(isset($url['fragment'])?'#'.$url['fragment']:'');
        
        if (filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)===false)
            return;

        $this->url = $url;
    }

    public function download($imageDir) {
        $this->downloaded = false;

        if (!isset($this->url) or $this->downloaded)
            return false;

        $this->fileName = $imageDir.'/'.$this->relId.'_'.uniqid();

        if (!@copy($this->url, $this->fileName))
            return false;

        $this->downloaded = true;

        return true;
    }

    public function delete() {
        if ($this->downloaded)
            @unlink($this->fileName);
    }


    public function resize($clip, $stretchSmall, $matchSize, $fillColor = [255, 255, 255]) {

        if (!$this->width or !$this->height)
            return false;

        $image = $this->openImage($this->fileName);

        if ($image===false)
            return false;


        $srcim = $image['image'];
        $srcw = $image['width'];
        $srch = $image['height'];

        $srcx = 0;
        $srcy = 0;

        $winw = $srcw;
        $winh = $srch;


        if (($srcw < $this->width and $srch < $this->height) and !$stretchSmall) {

            $dstw = $srcw;
            $dsth = $srch;
        } else {
            if (!$clip) {

                $k = $this->width / $srcw;

                // подгоняем масштаб по ширине
                $dstw = $this->width;
                $dsth = round($srch * $k);

                if ($dsth > $this->height) {
                    //если вылезла по высоте - подгоняем по высоте
                    $k = $this->height / $srch;
                    $dstw = round($srcw * $k);
                    $dsth = $this->height;
                }
            } else {

                $k = $this->width / $srcw;

                $dstw = $this->width;
                $dsth = round($srch * $k);

                if ($dsth < $this->height) {

                    $k = $this->height / $srch;
                    $dstw = $this->width;
                    $dsth = $this->height;

                    $winw = round($this->width / $k);
                    $srcx = round(($srcw - $winw) / 2);
                } else {
                    $dsth = $this->height;

                    $winh = round($this->height / $k);
                    $srcy = round(($srch - $winh) / 2);
                }
            }
        }

        if ($matchSize) {
            // matchSize для png с прозрачным фоном приведет к тому,
            // что прозрачность будет заменена фоном с цветом указанным в imageFillColor
            $outim = imagecreatetruecolor($this->width, $this->height);

            $color = imagecolorallocatealpha($outim, $fillColor[0], $fillColor[1], $fillColor[2], 0);
            imagefilledrectangle($outim, 0, 0, $this->width, $this->height, $color);

            $neww = $this->width;
            $newh = $this->height;

        } else {
            $outim = imagecreatetruecolor($dstw, $dsth);

            $neww = $dstw;
            $newh = $dsth;

            imagesavealpha($outim, true);
            imagefill($outim, 0, 0, imagecolorallocatealpha($outim, 0, 0, 0, 127));
        }

        imagecopyresampled($outim, $srcim, ($neww - $dstw) / 2, ($newh - $dsth) / 2, $srcx, $srcy, $dstw, $dsth, $winw, $winh);
        imagedestroy($srcim);

        $done = $this->saveImage($outim);

        imagedestroy($outim);

        return $done;
    }



    protected function openImage($imageFile) {

        $info = @getimagesize($imageFile);

        if ($info===false)
            return false;

        $srcw = $info[0];
        $srch = $info[1];

        if (($srcw == 0) or ($srch == 0)) {
            return false;
        }

        $this->type = $info[2];

        switch ($info[2]) {
            case IMAGETYPE_GIF:
                $srcim = imagecreatefromgif($imageFile);
                break;
            case IMAGETYPE_JPEG:
            case IMAGETYPE_JPEG2000:
                $srcim = imagecreatefromjpeg($imageFile);
                break;
            case IMAGETYPE_PNG:
                $srcim = imagecreatefrompng($imageFile);
                break;
            case IMAGETYPE_WBMP:
                $srcim = imagecreatefromwbmp($imageFile);
                break;
            case IMAGETYPE_XBM:
                $srcim = imagecreatefromxbm($imageFile);
                break;
            case IMAGETYPE_WEBP:
                $srcim = imagecreatefromwebp($imageFile);
                break;
            //case IMAGETYPE_BMP:
            //    $srcim = imagecreatefrombmp($imageFile); // функция только с php 7.2
            //    break;
            default:
                $srcim = imagecreatefromstring(file_get_contents($imageFile));
        }

        if ($srcim===false)
            return false;

        return [
                'image' => $srcim,
                'width' => $srcw,
                'height' => $srch
        ];
    }


    protected function saveImage($imageRes) {

        $parts = pathinfo($this->sourceName);

        switch ($this->type) {
            case IMAGETYPE_GIF:
                $this->targetName = $parts['filename'].'.gif';
                return imagegif($imageRes, $this->fileName);
                break;
            case IMAGETYPE_PNG:
                $this->targetName = $parts['filename'].'.png';
                return imagepng($imageRes, $this->fileName);
                break;
            case IMAGETYPE_JPEG:
            default:
                $this->targetName = $parts['filename'].'.jpeg';
                imageinterlace($imageRes, 1);
                return imagejpeg($imageRes, $this->fileName, self::JPEG_QUALITY);
            break;
        }
    }

}