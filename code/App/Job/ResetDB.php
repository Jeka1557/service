<?php

namespace App\Job;
use \Infr\Config;
use \Infr\Job;

class ResetDB extends Job {

    /**
     * @var \SQLite3
     */
    protected $SQLite;

    protected $dbFile;
    protected $uidFile;

    public function __construct()
    {
        parent::__construct();
        $this->dbFile = Config::DB_FILE_DIR.Config::DB_FILE_NAME;
        $this->uidFile = Config::DB_FILE_DIR.Config::UID_FILE_NAME;
    }


    protected function run() {

        $this->backupDb($this->uidFile);
        $this->backupDb($this->dbFile);

        $this->initUID_DB();

        $schemeFile = SERVICE_ROOT.'/db/scheme/sqlite.sql';
        $this->dbConnect();

        $queries = explode(';', file_get_contents($schemeFile));

        foreach ($queries as $query) {
            $query = trim($query);

            if (!strlen($query))
                continue;

            $this->SQLite->query($query);
            $this->logVar('Query', $query);
        }

        $this->dbDisconnect();
    }


    protected function dbConnect() {

        $this->SQLite = new \SQLite3($this->dbFile, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        $this->SQLite->busyTimeout(500);
        $this->SQLite->enableExceptions(true);

        @chmod($this->dbFile, 0777);

        $result = $this->SQLite->query("PRAGMA journal_mode=wal;");
        $row = $result->fetchArray(SQLITE3_ASSOC);
        $result->finalize();

        $this->logVar('Journal mode', $row['journal_mode']);
    }

    protected function dbDisconnect() {
        if (is_null($this->SQLite))
            return;

        $this->SQLite->close();
        unset($this->SQLite);
    }


    protected function initUID_DB() {

        $SQLite = new \SQLite3($this->uidFile, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        $SQLite->busyTimeout(500);
        $SQLite->enableExceptions(true);

        @chmod($this->uidFile, 0777);

        $SQLite->query("PRAGMA journal_mode=wal;");
        $SQLite->query("DROP TABLE IF EXISTS srv_uid;");
        $SQLite->query("CREATE TABLE srv_uid (uid INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL);");

        $this->logStage('UID Database initialized');

        $SQLite->close();
    }


    protected function backupDb($dbFile) {

        if (!file_exists($dbFile))
            return true;


        $pathParts = pathinfo($dbFile);
        $bcFileName = $pathParts['dirname'].'/'.$pathParts['filename'].'_'.date('Y-m-d_H-i').'.db';

        if (file_exists($bcFileName))
            $bcFileName = $pathParts['dirname'].'/'.$pathParts['filename'].'_'.date('Y-m-d_H-i-s').'.db';

        $result = @rename($dbFile, $bcFileName);

        return $result;
    }
}