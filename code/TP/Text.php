<?php

namespace TP\Text;
use TP;



/**
 * Plain
 *
 * Простой текст
 *
 * @package TP\Text
 */
class Plain extends TP\Type {

    /**
     * Преобразование входного параметра к объектному типу Plain.
     *
     * @static
     * @param string $value значение, преобразуемое к Plain
     * @return string
     * @access public
     */
    public static function cast($value) {
        return $value;
    }
}



/**
 * Html
 *
 * HTML текст
 *
 * @package TP\Text
 */
class Html extends TP\Type {

    /**
     * Преобразование входного параметра к объектному типу Html.
     *
     * @static
     * @param string $value значение, преобразуемое к Html
     * @return string
     * @access public
     */
    public static function cast($value) {
        return $value;
    }
}