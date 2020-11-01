<?php

include_once('func_utils.php');

/**
 * calli function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_calli($params, $open, $raw_params, &$self){
	dt_func_utils::init($self);
	
	$dummy_vars = &$self->_compile_context['functions_calli_dummy_vars'];
	
	$is_old = $func_name = $name_prefix = $function_version = $func_desc = null;
	$function = &dt_func_utils::locate_function_version($self, $params, $func_name, $name_prefix, $function_version, $func_desc, $is_old);
	$func_desc = &$func_desc[0];
	if(!$function)
		return false;
	
	$out = '';
	
	if(!empty($self->_compile_context['functions_calli_stack'][$name_prefix.$func_name][$function_version])){
		return $self->error("Calli recursion is not allowed. Use call.");
	}
	
	$actual_params = array();
	foreach ($function['params'] as $param) {
		if(isset($params[$param])){
			$actual_params[$param] = $params[$param];
			unset($params[$param]);
		}
		else{				
			if(count($params)){
				$actual_params[$param] = reset($params);
				unset($params[key($params)]);
			}
			else{
				return $self->error("Not enough params for calling '$name_prefix$func_name' required params are: '" . implode("', '", $function['params'])."'");
			}
		}
	}

	$t = array();
	foreach ($actual_params as $k => $param) {
		if(!$self->is_var_name($param)){
			$out .= $self->_php_start.'$__tmpl_inline_dummy'.($dummy_vars).' = '.$self->make_simple_param($param).';'.$self->_php_end;
			$t[substr($k, 1)] = '$__tmpl_inline_dummy'.($dummy_vars);
			$dummy_vars++;
		}
		else{
			$t[substr($k, 1)] = $self->make_simple_param($param);
		}
	}
	$actual_params = $t;
	
	$self->enter_context("inline $name_prefix$func_name", array(), false, '', '', 
		array(
			'map_vars' => $actual_params,
			'no_bubble' => 1,
		));

	$self->_compile_context['functions_current'][] = array($name_prefix.$func_name, $function_version);
	if(!isset($self->_compile_context['functions_calli_stack'][$name_prefix.$func_name][$function_version]))
		$self->_compile_context['functions_calli_stack'][$name_prefix.$func_name][$function_version] = 0;
		
	$self->_compile_context['functions_calli_stack'][$name_prefix.$func_name][$function_version]++;

	if(!$is_old){
		$func_parts = explode('.', $func_name);
		foreach ($func_parts as $_) {
			$self->_compile_context['functions_namespaces'][] = $_;
		}
	}
	
	$self->unshift_token(array(
			'type' => TOK_FUNC, 
			'text' => '{_calli_end}', 
			'line' => $self->_current_token['line'],
			'file' => $self->_current_token['file'],
			'name' => '_calli_end',
			'params' => '',
			'fn_calli_clean' => $is_old ? 0 : count($func_parts),
		));

	$self->unshift_token($function['tokens']);
	
	return $out;
}
