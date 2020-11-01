<?php

function dt_fninfo_load(){
	return array(
			'callback' => "dt_fn_load",
			'type' => DTC_FUNC_BOTH,
		);
}

/**
 * load function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_load($params, $open, $raw_params, &$self){
	if($open){
		$configs = $self->config['load_paths'];
		
		if(!$configs)
			$configs[] = '';
			
		$searched = array();
		
		$templ_name = $params[0];
		if(substr($templ_name, 0, 2) == './'){
//			$file_name = dirname($self->_caller->_template).'/'.$params[0].'.html';
			$file_name = dirname($self->_parsed_files[$self->_current_token['file']]).'/'.substr($templ_name, 2);
			if(substr($file_name, -5) != '.html')
				$file_name .= '.html';
			
			if(!file_exists($file_name)){
				$searched[] = $file_name;
				$file_name = false;
			}
		}
		elseif(!isset($templ_name{1}) || $templ_name{1} !== ':' || $templ_name{0} == '/'){
			foreach ($configs as $_) {
				if(substr($_, -1) != '/' && $_ != '')
					$_ .= '/';
					
				if(isset($_{1}) && $_{0} != '/' && $_{1} != ':')
					$file_name = $self->_caller->_template_dir.'/'.$_.$params[0].'.html';
				else
					$file_name = $_.$params[0].'.html';
					
				if(file_exists($file_name)){
					break;
				}
				else{
					$searched[] = $file_name;
					$file_name = false;
				}
			}
		}
		
		if(!$file_name)
			return $self->error('{load} - file not found (searched in ['.implode('], [', $searched)."])");
			
		$text = file_get_contents($file_name).'{/load}';
		
		$text = preg_replace('~\$\{(.*?)}~e', '$params["$1"]', $text);
		
		$tokens = $self->parse_tokens($text, array(), $file_name);
		
		$c = count($tokens);
		for($i = $c-1; $i >= 0; $i--){
			$self->unshift_token($tokens[$i]);
		}
		
		$self->set_dependency($file_name);
		
		$self->stack_push('load', $params[0], array('line' => $self->get_line(), 'name' => $self->get_template_name()));
	}
	else{
		$item = $self->stack_check();
		if($item['type'] !== 'load'){
			$i = count($self->stack) - 1;
			for($i; $i >=0; $i++){
				if($self->stack[$i]['type'] == 'load')
					break;
				$self->error("Unterminated {{$self->stack[$i]['type']}}");
			}
			return $self->error("Errors while loading file");
		}
		
		$self->stack_pop();
	}
}
