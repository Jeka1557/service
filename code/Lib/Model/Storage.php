<?php

namespace Lib\Model;
use Lib\LSObject;
use Lib\Model\Collection;


class Storage extends LSObject {

    /**
     * @param Collection $collection
     * @param $property
     * @return array
     * @access protected
     */
    protected function getCollectionGroupProperty(Collection $collection, $property) {
        try {
            $result = $collection->getGroupProperty($property);

        } catch (\Exception $e) {
            $result = array();

            foreach ($collection as $k=>$v) {
                $result[$v->$property] = 1;
            }

            $result = array_keys($result);
        }

        return $result;
    }

    /**
     * @param $name
     * @param null $entity
     * @return mixed
     * @access protected
     */
    protected function mapProperty($name, $entity = null) {
        return $name;
    }

    /**
     * @param $row
     * @param string $mapFunc
     * @return array
     * @access protected
     */
    protected function mapRow($row, $mapFunc = 'mapProperty') {
        $result = array();

        foreach ($row as $k=>$v) {
            $result[$this->$mapFunc($k)] = $v;
        }

        return $result;
    }

    /**
     * @param mixed $key
     * @param array $array
     * @return null|mixed
     * @access protected
     */
    protected function getNullable($key, array $array) {
        return ((isset($array[$key])) ? $array[$key] : null);
    }
}