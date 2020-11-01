<?php

/**
 * callv_check function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_callv_check($params, $open, $raw_params, &$self){
	if(!isset($params[0]) || !isset($params[1])){
		return $self->error("Not enough params for callv_check(virtual_func, var)");
	}
	$name = $params[0];
	
	if(preg_match("~{$self->_rx_var_name}~", $name))
		$name = $self->make_expr($name);
	else
		$name = $self->make_simple_param($name);
		
	$var = $self->var_name($params[1]);
		
	return $self->_php_start."$var = isset(\$GLOBALS['__tmpl_fn_virtual_top'][$name]);".$self->_php_end;
}
