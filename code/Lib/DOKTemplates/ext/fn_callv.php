<?php

include_once('func_utils.php');

/**
 * callv function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_callv($params, $open, $raw_params, &$self){
	dt_func_utils::init($self);

	$name = array_shift($params);
	
	$actual_params = array();
	foreach ($params as $_) {
		$actual_params[] = $self->make_simple_param($_);
	}
	
	if(preg_match("~{$self->_rx_var_name}~", $name))
		$name = $self->make_expr($name);
	else
		$name = $self->make_simple_param($name);
		
	$actual_params = implode(', ', $actual_params);
	if(!empty($actual_params))
		$actual_params = ", $actual_params";
		
	return $self->_php_start."if(!isset(\$GLOBALS['__tmpl_fn_virtual_top'][$name]))trigger_error('virtual function \''.($name).'\' not found', E_USER_WARNING); else call_user_func(\$GLOBALS['__tmpl_fn_virtual_top'][$name]$actual_params)".$self->_php_end;
}
