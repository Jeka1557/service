<?php

function dt_fninfo_sample_rt_hash(){
	return array(
			'callback' => "dt_fn_sample_rt_hash",
			'type' => DTC_FUNC_RT_HASH,
		);
}

/**
 * sample_rt_hash function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_sample_rt_hash($params){
	print_r($params);
}
