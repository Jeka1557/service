<?php


namespace Model\Cms;
use Lib\Model\Value;
use PT;

/**
 * Class Model_Page
 * @property-read \PT\LinkedHTML $content
 */

class Page extends Value {

    protected $_content;
    protected $_linkIds;

    protected $_links;

    protected function __construct() {}


    /**
     * @param array $data
     * @return Page
     */
    static public function newFromArray($data = []) {
        /* @var Page $entity */
        $entity = parent::newFromArray();
        $entity->_content = new PT\LinkedHTML($data['content']);

        return $entity;
    }

}
