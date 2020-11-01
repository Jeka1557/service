<?php

function dt_filter_group($args, $has_return){
	if(!$field = $args['field'])
		return trigger_error("No <b>field</b> specified for group filter");

	if(!isset($args[0]) && !isset($args['data']))
		return trigger_error("data is not specified", E_USER_ERROR);
	
	if(isset($args['data']))
		$data = $args['data'];
	else 
		$data = $args[0];
		
	$groups = array();
	if(is_array($data)){
		foreach ($data as $_) {
			$groups[$_[$field]][] = $_;
		}
	}
	
	if(array_key_exists('groups', $args))
		$args['groups'] = array_keys($groups);
	if($has_return)
		return $groups;
	else{
		if(isset($args['data']))
			$args['data'] = $groups;
		else
			$args[0] = $groups;
			
		return true;
	}
}
