<?php

function dt_fninfo_part(){
	return array(
			'callback' => "dt_fn_part",
			'type' => DTC_FUNC_BOTH,
		);
}

/**
 * part function
 * @author DoK
 * @package OOP
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_part($params, $open, $raw_params, &$self){
	return '';
}
