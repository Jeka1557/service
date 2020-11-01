<?php

namespace Lib\Model\Collection;


class Exception extends \Lib\Exception {

    const COUNT_UNDEF = 1;

    const PROP_UNDEF = 2;

    const GROUP_NOT_IMPLEMENTED = 3;

    const GROUP_UNDEF = 4;

    const METHOD_UNDEF = 5;

    const NOT_OBJECT = 6;

    const OBJECT_PARENT = 7;

    protected $_messages = array(
        self::COUNT_UNDEF => 'Data count is not set',
        self::PROP_UNDEF => 'Property %s is not member of %s',
        self::GROUP_NOT_IMPLEMENTED => 'Recount group properties are not implemented yet',
        self::GROUP_UNDEF => 'Group not set',
        self::METHOD_UNDEF => 'Method %s is not exist in %s',
        self::NOT_OBJECT => 'Value is not object',
        self::OBJECT_PARENT => 'Entity object is invalid'
    );
}