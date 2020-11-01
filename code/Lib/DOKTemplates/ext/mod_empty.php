<?php

/**
 * mod_empty
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_empty($expr, $params, &$self){
	if(strcspn($expr, '() ') != strlen($expr)){
		return $self->error("Empty может быть только первым модификатором, выражение [$expr] похоже на результат работы других модификаторов.");
	}
	
	$default = $self->make_simple_param($params);
	
	return "(empty($expr) ? $default : $expr)";
}
