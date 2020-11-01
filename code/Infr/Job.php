<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 28.10.2016
 * Time: 23:23
 */

namespace Infr;

use \Lib\Infr\SecondRunLocker;


abstract class Job {

    static protected $terminalOutput = false;

    static public function setTerminalOutput($on) {
        self::$terminalOutput = $on;
    }

    abstract protected function run();


    public function __construct()
    {
        /**
         * @todo Хак для сброса буфера, иначе не выводит в консоль. Разобраться как это решить красиво.
         */
        if (self::$terminalOutput) {
            while (@ob_end_flush()) ;
        }
    }

    static public function runJob() {
        set_time_limit(0);

        try {
            $job = new static();

            $locker = new SecondRunLocker();
            $locker->lockProccess($job->lockFile(), '.lock');

            if ($locker->getLastError()) {
                throw new \Exception($locker->getLastError());
            }


            echo "JOB STARTED: ".get_class($job)."\n";
            $startTime = microtime(true);

            $job->run();

            $time = microtime(true) - $startTime;
            echo "\nJOB DONE: ".gmdate('H:i:s',$time)."\n";

            return 0;

        } catch (\Exception $e) {
            echo "Error: ".$e->getMessage();
            return 1;
        }
    }

    protected function lockFile() {
        return SERVICE_ROOT.'/cache/'.str_replace('\\', '_', strtolower(get_class($this)));
    }


    protected function logStage($name) {
        echo "\n{$name}\n";
    }

    protected function logTime($time) {
        echo "Time: ".gmdate('H:i:s',$time)."\n";
    }

    protected function terminalCounter($value, $frequency) {
        if (!self::$terminalOutput)
            return;

        if ($value%$frequency==0)
            echo "{$value}\r";
    }

    protected function logVar($name, $value) {
        echo "{$name}: $value\n";
    }

    protected function logError(\Exception $e) {
        echo "Error: ".$e->getMessage();
    }

}