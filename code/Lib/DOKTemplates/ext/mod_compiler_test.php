<?php

/**
 * mod_sample
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_compiler_test($expr, $params, &$self){
	$params = addcslashes($params, "'");
	return "$expr.'$params'";
}

