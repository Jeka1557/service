<?php

include_once('func_utils.php');

/**
 * func_extend function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_func_extend($params, $open, $raw_params, &$self){
	dt_func_utils::init($self);

	if($self->_step == 1){
		if(!empty($params['_virtual'])){
			$virtual = $params['_virtual'];
			unset($params['_virtual']);
		}
		else
			$virtual = false;
			
	//	if(!empty($params['_copy'])){
	//		$copy = true;
	//		unset($params['_copy']);
	//	}
	//	else
	//		$copy = false;
		
		if(count($params) < 1)
			return $self->error("Not enough params for func_extend function at least 1 is required");
			
		reset($params);
		if(!is_int(key($params))){
			$who = key($params);
			$whom = current($params);
		}
		else{
			if(count($params) < 3 || isset($params[1]) ||  $params[1] != 'is' || !isset($params[0]) || !isset($params[2])){
				return $self->error("Not enough params or wrong params for func_extend function. Usage: <em>{func_extend child is parent}</em>");
			}
			
			$who = $params[0];
			$whom = $params[1];
		}
	
		$functionParent = &dt_func_utils::locate_func($self, $whom, false);
		if(!$functionParent)
			return $self->error("Function '$whom' to be extended not found");
		
		$function = &dt_func_utils::locate_func($self, $who, true);
		$function['name'] = $who;
		$function['extend'] = $whom;
		
		if($virtual !== false)
			$function['virtual'] = $virtual;	
	}
	
	return '';
}
