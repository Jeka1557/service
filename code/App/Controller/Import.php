<?php


namespace App\Controller;
use App\Controller;
use \Infr\Config;
use App\Job\ImportDB;

class Import extends Controller {

    protected $frame = 'main';

    public function indexAction() {
        return [];
    }


    public function runImportAction() {
        if (!isset($_POST['run-import']) or ($_POST['run-import']!=='database'))
            throw new \Exception('Invalid run');


        if (empty(Config::getImportDSN()))
            throw new \Exception('Import is not configured');

        ob_start();

        $result = ImportDB::runJob();
        $log = ob_get_contents();

        ob_end_clean();

        return [
            'result' => $result,
            'log' => $log,
        ];
    }
}