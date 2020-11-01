<?php

/**
 * mod_entity_decode
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_entity_decode($expr, $params, &$self){
    return "html_entity_decode({$expr})";
}

