<?php

/**
 * mod_lowercase
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_lowercase($expr, $params, &$self){
	return "strtolower($expr)";
}

