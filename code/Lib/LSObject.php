<?php


namespace Lib;

class LSObject {

    private static $traceOn = false;
    private static $traceMessages = array();

    protected function traceMsg($message) {
        if (self::$traceOn) {
            $class = get_called_class();

            if (!array_key_exists($class, self::$traceMessages)) {
                self::$traceMessages[$class] = array();
            }

            self::$traceMessages[$class][] = $message;
        }
    }

    public static function traceOn() {
        self::$traceOn = true;
    }
}
 
