<?php

namespace Model\Algorithm;



class AnswerSubstitutor {

    /**
     * @var AnswerSubstitutor
     */
    protected $parent;

    protected $infoMap = [];

    protected $questionMap = [];

    protected $contextId;

    /**
     * @var Result
     */
    protected $result;


    public function __construct($infoMap, $questionMap, $contextId, Result $result, AnswerSubstitutor $parent = null)
    {
        $this->infoMap = $infoMap;
        $this->questionMap = $questionMap;
        $this->parent = $parent;
        $this->contextId = $contextId;
        $this->result = $result;
    }


    public function infoData(\Model\Algorithm\Node\Info $node) {
        $infoId = $node->infoId;

        foreach ($this->infoMap as $mapRule) {

            if (!$this->checkRuleContext($mapRule))
                continue;

            if ($infoId == $mapRule['toId'] and $mapRule['fromId']==0) {
                $node->setData($mapRule['answers']['VALUE']);
                $node->setHidden($mapRule['hide']);
                return true;
            }


            if ($infoId != $mapRule['toId'])
                continue;

            $info = $this->getSourceItem($mapRule, $node);

            if (is_null($info))
                continue;


            $fields = $mapRule['fields'];

            if ($mapRule['multiple']) {
                if ($info->isEmpty($fields))
                    continue;

                $data = $info->__get($fields);

                if (!isset($data[$node->loopId]) or empty($data[$node->loopId]))
                    continue;

                $node->setData($data[$node->loopId]);
                $node->setHidden($mapRule['hide']);
                return true;


            } elseif (!is_array($fields)) {
                if ($info->isEmpty($fields))
                    continue;

                $node->setData($info->__get($fields));
                $node->setHidden($mapRule['hide']);
                return true;

            } else {
                $data = [];

                foreach ($fields as $name => $field) {
                    if (is_numeric($field)) {
                        $data[$name] = $field;
                        continue;
                    }

                    if ($info->isEmpty($field))
                        break;

                    $data[$name] = $info->__get($field);
                }

                if (count($data)!=count($fields))
                    continue;

                $node->setData($data);
                $node->setHidden($mapRule['hide']);
                return true;
            }
        }

        return is_null($this->parent)?false:$this->parent->infoData($node);
    }


    public function answer(\Model\Algorithm\Node\Question $node) {
        $questionId = $node->questionId;

        foreach ($this->questionMap as $mapRule) {

            if (!$this->checkRuleContext($mapRule))
                continue;

            if ($questionId == $mapRule['toId'] and $mapRule['fromId']==0) {
                $node->setAnswer($mapRule['answers']['VALUE']);
                $node->setHidden($mapRule['hide']);
                return true;
            }


            if ($questionId != $mapRule['toId'])
                continue;

            $info = $this->getSourceItem($mapRule, $node);

            if (is_null($info))
                continue;


            if (!$info->isEmpty($mapRule['fields'])) {
                $value = $info->__get($mapRule['fields']);

                if ($mapRule['multiple']) {
                    if (!isset($value[$node->loopId]) or empty($value[$node->loopId]))
                        continue;

                    $value = $value[$node->loopId];
                }

                if (isset($mapRule['answers'][$value])) {
                    $node->setAnswer($mapRule['answers'][$value]);
                    $node->setHidden($mapRule['hide']);
                    return true;
                }
            }
        }

        return is_null($this->parent)?false:$this->parent->answer($node);
    }

    /**
     * @param $mapRule
     * @param Node $node
     * @return mixed|null
     * @throws \Exception
     */

    protected function getSourceItem($mapRule, \Model\Algorithm\Node $node) {

        if (!$mapRule['multiple'])
            $searchId = "{$mapRule['fromId']}".($node->loopId>0?"_{$node->loopId}":'');
        else
            $searchId = "{$mapRule['fromId']}";


        switch ($mapRule['src_type']) {
            case 'info':
                $source = $this->result->info;
            break;
            case 'action':
                $source = $this->result->actions;
            break;
            default:
                throw new \Exception("Invalid src type: {$mapRule['src_type']}");
        }

        if (!$source->offsetExists($searchId))
            return null;

        return $source[$searchId];
    }

    protected function checkRuleContext($mapRule) {
        if ($mapRule['contextId']>0 and ($mapRule['contextId']!=$this->contextId))
            return false;
        else
            return true;
    }
}