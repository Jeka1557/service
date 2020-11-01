<?php

/**
 * mod_implode
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_implode($expr, $params, &$self){
	$self->_initial_php['implode'] = "include_once 'LS/tools/misc.php';";
	$param = $self->make_simple_param($params);
	
	return "((is_array(\$__mod_expr = ($expr))) ? implode($param, \$__mod_expr) : '')";
}
