<?php

class dt_func_utils {
	/**
	 * @param $self DOK_Template_Compiler
	 * @param string $func_name имя функции
	 * @return array
	 **/
	function &create_func(&$self, $func_name){
		$names = explode('.', $func_name);
		$context = &$self->_compile_context;
		
		$functions = array();
		
		foreach ($names as $i => $_) {
			$functions[$i] = &$context['functions'][$_];
			
			if(!isset($context['functions'][$_])){
				$context['functions'][$_] = array();
				$func_found = true;
			}
			
				
			$t = &$context['functions'][$_];
			$context = &$t;
			unset($t);
		}

		/**
		 * Надо проверить, нельзя ли найти предыдущие версии этой функции
		 */
		if(empty($functions[$i]['extended'])){
			for($j = $i; $j >= 0; $j--){
				if(!empty($functions[$j]['extend'])){
					// если нашли - конструируем имя функции-прототипа
					$extend_name = $functions[$j]['extend'].($i != $j ? '.' : '').implode('.', array_slice($names, $j + 1, $i - $j));
					
					$extend_func = &self::get_function_entry($self, $extend_name, false);
					if($extend_func != false){
						
//						self::_materialize_function($functions[$i], $self);
						if(empty($extend_func['versions']))
							if(!self::_materialize_function($extend_func, $self)){
								return $self->error("Error while materializing function '$extend_func'");
							}
							
						$functions[$i]['extended'] = true;
						$functions[$i]['versions'] = $extend_func['versions'];
						foreach ($functions[$i]['versions'] as $k => $_) {
							self::_back_fix_calls($functions[$i]['versions'][$k]['tokens'], $func_name, $extend_name, $self);
						}
						
						break;
					}
				}
			}
		}
		
		return $context;
	}
	
	/**
	 * @param $self DOK_Template_Compiler
	 * @param string $func_name имя функции
	 * @return array
	 **/
	function &locate_func(&$self, $func_name, $create, $implement = false){
		$names = explode('.', $func_name);
		$context = &$self->_compile_context;
		
		$functions = array();
		
		foreach ($names as $i => $_) {
			$functions[$i] = &$context['functions'][$_];
			
			if(!isset($context['functions'][$_])){
				if($create){
					$context['functions'][$_] = array();
					$func_found = true;
				}
				else{
					$t = $self->error("Can't find function namespace for '$func_name' error at '".implode('.', array_slice($names, 0, $i + 1))."'");
					return $t;
				}
//					$func_found = false;
//			}
//			else
//				$func_found = true;			
//			
//			if(!$func_found || (!$create && $implement && $func_found && empty($context['functions'][$_]['versions']))){
//				// Если мимо - то пытаемся найти наследование выше по контекстам					
//				$found = false;
//				
//				for($j = $i; $j >= 0; $j--){
//					if(!empty($functions[$j]['extend'])){
//						// если нашли - конструируем имя функции-прототипа
//						$extend_name = $functions[$j]['extend'].($i != $j ? '.' : '').implode('.', array_slice($names, $j + 1, $i - $j));
//						$extend_func = &self::locate_func($self, $extend_name, $create, $implement);
//						
//						// Вот интересно, этот if не выйдет боком?
//						// тут интересные вещи с наследованием могут всплыть
//						if($extend_func){
//							if(!$func_found){
//								// Создаём искомую фунцию
//								$context['functions'][$_] = array(
//										'name' => $func_name,
//									);
//							}
//								
//							if(!empty($extend_func['versions']))
//								$context['functions'][$_]['versions'] = $extend_func['versions'];
//								
//							// Говорим, что нашли и брык
//							$found = true;
//							break;
//						}
//					}
//				}
//				
//				if(!$found){
//					$t = $self->error("Can't find function namespace for '$func_name' error at '".implode('.', array_slice($names, 0, $i + 1))."'");
//					return $t;
//				}
			}
			
				
			$t = &$context['functions'][$_];
			$context = &$t;
			unset($t);
		}
		
		return $context;
	}
	
	function check_call_tokens(&$tokens, $func_name, &$self){
		$parts = explode('.', $func_name);
		for($i = 0, $cnt = count($parts); $i < $cnt; $i++){
			$call_hunt = $parts[$i] . '.';
			$call_hunt_len = strlen($call_hunt);
			
			foreach ($tokens as $k => $v) {  
				if($v['type'] == TOK_FUNC && ($v['name'] == 'call' || $v['name'] == 'calli'))
					self::check_call($tokens[$k], $i, $call_hunt, $call_hunt_len, $self);
			}
		}
	}
	
