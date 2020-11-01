<?php

include_once('fn_funci.php');
include_once('func_utils.php');

function dt_fninfo_func(){
	return array(
			'callback' => "dt_fn_func",
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
function dt_fn_func($params, $open, $raw_params, &$self, $text, $tokens, $parentDepth = null){
	dt_func_utils::init($self);
	
	if($self->_step == 1){
		$func_name = array_shift($params);
		
		$depth = substr_count($func_name, '.');
			
		$func_parts = explode('.', $func_name);
		$call_hunt = $func_parts[$depth].'.';
		$call_hunt_len = strlen($call_hunt);
		$full_hunt = implode('.', array_slice($func_parts, 0, $depth + 1)).'.';
		$full_hunt_len = strlen($full_hunt);

		$function = &dt_func_utils::create_func($self, $func_name);
//		$function = &dt_func_utils::get_function_entry($self, $func_name);
		$function['name'] = $func_name;
			
		if(!empty($function['inline_used']))
			return $self->error("Redefinition of function '{$func_name}' which was once used as inline is not allowed. (Нельзя переопределять функцию, которая хотя бы раз успела использоваться как inline)");
		
		if(!empty($params['_virtual'])){
			$function['virtual'] = $params['_virtual'];
			unset($params['_virtual']);
		}
			
		// Парсим внутренние функции, simple - то, что останется в конце концов
		$simple = array();
		
		$tok = reset($tokens);
		$version_line = $tok['line'];
		
		while($tok = array_shift($tokens)){
			if($tok['type'] == TOK_FUNC && ($tok['name'] == 'func' || $tok['name'] == 'funci')){
				$r = $self->parse_token_block($tokens, $tok['name']);
				if(!$r)
					return $self->error("Не возможно найти пару к функции '{$tok['name']}", $tok);
				
				$localParams = $self->_parse_params($tok['params']);
				
//				foreach ($r['tokens'] as $k => $v) {
//					dt_func_utils::check_call($r['tokens'][$k], $depth, $call_hunt, $call_hunt_len, $self);
//				}
				
				dt_fn_func($localParams, true, '', $self, $r['text'], $r['tokens'], $depth + 1);
				
				if($tok['name'] == 'funci'){
					$tok['name'] = 'calli';
					
//					dt_func_utils::check_call($tok, $depth, $full_hunt, $full_hunt_len, $self);
					
					$tok['text'] = "{{$tok['name']} {$tok['params']}}";
					
					$simple[] = $tok;
				}
			}
			else{
//				dt_func_utils::check_call($tok, $depth, $call_hunt, $call_hunt_len, $self);
				$simple[] = $tok;
			}
		}
		
		dt_func_utils::check_call_tokens($simple, $func_name, $self);
		
		$new_text = '';
		foreach ($simple as $_) {
			$new_text .= $_['text'];
		}
		
		$function['versions'][] = array(
				'params' => $params,
				'tokens' => $simple,
				'text' => $new_text,
				'line' => $version_line,
			);
	}
	
	return '';
}
