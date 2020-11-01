<?php

function dt_fninfo_extend(){
	return array(
			'callback' => "dt_fn_extend",
			'type' => DTC_FUNC_PAIRED,
		);
}

/**
 * extend function
 * @author DoK
 * @package OOP
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_extend($params, $open, $raw_params, &$self, $text, $tokens){
	$obj = new dt_fnclass_extend($self);
	return $obj->parse_extend($params, $tokens);
}

class dt_fnclass_extend {
	/**
	 * compiler
	 *
	 * @var DOK_Template_Compiler
	 */
	var $compiler;
	
	function dt_fnclass_extend(&$compiler){
		$this->compiler = &$compiler;
	}
	
	function make_file_name($file_name){
		if(!preg_match('~\.html$~', $file_name))
			$file_name .= '.html';
			
		$file_path = $this->compiler->_caller->_template_dir."/$file_name";
		if(!file_exists($file_path))
			return $this->compiler->error("File not found [$file_path]");

		return $file_path;	
	}
	
	function parse_extend($params, $extend){		
		$file_name = $this->make_file_name($params[0]);
		
		if(!file_exists($file_name))
			return $this->compiler->error("File [$file_name] not found");
		
		$tokens = $this->compiler->parse_tokens(file_get_contents($file_name), array(), $file_name);
		
		/**
		 * First step is used to find all extends and substitute them
		 * it calls this function recursively
		 */		
		$temp = array();
		reset($tokens);
		while($tok = current($tokens)){
			if($tok['type'] == TOK_FUNC && $tok['name'] == 'extend'){
				$params = $this->compiler->_parse_params($tok['params']);
				
				$c = 1;
				$extend_buf = array();
				while($c && current($tokens)){
					next($tokens);
					$extend_tok = current($tokens);
					if(!$extend_tok)
						break;
						
					if($extend_tok['type'] == TOK_FUNC && $extend_tok['name'] == 'extend') $c++;
					if($extend_tok['type'] == TOK_FUNC_CLOSE && $extend_tok['name'] == 'extend') $c--;
					
					if($c)
						$extend_buf[] = $extend_tok;
				}
				
				foreach ($this->parse_extend($params, $extend_buf) as $tok) {
					$temp[] = $tok;					
				}
				
				if($c){
					return $this->compiler->error('wrong extend hierarchi', $tok);
				}
			}
			else
				$temp[] = $tok;			
			
			next($tokens);
		}
		
		$tokens = $temp;
		
		/**
		 * This block is used to collect information about parts within extended file.
		 * At this step I want to get an array like
		 * $parts = array(
		 *   	0 => token,
		 *		'part_name' => array(
		 * 			0 => part_start_token,
		 * 			1 => some_token,
		 * 			'sub_part_name' => array(...),
		 * 			2 => token,
		 * 			...
		 * 			n => part_end_token
		 * 		),
		 * 	)
		 */
		$parts = array();
		$stack = array();
		$current = &$parts;
		
		foreach ($tokens as $tok) {
			if($tok['type'] == TOK_FUNC){
				if($tok['name'] == 'part'){
					$params = $this->compiler->_parse_params($tok['params']);
					$stack[] = &$current;
					
					$t = &$current[$params[0]];
					unset($current);
					$current = &$t;
					$current[] = $tok;
				}
			}
			elseif($tok['type'] == TOK_FUNC_CLOSE){
				if($tok['name'] == 'part'){
					if(!$stack){
						return $this->compiler->error('Wrong {/part}');
					}
					
					$current[] = $tok;
					end($stack);
					$current = &$stack[key($stack)];
					array_pop($stack);					
				}
			}
			else{
				$current[] = $tok;
			}
		}
		
		unset($t, $current, $stack, $tok);
		
		/**
		 * Now I'm going to parse parts part of file
		 * Note: extends within extending parts are not allowed
		 * Note: all tokens not within extending parts are ignored
		 */
		$new_parts = array();
		reset($extend);
		while($tok = current($extend)){
			if($tok['type'] == TOK_FUNC && $tok['name'] == 'part'){
				$params = $this->compiler->_parse_params($tok['params']);
				$part_name = $params[0];
				if($part_name{0} != '/')
					$part_name = '/'.$part_name;
				$new_parts[$part_name] = array();
				$c = 1;
				while ($c && current($extend)) {
					next($extend);				
					$t = current($extend);
					if(!$t) break;
					
					if($t['type'] == TOK_FUNC && $t['name'] == 'part') $c++;
					if($t['type'] == TOK_FUNC_CLOSE && $t['name'] == 'part') $c--;
					
					if($c){
						$new_parts[$part_name][] = $t;
					}
				}
				
				if($c){
					return $this->compiler->error("Wrong part hierarchy", $tok);
				}
			}
			
			next($extend);
		}
		
		/**
		 * Final step: combine it all :)
		 */
		
		$new = array();
		$this->final_combine($parts, $new_parts, $new, '', $parts);
		if($this->compiler->_step == 2){
			foreach ($new as $_) {
				print $_['text'];
			}
		}
		print "\n====\n";
		
		return $new;
	}
	
	function linearize($parts){
		$new = array();
		foreach ($parts as $k => $v) {
			if(is_int($k))
				$new[] = $v;
			else
				foreach ($this->linearize($v) as $_) {
					$new[] = $_;
				}
		}
		
		return $new;
	}
	
	function final_combine($parts, $extends, &$tokens, $path, $global_parts, $tree = array()){
		/* @var $this->compiler DOK_Template_Compiler */
		if(isset($extends[$path])){
			if(isset($tree[$path])){
				return $this->compiler->error("Recursion in [$path]");
			}
			
			$tree[$path] = 1;
			
			// Надо скопировать первый (и последний) элементы - {part xxx}
			$tokens[] = reset($parts);
			foreach ($extends[$path] as $_) {
				/**
				 * Если встречаем {parent} - делаем подстановку родительского 
				 * элемента без наследования т.е. эквивалент
				 * parent::xxx()
				 */
				if($_['type'] == TOK_FUNC && $_['name'] == 'parent'){
					$params = $this->compiler->_parse_params($_['params']);
					
					/**
					 * Разбор относительных/абсолютных путей
					 */
					$parent_path = $params[0];
					if(!$parent_path){
						$parent_path = $path;
					}
					elseif($parent_path{0} != '/'){
						$parent_path = $path.'/'.$parent_path;
					}
										
					$parent = $this->locate_path($global_parts, $parent_path);
					if(!$parent)
						return $this->compiler->error("Can't locate parent", $_);
					else{
						// При втавке родителя отсекается первый и последний
						// токены - {part xxx}
						$keys = array_keys($parent);
						for($i = 1; $i < count($keys) - 1; $i++){
							$key = $keys[$i];
							if(is_int($key))
								$tokens[] = $parent[$key];
							else{
								foreach ($this->linearize($parent[$key]) as $j){
									$tokens[] = $j;
								}
							}
						}
					}
				}
				elseif($_['type'] == TOK_FUNC && $_['name'] == 'this'){
					$params = $this->compiler->_parse_params($_['params']);
					$parent_path = $params[0];
					if(!$parent_path){
						$parent_path = $path;
					}
					elseif($parent_path{0} != '/'){
						$parent_path = $path.'/'.$parent_path;
					}
					
					$parent = $this->locate_path($global_parts, $parent_path);
					if(!$parent)
						return $this->compiler->error("Can't locate parent", $_);
					else{
						if(isset($extends[$parent_path]) && $parent_path != $path){
							if(!$this->final_combine($parent, $extends, $tokens, $parent_path, $global_parts, $tree))
								return false;
						}
						else{						
							$keys = array_keys($parent);
							for($i = 1; $i < count($keys) - 1; $i++){
								$key = $keys[$i];
								if(is_int($key))
									$tokens[] = $parent[$key];
								else{
									if(!$this->final_combine($parent[$key], $extends, $tokens, $parent_path.'/'.$key, $global_parts, $tree))
										return false;
								}
							}
						}
					}
				}
				else{
					$tokens[] = $_;
				}
			}
			$tokens[] = end($parts);
			
		}
		else{
			foreach ($parts as $k => $tok) {
				if(is_int($k)){
					$tokens[] = $tok;
				}
				else{
					if(!$this->final_combine($parts[$k], $extends, $tokens, $path.'/'.$k, $global_parts, $tree))
						return false;
				}
			}
		}
		
		return true;
	}
	
	function locate_path($parts, $path){
		$path = explode('/', $path);
		$cur = &$parts;
		foreach ($path as $_) {
			if($_){
				if(isset($cur[$_])){
					$t = &$cur[$_];
					unset($cur);
					$cur = &$t;
				}
				else{
					return false;
				}
			}
		}
		
		return $cur;
	}
}
