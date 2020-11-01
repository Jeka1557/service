<?php
$dir = dirname(realpath(__FILE__));
include $dir.'/../code/html2docx.php';


HTML2DOCX::setTmpDir($dir.'/../cache/temp');


$html = $_POST['html_file'];

if (!strlen($html))
    exit('HTML file not set');


echo HTML2DOCX::convert($html);
