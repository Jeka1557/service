<?php

/**
 * value_set function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_value_set($params, $open, $raw_params, &$self){
	if(isset($params['separator'])){
		$separator = $params['separator'];
		unset($params['separator']);
	}
	else{
		$separator = ', ';
	}
	
	$code = '$__tmpl_value_set = array();';
	foreach ($params as $k => $v) {
		if(is_int($k)){
			$k = $v;
		}
		
		$code .= "if(!empty({$self->make_simple_param($k)})) \$__tmpl_value_set[] = {$self->make_simple_param($v)};";
	}
	$code .= "echo implode({$self->make_simple_param($separator)}, \$__tmpl_value_set);";
	
	return $self->_php_start . $code . $self->_php_end;
}
