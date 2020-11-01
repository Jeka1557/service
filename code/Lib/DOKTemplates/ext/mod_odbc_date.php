<?php

/**
 * mod_odbc_date
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_odbc_date($expr, $params, &$self){
	$self->_initial_php['tools_date'] = "include_once 'LS/tools/date.php';";
	
	$params = addslashes($params);
	return "date(\"$params\", date_odbc2int($expr))";
}

