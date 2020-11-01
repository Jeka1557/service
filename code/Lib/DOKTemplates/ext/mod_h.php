<?php

/**
 * mod_h
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_h($expr, $params, &$self){
	return "htmlspecialchars($expr)";
}

