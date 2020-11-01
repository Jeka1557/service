<?php

/**
 * error function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_error($params, $open, $raw_params, &$self){
	//$error = preg_replace("~{$self->_rx_var_name}~e", '"{".$self->var_name(\'$0\')."}"', $params[0]);
	$error = preg_replace_callback(
		"~{$self->_rx_var_name}~",
		function ($m) use ($self) {
			return '{'.$self->var_name($m[0]).'}';
		},
		$params[0]
	);

	
	return $self->_php_start."trigger_error(\"$error\", E_USER_WARNING);".$self->_php_end;
}