	function check_call(&$tok, $depth, $call_hunt, $call_hunt_len, &$self){
		if($tok['type'] == TOK_FUNC && ($tok['name'] == 'call' || $tok['name'] == 'calli')){
			$params = $self->_parse_params($tok['params']);
			
			$func_name = $params[0];
			
			// search name
			// TODO: optimize strpos -> strncmp
			if(strpos($params[0], $call_hunt) === 0){
				$params[0] = substr($params[0], $call_hunt_len);
				$params['_self_call'] = $depth;
				
				$params = $self->_compose_params($params);
				$tok['params'] = $params;
				$tok['text'] = "{{$tok['name']} {$tok['params']}}";
			}
		}
	}
	
	function &locate_function_version(&$self, &$params, &$func_name, &$name_prefix, &$function_version, &$func_desc_result, &$is_old){
		$name_prefix = '';
		if(isset($params['_self_call'])){
			$self_call = $params['_self_call'];
			unset($params['_self_call']);

			$top = count($self->_compile_context['functions_current']) - 1;
			$caller = $self->_compile_context['functions_current'][$top][0];
			$caller_parts = explode('.', $caller);
			$cnt = count($caller_parts);
			if($cnt < $self_call){
				$t = $self->error("Something is wrong. Caller has less parts than _self_call");
				return $t;
			}
			
			for($i = 0; $i <= $self_call; $i++){
				$name_prefix .= $caller_parts[$i] . '.';
			}

//			$top = count($self->_compile_context['functions_namespaces']) - 1;
//			for($i = 0; $i <= $self_call; $i++){
////			for($i = $top - $self_call; $i <= $top; $i++){
//				$name_prefix .= $self->_compile_context['functions_namespaces'][$i].'.';
//			}
		}
		else
			$self_call = false;
		
		$func_name = array_shift($params);
		
		if($func_name == '_old'){
			$is_old = true;
			if(empty($self->_compile_context['functions_current']))
				return $self->error("Can't use _old function outside function body");
				
			list($func_name, $last_version) = end($self->_compile_context['functions_current']);
			
			if($last_version == 0){
				return $self->error("Can't call _old function in original function body");
			}
			
			$function_version = $last_version - 1;
	
			// Здесь и так будет полное имя - просто $func_name
			$func_desc = &dt_func_utils::locate_function_real($self, $func_name);
			if(!$func_desc){
				$t = $self->error("Не найдена функция $func_name");
				return $t;
			}
		}
		else{
			$is_old = false;
//			do{				
				$func_desc = &dt_func_utils::locate_function_real($self, $name_prefix.$func_name);
				if(!$func_desc){
					$t = $self->error("Не найдена функция '$name_prefix$func_name'");			
					return $t;
				}
		
				$function_version = count($func_desc['versions']) - 1;				
//			}
//			while($function_version === false);
		}
		
		// Важно! для call тоже будет вызвано, хотя теоретически необходимости нет		
		$func_desc['inline_used'] = true;
		$function = &$func_desc['versions'][$function_version];
		
		$func_desc_result = array(&$func_desc);
		
		return $function;
	}
	
	/**
	 * @param $self DOK_Template_Compiler
	 * @param string $func_name имя функции
	 * @return array
	 **/
	function &locate_function_real(&$self, $func_name){
		$descr = &self::get_function_entry($self, $func_name);
		
		if(!$descr){
			return $descr;
		}
		
		if(empty($descr['versions']))
			self::_materialize_function($descr, $self);
			
		return $descr;
	}
	
	/**
	 * Возвращает описание функции
	 *
	 * @param DOK_Template_Compiler $self
	 * @param string $func_name полное имя функции
	 */
	function &get_function_entry(&$self, $func_name, $errors = true){
		$names = explode('.', $func_name);
		$context = &$self->_compile_context;
		
		$functions = array();
		
		$count_names = count($names);
		
		foreach ($names as $i => $_) {
			$functions[$i] = &$context['functions'][$_];
			
			if(!isset($context['functions'][$_]) || empty($context['functions'][$_]['name'])){
				// Если мимо - то пытаемся найти наследование выше по контекстам					
				$found = false;
				
				for($j = $i; $j >= 0; $j--){
					if(!empty($functions[$j]['extend'])){
						// если нашли - конструируем имя функции-прототипа
						$extend_name = $functions[$j]['extend'].($i != $j ? '.' : '').implode('.', array_slice($names, $j + 1, $i - $j));
//						$extend_func = &self::get_function_entry($self, $extend_name);
						
						// Вот интересно, этот if не выйдет боком?
						// тут интересные вещи с наследованием могут всплыть
//						if($extend_func){
							// Создаём искомую фунцию
							$context['functions'][$_]['name'] = implode('.', array_slice($names, 0, $i + 1));
//							$context['functions'][$_]['name'] = $func_name;
							$context['functions'][$_]['extend'] = $extend_name;
								
//							if(!empty($extend_func['versions']))
//								$context['functions'][$_]['versions'] = $extend_func['versions'];
								
							// Говорим, что нашли и брык
							$found = true;
							break;
//						}
					}
				}
				
				if(!$found && $i == $count_names - 1){
					if($errors)
						$t = $self->error("Can't find function namespace for '$func_name' error at '".implode('.', array_slice($names, 0, $i + 1))."'");
					else
						$t = false;
						
					return $t;
				}
			}
			
				
			$t = &$context['functions'][$_];
			$context = &$t;
			unset($t);
		}
		
		return $context;
	}

