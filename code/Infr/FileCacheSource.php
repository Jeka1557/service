<?php
/**
 * Драйвер для кеширования файлов через FileCache получаемые из БД
 *
 */
namespace Infr;

use Lib\Infr\Db as DB;


class FileCacheSource {

    /**
     * Размещение временных файлов
     *
     * @var string
     */
    protected $_tempRoot = '/tmp';

    /**
     * Имя класса таблицы
     *
     * @var string
     */
    protected $_clsDbTable = false;

    /**
     * LSF_Db_Table объект для работы с таблицой
     *
     * @var \Infr\Db\Content\Document
     */
    protected $_objDbTable = null;

    /**
     * Путь к "Document root"
     *
     * @var string
     */
    protected $_documentRoot;

    /**
     * Путь к временному файлу
     *
     * @var string
     */
    protected $_tempFile;



    public function __construct($config) {
        $this->_tempRoot = $config['tempRoot'];
        $this->_objDbTable = $config['objFileTable'];
        $this->_documentRoot = $config['documentRoot'];

    }

    /**
     * Получить путь к файлу
     *
     * @param string $fileName
     * @param string $fileExt
     * @param string $pathBase
     * @return string
     */
    public function getFilePath($fileName, $fileExt, $pathBase, $fileId) {
        $table = $this->_objDbTable;

//        //имя файла конвертирую в win1251 для корректного чтения русских названий файлов
//        $fileName = iconv('utf-8', 'windows-1251', $fileName);
        $table->reset($table::COLUMNS)
              ->columns(array('id', 'file_ext', 'updated', 'file_body'))
              ->where('id', (int)$fileName)
              ->where('file_ext', $fileExt)
              ->where('updated', $fileId);

        $row = $table->execute()->fetchRow(Db::FETCH_ASSOC, 0, array('file_body' => Db::PARAM_BYTEA));

        if (!$row)
            throw new \Exception("No such record ". get_class($table)." id: {$fileName} ext: {$fileExt} updated: {$fileId}");

        $tempFile = $this->_documentRoot.$this->_tempRoot.'/'.$row['id'].'_'.rand();

        if (!$fp = fopen($tempFile,'w'))
            return false;

        fwrite($fp, $row['file_body']);
        fclose($fp);

        $this->_tempFile = $tempFile;

        return $tempFile;
    }

    /**
     * Удаление временного файла
     */
    public function __destruct() {
        if ($this->_tempFile) unlink($this->_tempFile);
    }
}

