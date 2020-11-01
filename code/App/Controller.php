<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 21.12.2017
 * Time: 01:06
 */

namespace App;

use Infr\Config;


class Controller  extends \Infr\Controller {

    protected $frame = 'main';


    public function indexAction() {
        return [];
    }

    public function pingAction() {

        if (Config::$LOCAL_DB) {

            try {
                $SQLite = new \SQLite3(Config::DB_FILE_DIR . Config::UID_FILE_NAME, SQLITE3_OPEN_READONLY);
                $SQLite->busyTimeout(500);
                $SQLite->enableExceptions(true);

                $SQLite->close();
            } catch (\Exception $e) {
                return "ERROR: " . Config::DB_FILE_DIR . Config::UID_FILE_NAME . " " . $e->getMessage();
            }

            try {
                $SQLite = new \SQLite3(Config::DB_FILE_DIR . Config::DB_FILE_NAME, SQLITE3_OPEN_READONLY);
                $SQLite->busyTimeout(500);
                $SQLite->enableExceptions(true);

                $SQLite->close();
            } catch (\Exception $e) {
                return "ERROR: " . Config::DB_FILE_DIR . Config::DB_FILE_NAME . " " . $e->getMessage();
            }

        }

        return 'pong';
    }

}