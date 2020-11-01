<?php

namespace App\Job;
use \Lib\Infr\Db\Adapter;
use \Lib\Infr\Db;
use \Infr\Config;
use \Infr\Job;
use \Infr\Db\TransferTable;


class ImportDB extends Job {

    const FETCH_BUFFER = 25000;
    const COUNTER_FREQUENCY = 50;

    /**
     * @var Adapter\Pgsql
     */
    protected $srcAdapter;

    /**
     * @var Adapter\Pgsql
     */
    protected $dstAdapter;



    protected $startTime;
    protected $lastCount = 0;


    public function __construct()
    {
        parent::__construct();

        $this->srcAdapter = new Adapter\Pgsql(Config::getImportDSN());
        $this->dstAdapter = new Adapter\Pgsql(Config::getDSN());
    }


    protected function run() {
        $tables = require(SERVICE_ROOT.'/db/scheme/posrgresql.php');

        foreach ($tables as $table) {
            $trTable = new TransferTable($table);
            $this->tableMergeTransfer($trTable);
        }
    }



    protected function tableMergeTransfer(TransferTable $table) {

        $workspaces = Config::getWorkspaces();

        $this->logVar('Transfer table:', $table->dstTable);

        $rowsCount = 0;
        $rowsUpdated = 0;
        $rowsInserted = 0;
        $rowsDeleted = 0;

        $pgStmt = $this->srcAdapter->query("SELECT min(id) as min_id, max(id) as max_id  FROM {$table->srcTable} WHERE workspace_id IN (".implode(',', $workspaces).")");
        $pgStmt->setFetchDefault(Db::FETCH_ASSOC);
        $row = $pgStmt->fetchRow();

        $minId = (int)$row['min_id'];
        $maxId = (int)$row['max_id'];

        $stmt = $this->dstAdapter->query("DELETE FROM {$table->dstTable} WHERE id<{$minId} OR id>{$maxId}");
        $rowsDeleted += $stmt->rowsAffected();


        try {

            $this->srcAdapter->query('BEGIN;');
            $this->srcAdapter->query("DECLARE cur_filter_transfer CURSOR FOR SELECT {$table->columnsString} FROM {$table->srcTable} s WHERE workspace_id IN (".implode(',', $workspaces).") ORDER BY id;");
            // $this->logVar('SELECT Query', "DECLARE cur_filter_transfer CURSOR FOR SELECT {$table->columnsString} FROM {$table->srcTable} s WHERE workspace_id IN (".implode(',', $workspaces).") ORDER BY id;");


            // $insertStmt = $this->SQLite->prepare("INSERT INTO {$table->name} ({$table->columns}) VALUES({$table->bind})");
            // $updateStmt = $this->SQLite->prepare("UPDATE {$table->name} SET {$table->assign} WHERE id=:id");

            $maxId = 0;

            do {
                $pgStmt = $this->srcAdapter->query('FETCH '.self::FETCH_BUFFER.' FROM cur_filter_transfer;');
                $pgStmt->setFetchDefault(Db::FETCH_ASSOC);


                $pgRows = array();

                foreach ($pgStmt as $row) {
                    $pgRows[] = $row;
                }

                $rowsFetched = count($pgRows);

                if ($rowsFetched==0)
                    break;

                $minId = $maxId;
                $maxId = $pgRows[$rowsFetched-1]['id'];


                $this->dstAdapter->query('BEGIN;');
                // $resSelect = $this->dstAdapter->query("SELECT id, updated FROM {$table->name} WHERE id>{$minId} AND id<={$maxId} ORDER BY id");
                $resSelect = $this->dstAdapter->query("SELECT id FROM {$table->dstTable} WHERE id>{$minId} AND id<={$maxId} ORDER BY id");


                $rowNum = 0;
                $pgNext = true;
                $ltNext = true;

                while ($rowNum<$rowsFetched) {

                    $this->terminalCounter($rowsCount+$rowNum, self::COUNTER_FREQUENCY);

                    if ($pgNext) {
                        $pgRow = $pgRows[$rowNum++];
                        $pgId = (int)$pgRow['id'];
                    }

                    if ($ltNext) {
                        $ltRow = $resSelect->fetchRow(DB::FETCH_ASSOC);
                        $ltId = (is_null($ltRow))?false:(int)$ltRow['id'];
                    }

                    if ($ltId===false) {
                        // PG: 101, 102, #103, 104
                        // LT: 101, 102, #
                        // $this->logVar('INSERT Query', "INSERT INTO {$table->dstTable} ({$table->columnsString}) VALUES(".$table->getValues($pgRow).")");
                        $this->dstAdapter->query("INSERT INTO {$table->dstTable} ({$table->columnsString}) VALUES(".$table->getValues($pgRow).")");

                        $rowsInserted++;

                        $pgNext = true;
                        $ltNext = false;
                        continue;
                    }

                    if ($ltId==$pgId) {
                         // PG: 101, 102, #103, 104
                         // LT: 101, 102, #103, 104
                        // if ($ltRow['updated']!=$pgRow['updated']) {
                            $this->dstAdapter->query("UPDATE {$table->dstTable} SET ".$table->getAssign($pgRow)." WHERE id='{$ltRow['id']}'");
                            $rowsUpdated++;
                        //}

                        $pgNext = true;
                        $ltNext = true;
                        continue;
                    }

                    if ($ltId<$pgId) {
                        // PG: 101,      #103, 104
                        // LT: 101, #102, 103, 104
                        $this->dstAdapter->query("DELETE FROM {$table->dstTable} WHERE id={$ltRow['id']}");
                        $rowsDeleted++;

                        $ltNext = true;
                        $pgNext = false;
                        continue;
                    }

                    if ($ltId>$pgId) {
                        // PG: 101, #102, 103, 104
                        // LT: 101,      #103, 104
                        $this->dstAdapter->query("INSERT INTO {$table->dstTable} ({$table->columnsString}) VALUES(".$table->getValues($pgRow).")");
                        $rowsInserted++;

                        $pgNext = true;
                        $ltNext = false;
                        continue;
                    }

                }

                $rowsCount += $rowsFetched;
                $this->dstAdapter->query('END;');

            } while ($rowsFetched==self::FETCH_BUFFER);


            $this->srcAdapter->query('CLOSE cur_filter_transfer;');
            $this->srcAdapter->query('END;');

            $this->logVar('Rows merged:', $rowsCount);
            $this->logVar('Rows updated:', $rowsUpdated);
            $this->logVar('Rows inserted:', $rowsInserted);
            $this->logVar('Rows deleted:', $rowsDeleted);


        } catch (\Exception $e) {
            $this->dstAdapter->query('ROLLBACK;');
            $this->srcAdapter->query('END;');
            throw $e;
        }
    }

}
