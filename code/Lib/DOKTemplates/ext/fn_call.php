<?php

include_once('func_utils.php');

/**
 * call function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_call($params, $open, $raw_params, &$self){
	dt_func_utils::init($self);
	
	$is_old = $func_name = $name_prefix = $function_version = $func_desc = null;
	$function = &dt_func_utils::locate_function_version($self, $params, $func_name, $name_prefix, $function_version, $func_desc, $is_old);
	$func_desc = &$func_desc[0];
	
	if(!$function)
		return false;

	$actual_params = array();
	foreach ($function['params'] as $param) {
		if(isset($params[$param])){
			$actual_params[$param] = $self->make_simple_param($params[$param]);			
			unset($params[$param]);
		}
		else{				
			if(count($params)){
				$actual_params[$param] = $self->make_simple_param(reset($params));
				unset($params[key($params)]);
			}
			else{
				return $self->error("Not enough params for calling '$name_prefix$func_name' required params are: '" . implode("', '", $function['params'])."'");
			}
		}
	}
	
	if($self->_step == 2){
		$php_fn_name = dt_func_utils::compile_function($func_desc, $self, $function_version);
		
		return "{$self->_php_start}$php_fn_name(".implode(', ', $actual_params).");{$self->_php_end}";
	}
	else
		return '';
}
