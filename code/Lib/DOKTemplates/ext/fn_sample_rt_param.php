<?php

function dt_fninfo_sample_rt_param(){
	return array(
			'callback' => "dt_fn_sample_rt_param",
			'type' => DTC_FUNC_RT_PARAM,
		);
}

/**
 * sample_rt_param function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_sample_rt_param($param1, $param2){
	print_r($param1 + $param2);
}
