<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 21.12.2017
 * Time: 02:34
 */

namespace Infr;


class LogParser {

    protected $algorithmId;
    protected $contextId = 0;
    protected $questions = [];
    protected $info = [];
    protected $conclusions = [];


    public function getInfoData() {
        $result = array();

        foreach ($this->info as $info) {
            $result[$info['id']] = $info['data'];
        }

        return $result;
    }

    public function getAlgorithmId() {
        return $this->algorithmId;
    }

    public function getContextId() {
        return $this->contextId;
    }

    public function getAnswers() {
        $result = array();

        foreach ($this->questions as $question) {

            if (count($question['answer'])==1) {
                $result[$question['id']] = $question['answer'][0];

            } elseif (count($question['answer'])>1) {
                $result[$question['id']] = $question['answer'];
            }
        }

        return $result;
    }

    public function getConclusions() {
        return $this->conclusions;
    }


    protected function prepare($rows) {
        $result = array();

        reset($rows);
        $row = current($rows);

        while ($row !== false) {
            if (trim($row)=='->InfoData: array (') {

                while (($sRow = next($rows)) !== false) {
                    $row .= $sRow;

                    if (trim($sRow)==')')
                        break;
                }
            }

            $result[] = $row;
            $row = next($rows);
        }

        return $result;
    }


    public function parse($log) {
        $rows = explode("\n", $log);
        $rows = $this->prepare($rows);

        reset($rows);

        if (!$this->parseHeader($rows))
            return false;

        while (($row=$this->nextRow($rows))!==false) {
            switch ($this->parseNode($row)) {
                case 'Info':
                    if (!$this->parseNodeInfo($rows))
                        return false;
                    break;
                case 'Question':
                    if (!$this->parseNodeQuestion($rows))
                        return false;
                    break;
                case 'Conclusion':
                    if (!$this->parseNodeConclusion($rows))
                        return false;
                    break;
            }
        }

        return true;
    }

    protected function parseNodeInfo(&$rows) {
        $row = trim(current($rows));

        $m = array();

        if (!preg_match('~^(\d+): Node Info \(NodeId: (\d+) LoopId: (\d+) InfoId: (\d+)\)$~', $row, $m))
            return false;

        $info = array(
            'id' => (int)$m[4].($m[3]>0?'_'.$m[3]:''),
            'data' => '',
        );

        $this->nextRow($rows);
        $this->nextRow($rows);
        $this->nextRow($rows);
        $row = $this->nextRow($rows);

        if (preg_match('~^\-\>InfoData: \'(.*)\'$~', $row, $m)) {
            $info['data'] = $m[1];

        } elseif (preg_match('~^\-\>InfoData: array\s?\((.*)\,\)$~', $row, $m)) {
            $info['data'] = eval("return array({$m[1]});");
        }

        $this->info[] = $info;

        return true;
    }


    protected function parseNodeQuestion(&$rows) {
        $row = trim(current($rows));

        $m = array();

        if (!preg_match('~^(\d+): Node Question \(NodeId: (\d+)  LoopId: (\d+) QuestionId: (\d+)\)$~', $row, $m))
            return false;


        $question = array(
            'id' => (int)$m[4].($m[3]>0?'_'.$m[3]:''),
            'answer' => array(),
        );

        $this->nextRow($rows);
        $this->nextRow($rows);
        $this->nextRow($rows);

        while (($row = $this->nextRow($rows))!==false) {
            if (preg_match('~^\-\>Answer(\s\d+)?: "(\d+)"~', $row, $m)) {
                $question['answer'][] = (int)$m[2];
            } else {
                prev($rows);
                break;
            }
        }

        $this->questions[] = $question;

        return true;
    }


    protected function parseNodeConclusion(&$rows) {
        $row = trim(current($rows));

        $m = array();


        if (!preg_match('~^(\d+): Node Conclusion \(NodeId: (\d+) LoopId: (\d+)\)$~', $row, $m))
            return false;

        $conclusion = array(
            'id' => '',
            'header' => '',
        );

        $row = $this->nextRow($rows);

        if (!preg_match('~^Conclusion \(id: (\d+)\)$~', $row, $m))
            return false;

        $conclusion['id'] = $m[1];

        $row = $this->nextRow($rows);

        if (!preg_match('~^Header: (.*)$~', $row, $m))
            return false;

        $conclusion['header'] = $m[1];

        $this->conclusions[] = $conclusion;

        return true;
    }



    protected function parseNode($row) {
        $m = array();

        if (!preg_match('~^(\d+): Node (\w+) \(.*\)$~', $row, $m))
            return false;

        return $m[2];
    }

    protected function nextRow(&$rows) {
        while (($row=next($rows))!==false) {
            $row = trim($row);

            if (!strlen($row))
                continue;

            return $row;
        }

        return false;
    }


    protected function parseHeader(&$rows) {
        $startMarker = trim(current($rows));

        if ($startMarker!='ALGORITHM START')
            return false;

        $algorithm =  $this->nextRow($rows);

        $m = array();

        if (!preg_match('~Algorithm \(id: (\d+)\)~', $algorithm, $m))
            return false;

        $this->algorithmId = (int)$m[1];

        $this->nextRow($rows);


        $row = $this->nextRow($rows);

        if (preg_match('~ContextId: (\d+)~', $row, $m))
            $this->contextId = (int)$m[1];
        else
            prev($rows);

        return true;
    }


}