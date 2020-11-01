<?php

/**
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_date($expr, $params, &$self){
	return "(\$__tmpl_temp=$expr)?date('$params', \$__tmpl_temp):''";
}
