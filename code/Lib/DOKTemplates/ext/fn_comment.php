<?php

function dt_fninfo_comment(){
	return array(
			'callback' => "dt_fn_comment",
			'type' => DTC_FUNC_PAIRED,
		);
}

/**
 * extend function
 * @author DoK
 * @package OOP
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_comment($params, $open, $raw_params, &$self, $text, $tokens){
	return '';
}
