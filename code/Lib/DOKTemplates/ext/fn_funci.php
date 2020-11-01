<?php

require_once('fn_func.php');
include_once('func_utils.php');

function dt_fninfo_funci(){
	return array(
			'callback' => "dt_fn_funci",
			'type' => DTC_FUNC_PAIRED,
		);
}


/**
 * funci function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_funci($params, $open, $raw_params, &$self, $text, $tokens){
	if(dt_fn_func($params, $open, $raw_params, $self, $text, $tokens) === ''){
		$orig_params = $params;
		$func_name = array_shift($params);
		
		$token = array(
				'type' => TOK_FUNC,
				'text' => '{calli '.$func_name.' '.implode(', ', $params).'}',
				'name' => 'calli',
				'params' => implode(' ', $orig_params),
				'file' => $self->_current_token['file'],
				'line' => $self->_current_token['line'],
			);
			
		$self->unshift_token($token);
		
		return '';
	}
	
//	if(isset($self->_params['no_func']))
//		return $self->error('No nested functions allowed at this moment');
		
	/*if($self->_step == 1){
		if(!isset($self->_compile_context['functions']))
			$self->_compile_context['functions'] = array();

		$DTC_FUNCTIONS = &$self->_compile_context['functions'];
			
		$func_name = array_shift($params);
		if(!empty($DTC_FUNCTIONS[$func_name]['inline_used']))
			return $self->error('Redefinition of function which was once used as inline is not allowed. (Нельзя переопределять функцию, которая хотя бы раз успела использоваться как inline)');
		
		$DTC_FUNCTIONS[$func_name]['versions'][] = array(
				'params' => $params,
				'tokens' => $tokens,
				'text' => $text,
			);
	}*/
	
	return '';
}
