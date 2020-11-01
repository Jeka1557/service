<?php

/**
 * mod_uppercase
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_uppercase($expr, $params, &$self){
	return "strtoupper($expr)";
}

