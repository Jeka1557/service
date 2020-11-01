<?php

/**
 * function number_format($number, $decimals, $decPoint, $thouthandsSep) см. http://php.net/number_format
 * @author wild_honey
 * @return string
 * @param $params array 
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_number_format($params, $open, $raw_params, &$self){
	$number = $self->make_simple_param($params[0]);
	$decimals = isset($params[1]) ? (int)$params[1] : NULL;
	$decPoint = isset($params[2]) ? (string)$params[2] : NULL;
	$thouthandsSep = isset($params[3]) ? (string)$params[3] : NULL;
	$arguments = "$number";
	if ($decimals !== NULL) {
		$arguments .= ', '. $decimals;
		if ($decPoint !== NULL) {
			$arguments .= ", '{$decPoint}'";
			if ($thouthandsSep !== NULL) {
				$arguments .= ", '{$thouthandsSep}'";
			}
		}
	}
	return $self->_echo_start . "number_format($arguments)" . $self->_echo_end;
}
