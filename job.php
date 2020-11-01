<?php
/** @var $jobClass \Infr\Job  */

set_time_limit(0);

define('SERVICE_ROOT', dirname(__FILE__));

include_once SERVICE_ROOT.'/code/Autoload.php';
RskAutoload::registerDirs(array(SERVICE_ROOT.'/code'));

\Infr\Config::load();

if (!isset($argv[1]))
    throw new \Exception("Unknown job");


$jobName = $argv[1];
$jobClass = '\\App\\Job\\'.$jobName;


if (!class_exists($jobClass, true))
    throw new \Exception("Class {$jobClass} not exists");


$jobClass::setTerminalOutput(posix_isatty(STDOUT)?true:false);
return $jobClass::runJob();
