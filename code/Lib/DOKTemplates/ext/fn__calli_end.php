<?php

/**
 * _calli_end function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 * @internal 
 **/
function dt_fn__calli_end($params, $open, $raw_params, &$self){	
	$context = $self->leave_context();
	$data = array_pop($self->_compile_context['functions_current']);

	for($i = 0; $i < $self->_current_token['fn_calli_clean']; $i++){
		array_pop($self->_compile_context['functions_namespaces']);
	}
	
	$self->_compile_context['functions_calli_stack'][$data[0]][$data[1]]--;
}
