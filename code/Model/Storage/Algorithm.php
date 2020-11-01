<?php


namespace Model\Storage;
use Model\Storage as Storage;
use Lib\Model\Collection;
use Lib\Infr\Db\Select;
use Lib\Infr\DSN;


class Algorithm extends Storage {

    protected $tableName = 'algorithm';
    protected $collectionClass = '\Model\Collection\Algorithm';

    protected $defaultFieldName = 'algorithm';

    protected $relContextTableName = 'rel_algorithm_context';
    protected $endPointTableName = 'node_end_point';
    protected $answerMapTableName = 'algorithm_answer_map';
    protected $dictContextTableName = 'context';


    public function __construct(DSN $dsn) {
        parent::__construct($dsn);

        $this->answerMapTableName = $this->makeTableName($this->answerMapTableName);
        $this->dictContextTableName = $this->makeTableName($this->dictContextTableName);

        $this->endPointTableName = \Infr\Db\Node\Table::makeTableName($this->endPointTableName);
    }


    function createEntity($row, $contextData, $className = null, $copyId = 0) {
        return \Model\Algorithm::newFromArray([
            'id' => $row['id'],
            'name' => $row['name'],
            'screenGroup' => $row['screen_grp'],
            'hasMap' => (isset($row['has_map']) and ($row['has_map']=='t'))?true:false,
            'copyId' => $copyId,
        ]);
    }

    function createEndPoint($row) {
        return \Model\Algorithm\Node\EndPoint::createFromArray([
            'id' => $row['id'],
        ]);
    }


    function addExtraEntities(Collection $collection, $entities, $copyId = 0) {
        $ids = array();
        $entityWithMap = array();

        foreach ($collection as $entity) {
            /** @var \Model\Algorithm $entity */
            $ids[] = $entity->id;

            if ($entity->hasMap)
                $entityWithMap[$entity->id] = $entity;
        }

        if (count($ids)) {
            $select = new Select($this->dbAdapter);
            $rows = $select->from($this->endPointTableName)
                ->where("algorithm_id", $ids)
                ->order('id')
                ->execute()
                ->fetchAll();

            $endPoints = array();

            foreach ($rows as $row) {
                if (!isset($endPoints[$row['algorithm_id']]))
                    $endPoints[$row['algorithm_id']] = array();

                $endPoints[$row['algorithm_id']][] = $this->createEndPoint($row);
            }

            foreach($collection as $entity) {
                $entity->endPoints = isset($endPoints[$entity->id])?$endPoints[$entity->id]:array();
            }
        }


        if (count($entityWithMap)) {
            $select = new Select($this->dbAdapter);
            $rows = $select->from($this->answerMapTableName)
                ->where("algorithm_id", array_keys($entityWithMap))
                ->order(['id' => Select::ORDER_ASC])
                ->execute()
                ->fetchAll();

            $infoMaps = [];
            $questionMaps = [];

            foreach ($rows as $row) {
                $rule = [
                    'src_type' => $row['src_type'],
                    'fromId' => (int)$row['from_id'],
                    'toId' => (int)$row['to_id'],
                    'contextId' => (int)$row['context_id'],
                    'hide' => ($row['hide']=='t')?true:false,
                    'fields' => null,
                    'multiple' => ($row['multiple']=='t')?true:false,
                    'answers' => [],
                ];

                if (strlen($row['fields'])) {
                    $rule['fields'] =  ($row['fields'][0]=='{')?json_decode($row['fields'], true):$row['fields'];
                }


                $answers = empty($row['answers'])?[]:json_decode($row['answers'], true);

                foreach ($answers as $name=>$value) {
                    if (defined($name))
                        $name = constant($name);

                    $rule['answers'][$name] = is_numeric($value)?(int)$value:$value;
                }

                switch ($row['dst_type']) {
                    case 'info':
                        $infoMaps[(int)$row['algorithm_id']][] = $rule;
                    break;
                    case 'question':
                        $questionMaps[(int)$row['algorithm_id']][] = $rule;
                    break;
                }
            }

            foreach($entityWithMap as $entity) {
                $entity->infoMap = isset($infoMaps[$entity->id])?$infoMaps[$entity->id]:[];
                $entity->questionMap = isset($questionMaps[$entity->id])?$questionMaps[$entity->id]:[];
            }
        }
    }


    public function getEntityContexts(\TP\Type $id) {
        $select = new Select($this->dbAdapter);
        $rows = $select->from(array('self' => $this->relContextTableName))
            ->joinLeft(array('ctx' => $this->dictContextTableName),'ctx.id = self.context_id')
            ->columns(array(
                'id' => 'ctx.id',
                'name' => 'ctx.name',
            ))
            ->where("algorithm_id", $id->val())
            ->order('ctx.id')
            ->execute()
            ->fetchAll();

        $result = new \Model\Collection\Context();

        foreach ($rows as $row) {
            $result[$row['id']] = \Model\Context::newFromArray($row);
        }

        return $result;
    }


    protected function getContextData($id) {
        return array();
    }

}