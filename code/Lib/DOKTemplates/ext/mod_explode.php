<?php

/**
 * mod_explode
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_explode($expr, $params, &$self){
	$array = explode(',', $params);
	if(count($array) == 1){
		$separator = $array[0];		
	}
	else{
		$num = end($array);
		$separator = implode(',', array_slice($array, 0, -1));
	}
	
	$separator = trim($separator);
	if(!preg_match('~^([\'"]).*(\\1)$~', $separator))
		$separator = '"'.addslashes($separator).'"';
	
	if(!is_null($num)){
		$num = trim($num);
		return "((\$__tmpl_mod_explode=explode($separator, $expr))?\$__tmpl_mod_explode[$num]:null)";
	}
	else{
		return "explode($separator, $expr)";
	}
}

