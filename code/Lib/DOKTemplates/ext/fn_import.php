<?php

/**
 * import function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_import($params, $open, $raw_params, &$self){
	$configs = $self->config['load_paths'];
	
	if(!$configs)
//		$configs[] = '';
		$configs = array();
	else
		$configs = array_filter($configs);
		
	$searched = array();

	$templ_name = $params[0];
	if(substr_compare($templ_name, '.html', -5) != 0)
		$templ_name .= '.html';
		
	if(substr($templ_name, 0, 2) == './'){
		$file_name = dirname($self->_parsed_files[$self->_current_token['file']]).'/'.substr($templ_name, 2);
		
		if(!file_exists($file_name)){
			$searched[] = $file_name;
			$file_name = false;
		}
	}
	elseif($templ_name{1} !== ':' && $templ_name{0} !== '/'){
		foreach ($configs as $_) {
			if(substr($_, -1) != '/')
				$_ .= '/';
				
			if($_{0} != '/' && $_{1} != ':')
				$file_name = $self->_caller->_template_dir.'/'.$_.$templ_name;
			else
				$file_name = $_.$templ_name;
			
			if(file_exists($file_name)){
				break;
			}
			else{
				$searched[] = $file_name;
				$file_name = false;
			}
		}
	}
	else{
		$file_name = $templ_name;
	}
	
	$file_name = str_replace('\\', '/', $file_name);

	$self->set_dependency($file_name);
	
	if(!$file_name)
		return $self->error('{import} - file not found (searched in ['.implode('], [', $searched)."])");
		
	if(isset($self->_compile_context['imported'][$file_name]) && empty($params['force']))
		return '';
		
	$self->_compile_context['imported'][$file_name] = true;
		
	$text = file_get_contents($file_name);
	
	//$text = preg_replace('~\$\{(.*?)}~e', '$params["$1"]', $text);
	$text = preg_replace_callback(
		'~\$\{(.*?)}~',
		function ($m) use ($params) {
			return $params[$m[1]];
		},
		$text
	);

	
	$tokens = $self->parse_tokens($text, array(), $file_name);
	
	// Логика работы простая - подпихиваем левую функцию вместо импорта
	// вместе со всем текстом, и дальше уже сам func подсосёт из неё всё, что нужно.
	// это работает до тех пор, пока не введены ограничения на имена вложенных функций.
	if(!isset($self->_compile_context['fn_import_dummy']))
		$self->_compile_context['fn_import_dummy'] = 0;
	$dummy = &$self->_compile_context['fn_import_dummy'];
	
	
	// Список функций, которые проходят через import в глобальном контексте 
	// (по умолчанию там всё трётся, кроме других import)
	if(!isset($self->config['import_allowed_functions']))
		$allowed_functions = array();
	else
		$allowed_functions = $self->config['import_allowed_functions'];
		
	$allowed_functions[] = 'import';
	$allowed_functions[] = 'func_extend';
	$allowed_functions[] = 'block';
	
	$new_tokens = array();
	while($token = array_shift($tokens)){
		if(($token['type'] == TOK_FUNC || $token['type'] == TOK_FUNC_CLOSE) && in_array($token['name'], $allowed_functions)){
			$new_tokens[] = $token;
		}
		elseif($token['type'] == TOK_FUNC && $token['name'] == 'func'){			
			$t = $self->parse_token_block($tokens, $token['name']);
			if($t){
				$new_tokens[] = $token;
				foreach ($t['tokens'] as $_) {
					$new_tokens[] = $_;
				}
				$_ = array(
						'type' => TOK_FUNC_CLOSE,
						'name' => 'func',
						'text' =>  '{/func}',
					);
					
				$new_tokens[] = $_;				
			}
			else{
				$t_params = $self->_parse_params($token['params']);
				$self->error("Couldn't import function '{$t_params[0]}'");
			}
		}
		elseif($token['type'] == TOK_FUNC && $token['name'] == 'funci'){
			$t = $self->parse_token_block($tokens, $token['name']);
			$_ = $token;
			$_['name'] = 'func';
			$_['text'] = str_replace('{funci', '{func', $_['text']);
			$new_tokens[] = $_;
			foreach ($t['tokens'] as $_) {
				$new_tokens[] = $_;
			}
			$_ = array(
					'type' => TOK_FUNC_CLOSE,
					'name' => 'func',
					'text' =>  '{/func}',
				);
				
			$new_tokens[] = $_;
			
		}
	}
	$self->unshift_token($new_tokens);
	
	$dummy++;
	
	return '';
//	return $self->_echo_start."'Compile time: ".date('H:i:s')."'".$self->_echo_end;
}
