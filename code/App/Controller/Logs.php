<?php


namespace App\Controller;
use App\Controller;
use Infr\LogParser;
use Lib\Infr\Utility\Encoding;

class Logs extends Controller {

    protected $frame = 'service';

    public function indexAction() {
        chdir(SERVICE_ROOT.'/logs/algorithms/');
        $files = glob('[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]/*');

        $dirs = array();

        foreach ($files as $file) {
            list($dir, $file) = explode('/', $file);

            if (!isset($dirs[$dir]))
                $dirs[$dir] = array(
                    'files' => array(),
                    'count' => 0,
                    'dir'  => $dir,
                );

            $result = $this->parseLogResults($dir, $file);

            $dirs[$dir]['files'][$result['time']] = $result;
            $dirs[$dir]['count']++;
        }

        krsort($dirs);


        foreach ($dirs as &$dir) {
            ksort($dir['files']);
        }


        return [
            'dirs' => $dirs
        ];
    }

    public function gotoAction() {
        return [
            'result' => 1
        ];
    }


    public function fileAction() {
        if (!isset($_GET['dir']))
            throw new \Exception("Dir is not set");

        if (!isset($_GET['name']))
            throw new \Exception("Name is not set");

        $dir = $_GET['dir'];
        $name = $_GET['name'];

        $fileName = SERVICE_ROOT."/logs/algorithms/{$dir}/{$name}";
        $log = file_get_contents($fileName);

        $parser = new LogParser($log);

        $algorithmId = '';
        $contextId = '';
        $answers = '';
        $infoData = '';
        $conclusions = [];


        if ($parser->parse($log)) {

            $algorithmId = $parser->getAlgorithmId();
            $contextId = $parser->getContextId();
            $answers = json_encode($parser->getAnswers());
            $infoData = json_encode($parser->getInfoData());

            $conclusions = $parser->getConclusions();

            /*
            $session = array(
                'algorithmId' => $parser->getAlgorithmId(),
                'contextId' => $parser->getContextId(),
                'linkType' => 1,
                'session' => array(
                    'answers' => $parser->getAnswers(),
                    'infoData' => $parser->getInfoData(),
                    'done' => false,
                ),
            );
            */

        }

        return [
            'log' => $log,
            'algorithmId' => $algorithmId,
            'contextId' => $contextId,
            'answers' => $answers,
            'infoData' => $infoData,
            'conclusions' => $conclusions,
        ];
    }


    protected function parseLogResults($dir, $name) {
        $result = array(
            'name' => $name,
            'steps' => 0,
            'complete' => false,
            'error' => true,
        );
        $m = array();

        $fileName = escapeshellcmd(SERVICE_ROOT."/logs/algorithms/{$dir}/{$name}");
        $line = shell_exec("tail -n 1 $fileName");

        if (preg_match("~^Result \(steps:(\d+) complete:(true|false) error:(true|false)\)$~", $line, $m)) {
            $result['steps'] = (int)$m[1];
            $result['complete'] =  $m[2]=='true'?true:false;
            $result['error'] =  $m[3]=='true'?true:false;
        }


        $result['time'] = date('H:i:s', filemtime($fileName));

        return $result;
    }


}