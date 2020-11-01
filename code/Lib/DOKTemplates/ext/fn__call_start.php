<?php

/**
 * _call_start function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 * @internal 
 **/
function dt_fn___call_start($params, $open, $raw_params, &$self){
	$actual_params = array();
	foreach ($params as $param) {
		$actual_params[substr($param, 1)] = $param;
	}
	
	$self->enter_context("inline", array(), false, '', '', 
		array(
			'map_vars' => $actual_params,
			'no_bubble' => 1,
		));
}
