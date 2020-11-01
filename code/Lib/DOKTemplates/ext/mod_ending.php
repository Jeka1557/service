<?php

/**
 * mod_ending
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_ending($expr, $params, &$self){
	$self->_initial_php['ending'] = "include_once 'LS/tools/misc.php';";
	if($params){
		$items = explode(',', $params);
		$items = 'array("'.implode('", "', $items).'")';
		
		return "get_ending($expr, $items)";
	}
	else
		return "get_ending($expr)";
}
