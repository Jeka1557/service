<?php

/**
 * mod_decode_var
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_decode_var($expr, $params, &$self){
	$parts = explode(',', $params, 2);
	$var = $parts[0];
	$index = isset($parts[1]) ? $parts[1] : null;
	
	$var = $self->var_name($var);
	if($index){
		$index = addslashes($index);
		$new = "{$var}[$expr][\"$index\"]";		
	}
	else
		$new = "{$var}[$expr]";
	return $new;
}

