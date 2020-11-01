<?php

function dt_fninfo_json(){
	return array(
			'callback' => "dt_fn_json",
			'type' => DTC_FUNC_RT_PARAM,
		);
}

/**
 * json function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_json($var, $print = true){
	if(is_array($var)){
		reset($var);
		$first = key($var);
		end($var);
		$last = key($var);
		if(!count($var) || is_int($first) && is_int($last) && $first == 0 && $last == count($var) - 1){
			$out = "[\n";
			foreach ($var as $k => $v) {
				if($out !== "[\n") $out .= ",\n";
				$out .= dt_fn_json($v, false);
			}
			$out .= "\n]";
		}
		else{
			$out = "{\n";
			foreach ($var as $k => $v) {
				if($out !== "{\n") $out .= ",\n";
				$out .= "'$k' : ".dt_fn_json($v, false);
			}
			$out .= "\n}";
		}
	}
	elseif(is_int($var)){
		$out = $var;
	}
	elseif(is_bool($var)){
		$out = $var ? 'true' : 'false';
	}
	elseif(is_null($var)){
		$out = 'null';
	}
	else{
		$out = "'".addcslashes($var, "\n\r\t'\"")."'";
	}
	
	if($print){
		print $out;
		return true;
	}
	else
		return $out;	
}
