<?php
define('SERVICE_ROOT', dirname(dirname(__FILE__)));
define('SERVICE_TMPL_ROOT', SERVICE_ROOT.'/templ');

include_once SERVICE_ROOT.'/code/Infr/Utils.php';
include_once SERVICE_ROOT.'/code/Autoload.php';

if (!file_exists(SERVICE_ROOT.'/config.php'))
    exit('Project config not found');

RskAutoload::registerDirs(array(SERVICE_ROOT.'/code'));


\Infr\Config::load();

if ($_SERVER['REQUEST_URI']!='/ping')
    \Model\LinkRenderer::instance(\Infr\Config::getDSN());
  

print \Infr\Controller::run();

