<?php

namespace Lib\Infr;


class FileCache {
    /**
     * Путь к начальной папке хранения файлов
     * @var string
     */
    protected $_fileRoot;
    /**
     * Путь к папке временного хранения генерируемых файлов
     * @var string
     */
    protected $_tmpRoot;
    /**
     * Путь к папке хранения кешируемых файлов
     * @var string
     */
    protected $_cacheDir;
    /**
     * Путь к папке - корню хранения файлов
     * @var string
     */
    protected $_documentRoot;
    /**
     * Имя класса - источника изображения из файла
     * @var string
     */
    protected $_fileSource;
    /**
     * Включён/выключен режим отладки
     * @var bool
     */
    protected $_debug;
    /**
     * Время кеширования файла
     * @var int
     */
    protected $_timeOut;
    /**
     * Задаёт режим отладки ошибок: объектный/процедурный
     * @var bool
     */
    protected $_throwExceptions = false;
    /**
     * Конструктор - считывает и задаёт конфигурацию
     * @param array $config Массив параметров
     */
    public function __construct($config = null) {
        if (!isset($config['fileRoot'])) {
            throw new \Exception("Не задан конфиг fileCache.fileRoot");
        }
        
        $this->_fileRoot = $config['fileRoot'];
        $this->_cacheDir = isset($config['cacheDir']) ? $config['cacheDir'] : '/fcache';
        $this->_timeOut = isset($config['timeOut']) ? $config['timeOut'] : 0;
        $this->_throwExceptions = !empty($config['throwExceptions']);
        $this->_documentRoot = $config['documentRoot'];

        $config['dbSource']['documentRoot'] = $this->_documentRoot;

        $fsClass = $config['clsFileSource'];
        $this->_fileSource = new $fsClass($config['dbSource']);
    }
    
    /**
     * Генерирует сообщение об ощибке в объектном/процедурном стиле
     * @param string $message Сообщение об ошибке

     */
    protected  function _debugMessage($message) {
        if ($this->_throwExceptions) {
            throw new \Exception($message);
        } else {
            trigger_error($message, E_USER_WARNING);
        }
    }


    /**
     * Включает/выключает режим отладки
     * @param bool $on Режим отладки (вкл./выкл.)

     */
    public function debug($on = true) {
        $this->_debug = $on;
    }
    
    /**
     * Эмуляция функции mime_content_type (Определение MIME Content-type файла)
     * 
     * @param string $filename
     */
    protected function _mimeContentType($filename) {
        $mimeTypes = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
        
            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
    
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
        
            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
        
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
        
            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
        
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $nameParts = explode('.', $filename);
        $ext = strtolower(array_pop($nameParts));
        
        if (isset($mimeTypes[$ext])) {
            return $mimeTypes[$ext];
            
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
            
        } else {
            return 'application/octet-stream';
        }
    }

    

    /**
     * Кеширует файл в указанном месте
     * @param string $srcPath Путь к исходнику файла
     * @param string $destName Имя файла на выходе
     * @param string $destBase Путь к папке файла на выходе
     * @return bool Результат кеширования файла
     */
    public function cacheFile($srcPath, $destName, $destBase) {
        $destFile = $this->_documentRoot . $this->_cacheDir . $destBase . '/' . $destName;
        
        $dir = dirname($destFile);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        
        if (!copy($srcPath, $destFile)) {
            $this->_debugMessage("Ошибка при копировании файла `{$destFile}`");
            return false;
        } else {
            chmod($destFile, 0777);
        }
        
        return true;
    }




    /**
     * Удаляет файл из директории кеширования
     * @param $name Имя удаляемого файла
     * @param $cat Используемая директория внутри директории кеширования

     */
    public function dropFile($name, $cat = null) {

        if (!is_null($cat)) {
            $dir = $this->_documentRoot.'/'.$this->_cacheDir."/{$cat}";
        } else {
            $dir = $this->_documentRoot.'/'.$this->_cacheDir;
        }

        $files = scandir($dir);

        foreach ($files as $file) {
            if (preg_match("/^{$name}_\d+x\d+\.\w+$/", $file)) {
                unlink($dir.'/'.$file);
            }
        }
    }

