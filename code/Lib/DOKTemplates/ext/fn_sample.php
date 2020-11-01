<?php

/**
 * sample function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_sample($params, $open, $raw_params, &$self){
	return $self->_echo_start."'Compile time: ".date('H:i:s')."'".$self->_echo_end;
}
