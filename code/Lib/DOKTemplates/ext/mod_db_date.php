<?php

/**
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_db_date($expr, $params, &$self){
	if(strtolower($self->_params['sql']) == 'ms')
		return "gmdate('$params', $expr)";
	else
		return "date('$params', $expr)";
}
