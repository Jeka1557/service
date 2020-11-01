<?php

/**
 * mod_replace
 * @author DoK
 * @return string
 * @param $expr string
 * @param $params string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_replace($expr, $params, &$self){

    $args = array();
    $m = array();
    if (preg_match("~^('(.*)','(.*)')|(\"(.*)\",\"(.*)\")$~", $params, $m)) {
        if (count($m) == 7) {
            $args[0] = $m[5];
            $args[1] = $m[6];
        }
        elseif (count($m) == 4) {
            $args[0] = $m[2];
            $args[1] = $m[3];
        }
        
        return "str_replace('{$args[0]}', '{$args[1]}', $expr)";
    }
    
    return $expr;
}