	/**
	 * Инициализирует модуль функций для данного компилятора
	 *
	 * @param DOK_Template_Compiler $self
	 */
	function init(&$self){
		if(isset($self->_compile_context['functions']))
			return;
		
		$context = &$self->_compile_context;
		$context['functions'] = array();
		$context['functions_calli_dummy_vars'] = 0;
		$context['functions_calli_stack'] = array();
		$context['functions_current'] = array();
		$context['functions_namespaces'] = array();
		$context['functions_compiled'] = array();
		
		$self->register_post_step(array(__CLASS__, 'clean'));
	}
	
	/**
	 * Компилирует функцию
	 *
	 * @param array $func_desc массив, описывающий функции
	 * @param array $function массив, описывающий версию функции
	 * @param DOK_Template_Compiler $self
	 * 
	 * @return string имя получившейся функции
	 */
	function compile_function(&$func_desc, &$self, $version = null){
		if(empty($func_desc['versions'])){
			if(!empty($func_desc['extend']))
				self::_materialize_function($func_desc, $self);
			else
				return $self->error("Empty function '{$func_desc['name']}' found without extend");
		}
		
		if(is_null($version))
			$version = max(array_keys($func_desc['versions']));
			
		// TODO: опять же, это - хак.
		$params = $self->_params;
		if(isset($params['no_func']))
			unset($params['no_func']);
		$uniq_info = $self->_template_name.serialize($params);
		
		// TODO: это - хак, надо бы сделать правильнее.
		if(isset($self->_caller->_ext_params))
			$uniq_info .= serialize($self->_caller->_ext_params);
			
		$php_fn_name = "__tmpl_f_".str_replace('.', '__', $func_desc['name'])."_{$version}_".substr(md5($uniq_info), 0, 12);
		
		if(empty($func_desc['compiled'][$version])){
			$func_desc['compiled'][$version] = $php_fn_name;

			$function = $func_desc['versions'][$version];
			$func_name = $func_desc['name'];
			
			$top = count($self->_compile_context['functions_current']) - 1;
			$is_old = $top >= 0 &&$self->_compile_context['functions_current'][$top][0] == $func_name;
			
			$self->_compile_context['functions_current'][] = array($func_name, $version);
			
			if(!$is_old){
				$func_parts = explode('.', $func_name);
				foreach ($func_parts as $_) {
					$self->_compile_context['functions_namespaces'][] = $_;
				}
			}
			
			$class = get_class($self);			
			$compiler = new $class($self->_params + array('no_func' => true), $self->_caller);
			/* @var $compiler DOK_Template_Compiler */
			
			$compiler->ext_dirs = $self->ext_dirs;

			$compiler->_compile_context['functions'] = &$self->_compile_context['functions'];			
			$compiler->_compile_context['functions_current'] = &$self->_compile_context['functions_current'];
			$compiler->_compile_context['functions_namespaces'] = &$self->_compile_context['functions_namespaces'];
			$compiler->_compile_context['functions_compiled'] = &$self->_compile_context['functions_compiled'];
			$compiler->_compile_context['functions_subcompiler'] = true;
			$compiler->_parsed_files = $self->_parsed_files;
			// Походу 1 два раза прибавляется - один раз надо вычесть
			$compiler->set_line_offset($function['line'] - 1);
			
			$code = $compiler->compile($function['text'], 0, $self->_template_name);
			if($code === false){
				return $self->error("Compilation of function failed");
			}
			
			array_pop($self->_compile_context['functions_current']);
			
			if(!$is_old){
				foreach ($func_parts as $_) {
					array_pop($self->_compile_context['functions_namespaces']);
				}			
			}
			
			$compiler->_compile_context['functions_compiled'][$func_desc['name']][$version] = "if(!function_exists('$php_fn_name')){\n\tfunction $php_fn_name(".implode(', ', $function['params'])."){{$self->_php_end}$code{$self->_php_start}\t}\n}";
		}
		else{
			if($func_desc['compiled'][$version] != $php_fn_name)
				trigger_error("Не совпадают сгенерированные имена функций в щаблонах, надо найти в чём дело.", E_USER_WARNING);
				
			return $func_desc['compiled'][$version];
		}
		
		return $php_fn_name;
	}
	