    /**
     * Выдаёт сообщение с заголовками HTTP,
     * если не удалось получить путь к файлу изображения.
     * @see LSF_Tools_ImageCache::cacheImageByUri()
     * @param string $message Выдаваемое при ошибке сообщение

     */
    protected function _call404() {
        header("HTTP/1.0 404 Not Found");
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        header('Content-Type: text/html; charset=UTF-8');
    }

    /**
     * Кеширует файл по указанному пути.
     * @param string $uri Путь для кеширования файла
     * @return mixed Путь к закешированному файлу либо false, если произошла ошибка
     */
    public function cacheFileByUri($uri) {            
        $info = $this->parseUri($uri);
        
        if (!$info) {
            $this->_debugMessage("Ошибка при разборе URI {$uri}");
            return false;
        }

        $filePath = $this->_fileSource
            ->getFilePath($info['fileName'], $info['fileExt'], $info['pathBase'], $info['revision']);                

        if (!$filePath) {
            $this->_call404();
            return false;
        }

        if (!$this->cacheFile($filePath, $info['uriFileName'], $info['pathBase'])) {
            return false;
        }

        return $this->_documentRoot . $uri;        
    }

    /**
     * Выдаёт информацию о пути к файлу
     * @param string $uri Путь к файлу
     * @return mixed Инофрмация о пути к файлу либо false, если путь задан неверно
     */
    public function parseUri($uri) {
        $out = array();
        $info = pathinfo($uri);
        $parts = explode('/',ltrim($info['dirname'],'/'));

        if ('/'.$parts[0] != $this->_cacheDir) {
            $this->_debugMessage("Начало URI /{$parts[0]} отличается от директории кэша {$this->_cacheDir}");
            return false;
        }
        
        if (!preg_match('/^(\d+)(\_(\d+))?\.(\w+)$/', $info['basename'], $m)) {
            $this->_debugMessage("Неверный формат файла {$info['basename']}");
            return false;
        }
        
        unset($parts[0]);
        $out['pathBase'] = '/' . implode('/', $parts);
        $out['fileName'] = (int)$m[1];
        $out['fileExt'] = $m[4];
        $out['revision'] = (int)$m[3];
        $out['uriFileName'] = $info['basename'];
        
        return $out;
    }


    /**
     * Стирает файлы в директории кеша,
     * для которых истекло время хранения,
     * задаваемое @see LSF_Tools_ImageCache::$_timeOut
     * @param string $directory Путь очищаемой директории

     */
    protected function _clearCacheDir($directory) {
        $dir = opendir($directory);
        $time = time();

        while (($file=readdir($dir))) {
            if (is_file($directory.'/'.$file)) {
                if ($this->_timeOut>0) {
                    $accTime = filectime($directory.'/'.$file);

                    if (($time-$accTime)>$this->_timeOut) {
                        unlink($directory.'/'.$file);
                    }
                }
            }
            elseif (is_dir($directory.'/'.$file) && ($file!='.') && ($file!='..')) {
                $this->_clearCacheDir($directory.'/'.$file);
            }
        }
        
        closedir($dir);        
    }
    
    /**
     * Выдаёт содержимое файла с заголовками HTTP
     * @param string $uri Путь к выдаваемому файлу
     * @return bool Результат выполнения метода
     */
    public function outFile($uri) {
        $file = $this->cacheFileByUri($uri);
        if (!$file) {
            return false;
        }
        
        $fileinfo = pathinfo($uri);
        
        header("Content-Type: " . $this->_mimeContentType($file));
        header("Content-Length: " . filesize($file));
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", filemtime($file))." GMT");                            
        header("Content-Disposition: attachment; filename=\"{$fileinfo['basename']}\"");
        header("Accept-Ranges: bytes");        

        readfile($file);

        return true;
    }

    /**
     * Очищает папку кеш по умолчанию.
     * @see LSF_Tools_FileCache::_clearCacheDir()

     */
    public function clearCache() {
        $this->_clearCacheDir($this->_documentRoot.$this->_cacheDir);
    }
}


