<?php

/**
 * mod_decode
 * @author DoK
 * @return string
 * @param $params string
 * @param $expr string
 * @param $self DOK_Template_Compiler
 **/
function dt_mod_decode($expr, $params, &$self){
	$args = explode(',', $params);
	
	if(!isset($self->_compile_context['mod_decode_array_map']))
		$self->_compile_context['mod_decode_array_map'] = array();
		
	$array_map = &$self->_compile_context['mod_decode_array_map'];
	
	if(!isset($array_map[$params])){
		$num = count($array_map);
		$array_map[$params] = $num;
	}
	else
		$num = $array_map[$params];
	
	$array = "\$__tmpl_decode$num = array(";
	$i = 0;
	foreach ($args as $_) {
		$t = explode('=>', $_);
		$k = $t[0];
		$v = isset($t[1]) ? $t[1] : null;
		if($v === null){
			$v = $k;
			$k = $i++;		
		}
		
		$v = trim($v);
		
		if(!preg_match("~^(['\"]).*\\1$~", $v)){
			$v = '"'.addcslashes($v,'\\"').'"';			
		}
		
		$array .= "\n\t$k => $v,";
	}
	$array .= "\n);";
	
	$self->_initial_php["decode$num"] = $array;
	
	return "\$__tmpl_decode{$num}[$expr]";
}

