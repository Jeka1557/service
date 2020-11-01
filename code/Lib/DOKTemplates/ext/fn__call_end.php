<?php

/**
 * _call_end function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 * @internal 
 **/
function dt_fn___call_end($params, $open, $raw_params, &$self){
	$self->leave_context();
	array_pop($self->_compile_context['functions_current']);
}
