<?php
define('SERVICE_ROOT', dirname(dirname(__FILE__)));
define('SERVICE_TMPL_ROOT', SERVICE_ROOT.'/templ');

include_once SERVICE_ROOT.'/code/Infr/Utils.php';
include_once SERVICE_ROOT.'/code/Autoload.php';
RskAutoload::registerDirs(array(SERVICE_ROOT.'/code'));

\Infr\Config::load();


if (isset($_GET['uri'])) {

    $config = array(
        'fileRoot' => '/',
        'cacheDir' => '/fcache',  // путь к папке с кешем
        'clsFileSource' => '\Infr\FileCacheSource', // драйвер для работы с файлами (На данный момент только LSFDbTableSource)
        'documentRoot' => SERVICE_ROOT.'/www',
        'dbSource' => [
            'tempRoot' => '/fcache',
            'clsFileTable' => '',
            'objFileTable' => new \Infr\Db\Content\Document(\Infr\Config::getDSN()),
        ]
    );

    $cache = new Lib\Infr\FileCache($config);
    $cache->debug(true);

    if (!$cache->outFile($_GET['uri'])) {
        header("HTTP/1.0 404 Not Found");
    }
} else {
    header("HTTP/1.0 404 Not Found");
}
