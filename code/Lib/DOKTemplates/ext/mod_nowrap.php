<?php

/**
 * mod_nowrap
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_nowrap($expr, $params, &$self){
	return "str_replace(' ', '&nbsp;', $expr)";
}

