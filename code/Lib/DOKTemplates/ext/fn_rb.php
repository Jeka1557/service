<?php

/**
 * rb function - right bracket
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_rb($params, $open, $raw_params, &$self){
	return $self->_templ_end;
}
