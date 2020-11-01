<?php

function dt_filter_filter($args, $has_return){
	if(!isset($args[0]) || !isset($args[1]))
		return trigger_error("wrong parameters", E_USER_ERROR);
		
	$new = array();
	
	$map = array_flip($args[1]);
	
	foreach ($args[0] as $k => $row) {		
		if(array_key_exists($k, $map))
			$new[$k] = $row;
	}
	
	if($has_return)
		return $new;
	else{
		$args[0] = $new;
		return true;
	}
}
