<?php

namespace Model\Algorithm\Node;
use Model\Algorithm\Node;


class Info extends Node {
    public $infoId;
    /**
     * @var \Model\Info
     */
    public $info;
    public $hidden = false;

    protected $data;

    public function getEndpoints() {
        $endpoints = array();

        foreach ($this->info->answers as $id=>$answer) {
            $endpoints[$id] = (string)$answer; // json не правильно воспринимает null, надо пустую строку
        }

        return $endpoints;
    }

    /*
    function toGraphArray() {
        $result = parent::toGraphArray();
        $result['info_id'] = $this->infoId;

        return $result;
    }
    */

    static function createFromArray($data) {
        $node = new self();

        $node->id = (int)$data['id'];
        $node->infoId = (int)$data['info_id'];

        /*
        $node->comment = $data['comment'];
        $node->x = (int)$data['x'];
        $node->y = (int)$data['y'];
        $node->width = (int)$data['width'];
        $node->height = (int)$data['height'];
        */

        return $node;
    }


    public function setData($data) {
        $this->data = $data;
        $this->info->setData($data);
    }

    public function dataReady() {
        if (!isset($this->info))
            return false;

        /** Отсутствие данных для скрытого поля не должно останавливать выполнение алгоритма */
        if (is_a($this->info, '\Model\Info\Hidden'))
            return true;

        return $this->info->dataReady();
    }

    public function getData() {
        return $this->data;
    }

    public function setHidden($hidden) {
        $this->hidden = $hidden;
    }

    public function nextNode() {
        return $this->findChild($this->info->getAnswerId());
    }

    public function reset() {
        $this->data = null;
    }
}