<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 24.02.2019
 * Time: 16:26
 */


namespace Infr;


class OutputDoc {


    static public function pdf($content) {

        ob_start();
        require(SERVICE_TMPL_ROOT."/conclusion-pdf.phtml");
        $html = ob_get_clean();

        $mpdf = new \Mpdf\Mpdf();

        $mpdf->WriteHTML($html);
        $mpdf->Output('conclusion.pdf', \Mpdf\Output\Destination::INLINE);
        $mpdf->cleanup();

    }


    static public function docx($content) {

        $content = str_replace('https://', 'http://', $content);  // У libreoffice есть проблемы с загрузкой картинок по https на одном из серверов, по этому обрезаем до http

        ob_start();
        require(SERVICE_TMPL_ROOT."/conclusion-pdf.phtml");
        $html = ob_get_clean();

        $doc = \HTML2DOCX::convert($html);

        header($_SERVER["SERVER_PROTOCOL"].' 200 OK');
        header('Cache-Control: public'); // needed for internet explorer
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Length: '.strlen($doc));

        header('Content-Disposition: attachment; filename="conclusion.docx"');

        echo $doc;
    }


    static public function notFound(\Exception $e, $UID, $logKey) {

        $message = str_replace(PHP_EOL, ' ', $e->getMessage());
        $file = str_replace(PHP_EOL, ' ', $e->getFile());
        $line = str_replace(PHP_EOL, ' ', $e->getLine());

        header('HTTP/1.0 404 Not Found', true, 404);
        header("Content-Type: application/octet-stream", true);
        header("Warning: \"{$message} File: {$file} Line: {$line}\"");
        header("Log: \"http://{$_SERVER['HTTP_HOST']}/api/log-file?UID={$UID}&logKey={$logKey}\"");

    }

    public static function excel($content) {
        ob_clean();
        header($_SERVER["SERVER_PROTOCOL"].' 200 OK');
        header('Cache-Control: public');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Length: ' . filesize($content));
        header('Content-Disposition: attachment; filename="conclusion.xlsx"');
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        echo $content;
        exit;
    }
}