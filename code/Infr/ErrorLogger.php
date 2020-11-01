<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 01.03.2020
 * Time: 09:33
 */

namespace Infr;

class ErrorLogger {

    static protected $traceRequest = false;


    public static function traceRequest() {
        self::$traceRequest = true;
    }


    static public function serviceError($message) {

        $fileName = SERVICE_ROOT.'/logs/errors/service/'.date('Y-m-d_H-i-s').".log";

        $text = 'REQUEST_URI: '.$_SERVER['REQUEST_URI']."\n";
        $text .= 'REQUEST_METHOD: '.$_SERVER['REQUEST_METHOD']."\n\n";

        if (self::$traceRequest) {
            $text .= 'GET: ' . var_export($_GET, true) . "\n";
            $text .= 'POST: ' . var_export($_POST, true) . "\n\n\n";
        }

        $text .= "Error: \n";
        $text .= $message;

        file_put_contents($fileName, $text);
    }


    static public function nodeError($message, $uid) {

        $fileName = SERVICE_ROOT.'/logs/errors/node/'.date('Y-m-d_H-i-s')."_{$uid}.log";

        $text = 'REQUEST_URI: '.$_SERVER['REQUEST_URI']."\n";
        $text .= 'REQUEST_METHOD: '.$_SERVER['REQUEST_METHOD']."\n\n";

        if (self::$traceRequest) {
            $text .= 'GET: ' . var_export($_GET, true) . "\n";
            $text .= 'POST: ' . var_export($_POST, true) . "\n\n\n";
        }

        $text .= "UID: {$uid}\n";
        $text .= "Error: \n";
        $text .= $message;

        file_put_contents($fileName, $text);
    }


    static public function mailError($message) {

        $fileName = SERVICE_ROOT.'/logs/errors/mail/'.date('Y-m-d_H-i-s').".log";

        $text = 'REQUEST_URI: '.$_SERVER['REQUEST_URI']."\n";
        $text .= 'REQUEST_METHOD: '.$_SERVER['REQUEST_METHOD']."\n\n";

        if (self::$traceRequest) {
            $text .= 'GET: ' . var_export($_GET, true) . "\n";
            $text .= 'POST: ' . var_export($_POST, true) . "\n\n\n";
        }

        $text .= "Error: \n";
        $text .= $message;

        file_put_contents($fileName, $text);
    }
}