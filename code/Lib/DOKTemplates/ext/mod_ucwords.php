<?php

/**
 * mod_ucwords
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_ucwords($expr, $params, &$self){
	if($params != 'N')
		return "ucwords(strtolower($expr))";
	else
		return "ucwords($expr)";
}

