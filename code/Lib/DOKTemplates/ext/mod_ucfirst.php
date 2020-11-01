<?php

/**
 * mod_ucfirst
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_ucfirst($expr, $params, &$self){
	if($params != 'N')
		return "ucfirst(strtolower($expr))";
	else
		return "ucfirst($expr)";
}

