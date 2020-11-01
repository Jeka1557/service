<?php

class DOK_Templates_VariableRequiredException extends Exception {}

function dok_templates_check_required($value, $name) {
	if(!empty($value))
		return $value;
	else
		throw new DOK_Templates_VariableRequiredException("Variable [$name] is required.");
}

/**
 * mod_required
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_required($expr, $params, &$self){
	$self->_initial_php['inc_mod_required'] = 'include_once \'' . __FILE__ . '\';';
	return "dok_templates_check_required($expr, '" . addcslashes($expr, "'\\") . "')";
}

