<?php

namespace Model\Info;
use Infr;
use Model\Exception;

class Hidden extends \Model\Info {

    protected $_varName = '';
    protected $_varSource = 'GET';

    protected $TMPL_DEFAULT = 'Hidden';
    protected $TMPL_WC = 'Hidden';
    protected $TMPL_VTB = 'Hidden';
    protected $TMPL_BST4 = 'Hidden';


    static public function newFromArray($data = [])
    {
        /* @var Hidden $entity */
        $entity = parent::newInfo($data);

        if (isset($data['settings']['var_name']))
            $entity->_varName = $data['settings']['var_name'];

        if (isset($data['settings']['var_source']))
            $entity->_varSource = $data['settings']['var_source'];


        $entity->initDefault(static::castVar($data['defaultValue'],'TP\Text\Plain'));

        try {
            $entity->applyContext($data['contextData']);
        } catch (Exception\NotExistsInContextsException $e) {
            return null;
        }

        $entity->_answers = static::getAnswers();


        return $entity;
    }

}