	/**
	 * Завершает компиляцию
	 *
	 * @param null $params
	 * @param DOK_Template_Compiler $self
	 */
	function clean($params, &$self){
		if($self->_step == 1 || !empty($self->_compile_context['functions_subcompiler']))
			return; // На первом шаге это не нужно
		
		// Готовим виртуальные функции, важно, что это должно делаться ДО
		// сбора компилированных функций, т.к. некоторые может понадобиться
		// компилировать.
		self::_prepare_virtual($self->_compile_context['functions'], $self);
		
		// И все скомпилированные функции добавляем сюда. Важно, чтобы они были в 
		// глобальном контексте, т.к. вызовы у нас не ограничены пространствами имён
		// и может быть ситуация, когда функция локальна в другой функции, но вызов внешний
		// и функция получится неопределена.
		foreach ($self->_compile_context['functions_compiled'] as $k => $versions) {
			foreach ($versions as $version => $_) {
				$self->_initial_php["fn_{$k}__$version"] = $_;
			}
		}
	}
	
	/**
	 * Пробегает по дереву функций и компилирует вирутальные/собирает информацию
	 * о них в массив $__tmpl_fn_virtual.
	 * 
	 * @todo Придумать чистку глобальных переменных
	 *
	 * @param null $params
	 * @param DOK_Template_Compiler $self
	 */
	function _prepare_virtual(&$functions, &$self){
		foreach ($functions as $k => $description) {
			if(!empty($description['virtual'])){
				$func_name = self::compile_function($functions[$k], $self);
				
				$self->_initial_php["fn_virtual"] = 
					  "\$GLOBALS['__tmpl_fn_virtual'][] = array();\n"
					. "\$GLOBALS['__tmpl_fn_virtual_top'] = &\$GLOBALS['__tmpl_fn_virtual'][count(\$GLOBALS['__tmpl_fn_virtual']) - 1];\n";
				$self->_initial_php["fn_virtual_{$description['name']}"] = "\$GLOBALS['__tmpl_fn_virtual_top']['{$description['virtual']}'] = '$func_name';";
			}
			
			if(!empty($functions[$k]['functions']))
				self::_prepare_virtual($functions[$k]['functions'], $self);
		}
	}
	
	function _materialize_function(&$func_desc, &$self){
		if(empty($func_desc['extend']))
			return $self->error("No extend exists in function desc '{$func_desc['name']}' couldn't materialize it.");
			
		$extend = &self::get_function_entry($self, $func_desc['extend'], true);
		// Считаем, что правильное - проверяется при создании
		
		if($extend !== false && empty($extend['versions']))
			if(!self::_materialize_function($extend, $self))
				return false;
		
		$func_desc['versions'] = $extend['versions'];
		foreach ($func_desc['versions'] as $k => $version) {
			self::_back_fix_calls($func_desc['versions'][$k]['tokens'], $func_desc['name'], $func_desc['extend'], $self);
			
			$text = '';
			foreach ($func_desc['versions'][$k]['tokens'] as $_) {
				$text .= $_['text'];
			}
			
			$func_desc['versions'][$k]['text'] = $text;
		}
		
		return true;
	}
	
	/**
	 * Эта функция восстанавливает self_call'ы при материализации наследования
	 *
	 * @param array $tokens
	 * @param string $old_name
	 * @param string $new_name
	 * @param DOK_Template_Compiler $self
	 */
	function _back_fix_calls(&$tokens, $name, $extend_name, &$self){
		$name = explode('.', $name);
		$extend = explode('.', $extend_name);
		$n = count($extend) - 1; // Поправка на то, что были в старом неймспейсе
		$i = count($name) - 1; // Поправка на то, что теперь в новом неймспейсе и стало быть надо сдвинуть вызов
		
		foreach ($tokens as $k => $tok) {
			if($tok['type'] == TOK_FUNC && ($tok['name'] == 'call' || $tok['name'] == 'calli')){
				$params = $self->_parse_params($tok['params']);
				if(isset($params['_self_call'])){
					if($params['_self_call'] >= $n - $i)
						$params['_self_call'] -= $n - $i;
					else{ // Коррекция
						$params[0] = implode('.', array_slice($extend, 0, $params['_self_call'] + 1)).'.'.$params[0];
						unset($params['_self_call']);
					}
					
					$params = $self->_compose_params($params);
					$tok['params'] = $params;
					$tok['text'] = "{{$tok['name']} {$tok['params']}}";
					
					$tokens[$k] = $tok;
				}
			}
		}
	}
}
