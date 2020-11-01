<?php

function dt_fninfo_block(){
	return array(
			'callback' => "dt_fn_block",
			'type' => DTC_FUNC_PAIRED,
		);
}

/**
 * block function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_block($params, $open, $raw_params, &$self, $text, $tokens){
	$block = array_shift($params);
	
	if(isset($self->_params['block']) && $self->_params['block'] == $block){
		$self->unshift_token($tokens);
	}
	else{
		$self->set_line($self->get_line() + substr_count($text, "\n"));
	}
}
