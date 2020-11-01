<?php

include_once('fn_funci.php');

function dt_fninfo_smart_form_qs(){
	return array(
			'callback' => "dt_fn_smart_form_qs",
			'type' => DTC_FUNC_PAIRED,
		);
}


/**
 * func function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_smart_form_qs($params, $open, $raw_params, &$self, $text, $tokens){
	$self->unshift_token($tokens);
	
	// look for vars
	$rx = '~<(input|textarea|select)[^>]*?name\s*=\s*(\'[^\']+\'|\"[^"]+\"|[\s]+)~';
	$matches = array();
	
	$remove_str = '';
	
	$skip_array = array();
	if(isset($params['skip'])){
		foreach (explode(',', $params['skip']) as $_) {
			$_ = trim($_);
			$skip_array[$_] = true;
		}
	}
	
	if(preg_match_all($rx, $text, $matches, PREG_SET_ORDER)){
		foreach ($matches as $_) {
			$match = $_[2];
			if((substr($match, 0, 1) == '"' and substr($match, -1) == '"')
				|| (substr($match, 0, 1) == "'" and substr($match, -1) == "'")){
					$match = substr($match, 1, -1);
			}
			
			if(preg_match('~^([^\[]+)\[.*\]$~', $match, $m)){
				$match = $m[1];
			}
				
			if(!isset($skip_array[$match])){
				$remove_str .= '"' . addslashes($match) . '", ';
			}
		}		
	}
	
	if(isset($params['remove'])){
		foreach (explode(',', $params['remove']) as $_) {
				$remove_str .= '"' . addslashes($_) . '", ';
		}
	}
	
	return $self->_echo_start . "get_form_qs(array(), array($remove_str))" . $self->_echo_end;
}
