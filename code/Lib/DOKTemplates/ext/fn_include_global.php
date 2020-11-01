<?php

/**
 * include_global function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_include_global($params, $open, $raw_params, &$self){
	$file_name = array_shift($params);
	$file_name = trim($file_name, '"\'');
	if(!$file_name){
		return $self->error('{include} - no include file given');
	}

	return "{$self->_space}{$self->_echo_start}\$this->parse_file('$file_name', \$this->_data){$self->_echo_end}";
}
