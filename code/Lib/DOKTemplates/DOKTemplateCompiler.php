<?php

namespace Lib\DOKTemplates;

define('DTC_FUNC_BOTH', 1);
define('DTC_FUNC_SIMPLE', 2);
define('DTC_FUNC_PAIRED', 4);
define('DTC_FUNC_RT_HASH', 8);
define('DTC_FUNC_RT_PARAM', 16);
define('DTC_FUNC_TOKEN', 32);

define('TOK_TEXT', 1);
define('TOK_VAR', 2);
define('TOK_COMMENT', 3);
define('TOK_FUNC', 4);
define('TOK_FUNC_CLOSE', 5);
define('TOK_EXPR', 6);

/**
 * Компилятор шаблонов
 * @todo Исправить обрезание пробелов перед закрывающими функциями пример - close_func_space.txt
 * @author DoK
 **/
class DOKTemplateCompiler {
    
	public $short_tags = false;
	public $_last_block = -1;
	public $_line;
	public $_template_name;
	public $_compile_ok;
	public $_initial_php;

	/**
	 * @var DOKTemplate
	 **/
	public $_caller;

	public $stack = array();
	public $functions = array();
	public $modifiers = array();

	public $_php_start;
	public $_php_end;
	public $_space = '';
	public $_echo_start;
	public $_echo_end;
	public $_templ_start = '{';
	public $_templ_end = '}';

	public $_rx_ident;
	public $_rx_fn_name;
	public $_rx_var;
	public $_rx_var_name;
	public $_rx_expr;
	public $_rx_comment;
	public $_rx_modifier;
	public $_rx_func;
	public $_rx_open_bracket;
	public $_rx_close_bracket;

	public $_params;
	public $_pre_steps = array();
	public $_post_steps = array();

	public $_contexts = array();
	public $_step = 1;
	public $_meta = array();

	public $_pragma = array();

	public $_last_var_path = '';
	public $_dependencies = array();
	
	public $ext_dirs;
	public $config;
	
	public $_work_tokens;
	
	/**
	 * Токен, который в данный момент парсится, алиас
	 * 
	 * @var array
	 **/
	public $_current_token;
	
	/**
	 * Файлы, которые были пропарсены, сюда идут ссылки из токенов ['file']
	 * 
	 * @var array
	 **/
	public $_parsed_files;
	
	/**
	 * Переменная, которая используется для временных данных компиляции.
	 *
	 * @var array
	 */
	public $_compile_context = array();
	
	public $_line_offset = 0;

	public $_errors = [];

	public $_log_errors = false;

	public $_trigger_each_error = true;

	function __construct($params = array(), &$template){
		$this->_params = $params;
		$this->_caller = &$template;

		$this->register_function('loop', array(&$this, 'fn_loop'), DTC_FUNC_BOTH);
		$this->register_function('elseloop', array(&$this, 'fn_elseloop'));
		$this->register_function('context', array(&$this, 'fn_context'), DTC_FUNC_BOTH);
		$this->register_function('elsecontext', array(&$this, 'fn_elsecontext'));
		$this->register_function('if', array(&$this, 'fn_if'), DTC_FUNC_BOTH);
		$this->register_function('else', array(&$this, 'fn_else'));
		$this->register_function('elseif', array(&$this, 'fn_elseif'));
		$this->register_function('tmpl', array(&$this, 'fn_tmpl'), DTC_FUNC_BOTH);
		$this->register_function('filter', array(&$this, 'fn_filter'));
		$this->register_function('pragma', array(&$this, 'fn_pragma'));
//		$this->register_function('load', array(&$this, 'fn_load'));
//		$this->register_function('block', array(&$this, 'fn_block'), DTC_FUNC_BOTH);

		$this->register_function('cycle', array(&$this, 'fn_cycle'));
		$this->register_function('base_url', array(&$this, 'fn_base_url'));
		$this->register_function('qs', array(&$this, 'fn_qs'));
		$this->register_function('qs2', array(&$this, 'fn_qs2'));
		$this->register_function('qs_old', array(&$this, 'fn_qs_old'));
		$this->register_function('qs_prefix', array(&$this, 'fn_qs_prefix'));
		$this->register_function('form_qs', array(&$this, 'fn_form_qs'));
		$this->register_function('selected', array(&$this, 'fn_selected'));
		$this->register_function('checked', array(&$this, 'fn_checked'));
		$this->register_function('include', array(&$this, 'fn_include'));
		$this->register_function('file', array(&$this, 'fn_file'));
		$this->register_function('implode', array(&$this, 'fn_implode'));
		$this->register_function('join', array(&$this, 'fn_implode'));
		$this->register_function('callback', array(&$this, 'fn_callback'));
		$this->register_function('hidden', array(&$this, 'fn_hidden'));
		$this->register_function('sort', array(&$this, 'fn_sort'));
		$this->register_function('sort_old', array(&$this, 'fn_sort_old'));
		$this->register_function('session', array(&$this, 'fn_session'));
		$this->register_function('set', array(&$this, 'fn_set'));
		$this->register_function('capture', array(&$this, 'fn_capture'), DTC_FUNC_BOTH);


//		$this->register_modifier('date', array(&$this, 'mod_date'));
//		$this->register_modifier('db_date', array(&$this, 'mod_db_date'));
		$this->register_modifier('truncate', array(&$this, 'mod_truncate'));
		$this->register_modifier('nbsp', array(&$this, 'mod_nbsp'));
		$this->register_modifier('default', array(&$this, 'mod_default'));
		$this->register_modifier('switch', array(&$this, 'mod_switch'));
		$this->register_modifier('image_width', array(&$this, 'mod_image_width'));
		$this->register_modifier('image_height', array(&$this, 'mod_image_height'));
		$this->register_modifier('null', array(&$this, 'mod_null'));
		$this->register_modifier('rtrim', array(&$this, 'mod_rtrim'));
		$this->register_modifier('nl2br', array(&$this, 'mod_nl2br'));
		$this->register_modifier('format_par', array(&$this, 'mod_format_par'));
		$this->register_modifier('urlencode', array(&$this, 'mod_urlencode'));
		$this->register_modifier('addslashes', array(&$this, 'mod_addslashes'));
		$this->register_modifier('print_r', array(&$this, 'mod_print_r'));
		$this->register_modifier('pre', array(&$this, 'mod_pre'));
		$this->register_modifier('round', array(&$this, 'mod_round'));
		$this->register_modifier('htmlspecialchars', array(&$this, 'mod_htmlspecialchars'));
		$this->register_modifier('subs', array(&$this, 'mod_subs'));
		
		$this->_init_rx();
		$this->_init_conf();
	}
	
	function _init_conf(){
		if($this->_caller->config_path && file_exists($this->_caller->config_path)){
			$config = parse_ini_file($this->_caller->config_path);
			$this->_load_config($config);
		}
	}
	
	function _load_config($config){
		if(!empty($config['ext_dir'])){
			foreach ((array)$config['ext_dir'] as $_) {
				$this->ext_dirs[] = $_;
			}
		}		
		
		if($this->config)
			$this->config = $config + $this->config;
		else
			$this->config = $config;
	}
	
	function _init_rx(){
		$rx_ident = $this->_rx_ident = '[a-zA-Z_0-9]+';
		$rx_fn_name = $this->_rx_fn_name = '[a-zA-Z_][a-zA-Z_0-9]*';

		$rx_var_name = $this->_rx_var_name = "\\$((([#]($rx_ident)?|($rx_ident))((->|\.)$rx_ident)*:+)?|:)([#]($rx_ident)?|($rx_ident))((->|\.)$rx_ident)*";
		
		$this->_rx_open_bracket = $rx_open_bracket = preg_quote($this->_templ_start);
		$this->_rx_close_bracket = $rx_close_bracket = preg_quote($this->_templ_end);
		
		$this->_rx_modifier = $rx_modifier = 
			"\\|(
				[ \t]*[a-zA-Z0-9_]+[ \t]* #name
					(= #equal
						(	
							(?=\||$rx_close_bracket) 					# just an empty string
							| [^'\"\n][^\n]*?(?=$rx_close_bracket|\|) 	# simple expression
							|\"[^\"\n]*\" 		# double quotes
							|'[^'\n]*' 		# single quotes
						)
					)?
				)[ \t]*";
			
//		$this->_rx_modifier = $rx_modifier = 
//			"\\|([ \t]*[a-zA-Z0-9_]+[ \t]*(=([^'\"|}][^|\n}]*|\"[^\"\n}]+\"|'[^'\n}]+'))?)[ \t]*";
		$rx_var = $this->_rx_var = "$rx_open_bracket$rx_var_name($rx_modifier)*$rx_close_bracket";
		$this->_rx_expr = $rx_expr = "(?m)$rx_open_bracket@(.+?)$rx_close_bracket(?-m)";
		$this->_rx_func = $rx_func = "$rx_open_bracket/?$rx_fn_name(.*?)$rx_close_bracket";
		$this->_rx_comment = $rx_comment = "(?m)$rx_open_bracket\\*(.*?)\\*$rx_close_bracket(?-m)";
		
		/* old
		$rx_ident = $this->_rx_ident = '[a-zA-Z_0-9]+';
		$rx_fn_name = $this->_rx_fn_name = '[a-zA-Z_][a-zA-Z_0-9]*';

		$rx_var_name = $this->_rx_var_name = "\\$(((#($rx_ident)?|($rx_ident))((->|\.)$rx_ident)*:+)?|:)(#($rx_ident)?|($rx_ident))((->|\.)$rx_ident)*";
		$this->_rx_modifier = $rx_modifier = 
			"\\|([ \t]*[a-zA-Z0-9_]+[ \t]*(=([^'\"|}][^|\n}]*|\"[^\"\n}]+\"|'[^'\n}]+'))?)[ \t]*";
		$rx_var = $this->_rx_var = '\\{'."$rx_var_name($rx_modifier)*\\}";
		$this->_rx_expr = $rx_expr = "(?m)\\{@([^\}]+)\\}(?-m)";
		$this->_rx_func = $rx_func = "\\{/?$rx_fn_name([^}]|\\\\)*\\}";
		$this->_rx_comment = $rx_comment = "\\{\\*([^\}])+\\*}";		
		*/


		if($this->short_tags){
			$this->_php_start = '<?';
			$this->_php_end = '?>';
			$this->_echo_start = '<?=';
			$this->_echo_end = '?>';
		}
		else{
			$this->_php_start = '<?php ';
			$this->_php_end = '?>';
			$this->_echo_start = '<?php echo ';
			$this->_echo_end = ';?>';
		}
	}

	function register_pre_step($callback, $param = null){
		$this->_pre_steps[] = array($callback, $param);
	}

	function register_post_step($callback, $param = null){
		$this->_post_steps[] = array($callback, $param);
	}
	
	function get_dependencies(){
		return array_keys($this->_dependencies);
	}
	
	function set_dependency($dep){
		$this->_dependencies[$dep] = 1;
	}
	
	function set_line_offset($offset){
		$this->_line_offset = $offset;
	}
	
	function unshift_token($tok){
		if(!empty($tok)){
			if(is_array($tok) && is_array(current($tok)))
				$this->_work_tokens = array_merge($tok, $this->_work_tokens);
			else
				array_unshift($this->_work_tokens, $tok);
		}
	}

	function _array_name($name){
		$out = '';

		if(!is_null($name)){
			foreach (explode('.', $name) as $_) {
				$out .= '["'.addslashes($_).'"]';
			}
		}

		return $out;
	}

	function _translate_var($name, $level){
		if($level !== false){
			if($this->_step == 1){
				if(!isset($this->_contexts[$level]['meta']['vars'][$name]))
					$this->_contexts[$level]['meta']['vars'][$name] = 0;
				$this->_contexts[$level]['meta']['vars'][$name]++;
			}

			$this->_last_var_path = $this->_contexts[$level]['meta']['var_path'].'/'.$name;

			if(isset($this->_contexts[$level]['map_vars'])){ // Direct mapping of variables
				if(!isset($this->_contexts[$level]['map_vars'][$name])){
					if(isset($this->_contexts[$level]['map_vars']['#']))
						return $this->_contexts[$level]['map_vars']['#'].'["'.$name.'"]';
					else
						return $this->error("Can't use var $$name in this context");
				}
				else 
					return $this->_contexts[$level]['map_vars'][$name];
			}
			elseif($name[0] != '#'){
				return $this->_contexts[$level]['vars']['value'].'["'.$name.'"]';
			}
			else{
				$name = substr($name, 1);
				$name = (string)$name; // In case substr returns false for empty string
				if(isset($this->_contexts[$level]['vars'][$name])){
					return $this->_contexts[$level]['vars'][$name];
				}
				else
					$this->error("Unknown special var $#$name");
			}
		}
		else{
			if($this->_step == 1){
				if(!isset($this->_meta['vars'][$name]))
					$this->_meta['vars'][$name] = 0;
				$this->_meta['vars'][$name]++;
			}
			$this->_last_var_path = $name;

			if($name == '#' || $name == '#value'){
				return '$this->_data';
			}
			elseif($name[0] != '#'){
				return '$'.$name;
			}
			else
				$this->error("Special vars are not supported under global context");
		}
	}

	function is_var_name($expr){
		return preg_match("~^{$this->_rx_var_name}$~", $expr);
	}
	
	function make_simple_param($param){
		if($this->is_var_name($param))
			return $this->var_name($param);
		if(is_numeric($param))
			return $param;
		else
			return '"'.addcslashes($param, '\\"').'"';
	}
	
	function var_name($name_expr){
		if(!$this->is_var_name($name_expr))
			return $this->error("Wrong variable name '".htmlentities($name_expr)."'");

		$name_expr = substr($name_expr, 1);
		$p = explode(':', $name_expr);

		$t = explode('->', $p[count($p) - 1], 2);
		$name = $t[0];
		$props = isset($t[1])?$t[1]:null;
		if(isset($props)){
			$props = "->$props";
			$p[count($p) - 1] = $name;
		}

		if(count($p) > 1){
			if($p[0]){
				$tag = $p[0];
				for($i = 1; $i < count($p) - 2; $i++)
					if($p[$i]){
						$this->error("Wrong var name {{$name_expr}}");
						return NULL;
					}

				$var = $p[count($p) - 1];
				$cnt = count($p) - 2;
				$loop = FALSE;

				for($i = count($this->_contexts) - 1; $i >= 0; $i--){
					if($this->_contexts[$i]['name'] == $tag){
						if(!$cnt){
							$loop = $i;
							break;
						}
						else
							$cnt--;
					}
					
					if(!empty($this->_contexts[$i]['no_bubble']))
						break;
				}

				$t = explode('.', $var, 2);
				$var = $t[0];
				$array = isset($t[1]) ? $t[1] : null;
				
				$array = $this->_array_name($array);

				if($loop !== FALSE){
					$res = $this->_translate_var($var, $loop).$array.$props;
				}
				else{
					$this->error("Unknown block '$tag'");
					return NULL;
				}
			}
			else{ // global variable
				//Странное условие, наверное его нужно прибить
				if(count($p) > 2){
					$this->error("Subscription in global variables is not supported, use expressions");
					return NULL;
				}

				if(is_numeric($p[1][0])){
					$this->error("Invalid variable name '\{{$p[1]}}'");
					return NULL;
				}

				// Если есть ограничивающий контекст - используем его, если нет - глобальный
				$loop = false;
				for($i = count($this->_contexts) - 1; $i >= 0; $i--){
					if(!empty($this->_contexts[$i]['no_bubble'])){
						$loop = $i;
						break;
					}						
				}
				
				
				$t = explode('.', $p[1], 2);
				$var = $t[0];
				$array = isset($t[1]) ? $t[1] : null;
				$array = $this->_array_name($array);

				$res = $this->_translate_var($var, $loop).$array.$props;
			}
		}
		else{
			$t = explode('.', $name, 2);
			$name = $t[0];
			$array = isset($t[1]) ? $t[1] : null;
			$array = $this->_array_name($array);

			if(count($this->_contexts) > 0){
				$res = $this->_translate_var($name, count($this->_contexts) - 1).$array.$props;
			}
			else{
				if(is_numeric($p[0][0])){
					$this->error("Invalid variable name '{{$p[0]}}'");
					return NULL;
				}

				$res = $this->_translate_var($name, false).$array.$props;
			}
		}

		return $res;
	}

	function rep_callback($matches){
		return $this->var_name($matches[0]);
	}

	function make_expr($expr){
	    $expr = trim($expr);

        if(strpos($expr, ';'))
            return $this->error('; are not allowed within expressions');

		if(strpos($expr, '}') or strpos($expr, '{'))
			return $this->error('Curly brackets { } are not allowed within expressions');

		$result = preg_replace_callback("~{$this->_rx_var_name}~", array($this, 'rep_callback'), $expr);


        $matches = [];

        if (preg_match_all('~(\w+)\([^\)]+\)~', $expr, $matches)) {
            foreach ($matches[1] as $func_name) {
                if (!function_exists($func_name)) {
                    return $this->error("Unknown function {$func_name}");
                }
            }
        }

		return $result;
	}

	function register_function($name, $callback, $type = DTC_FUNC_SIMPLE){
		$this->functions[$name] = array('callback' => $callback, 'type' => $type);
	}

	function register_modifier($name, $callback){
		$this->modifiers[$name] = $callback;
	}

	function error($text, $tok = null){
		$this->_compile_ok = false;
		
		if(is_null($tok))
			$tok = $this->_current_token;
			
		$file = isset($tok['file']) ? $tok['file'] : 0;
		$line = isset($tok['line']) ? $tok['line'] : 0;
			

		if ($this->_log_errors) {
            $this->_errors[] = "Line {$line}: $text";

        } elseif($this->_trigger_each_error){
            $error = "Error while compiling template '{$this->_parsed_files[$file]}' at line <b>{$line}</b>: $text<br>\n";
			trigger_error($error, E_USER_WARNING);

		} elseif(error_reporting() & E_USER_WARNING){
            $error = "Error while compiling template '{$this->_parsed_files[$file]}' at line <b>{$line}</b>: $text<br>\n";
			print $error;
//			print "Error while compiling template '{$this->_template_name}' at line <b>{$this->_line}</b>: $text<br>\n";
		}
	}

	function &enter_context($name, $vars, $is_array = true, $var_name = '', $var_path = '', $special = array()){
		$meta = &$this->_meta;
		$ok = true;

		if($this->_step == 1){
			foreach ($this->_contexts as $_) {
				for($i = count($meta['contexts']) - 1; $i >= 0; $i--){
					if($meta['contexts'][$i]['name'] == $_['name']){
						$t = &$meta['contexts'][$i];
						unset($meta);
						$meta = &$t;
						break;
					}
				}

				if($i < 0){
					$ok = false;
					break;
				}
			}
		}
		else{
			/**
			 * Короткое пояснение. У нас структура выглядит примерно так:
			 * $meta = array(
			 * 	'data',
			 * 	'contexts' => array(
			 * 		array(
			 * 			'data'
			 * 			'contexts' => array(...)
			 *
			 * При втором проходе мы используем $meta['counter'] для указания, какой
			 * контекст используется. Увеличивается этот счётчик в leave_context
			 * (через ссылку на родителя)
			 */
			foreach ($this->_contexts as $_) {
				if(!$meta['contexts'][$meta['counter']]){
					$ok = false;
					break;
				}
				$t = &$meta['contexts'][$meta['counter']];
				unset($meta);
				$meta = &$t;
			}
		}

		if(!$ok)
			$this->error("Can't find meta");

		$parent = &$meta;

		if($this->_step == 1){
			$meta['contexts'][] = array(
					'name' => $name,
					'var_name' => $var_name,
					'var_path' => $var_path,
					'vars' => array(),
					'contexts' => array(),
					'counter' => 0,
					'is_array' => $is_array
				);
			$t = &$meta['contexts'][count($meta['contexts']) - 1];
			unset($meta);
			$meta = &$t;
		}
		else{
			$t = &$meta['contexts'][$meta['counter']];
			unset($meta);
			$meta = &$t;
		}

		$this->_contexts[] = array(
				'vars' => $vars,
				'name' => $name,
				'meta' => &$meta,
				'parent' => &$parent,
			) + $special;

		return $meta;
	}

	function leave_context(){
		$meta = $this->_contexts[count($this->_contexts) - 1]['meta'];
		if($this->_step == 2){
			if($this->_contexts[count($this->_contexts) - 1]['parent'])
				++$this->_contexts[count($this->_contexts) - 1]['parent']['counter'];
		}

		array_pop($this->_contexts);
		return $meta;
	}

	function add_context_var($name, $value, $key = false, $level = 0){
		if($key !== false)
			$this->_contexts[count($this->_contexts) - 1 - $level]['meta']['data'][$name][$key] = $value;
		else
			$this->_contexts[count($this->_contexts) - 1 - $level]['meta']['data'][$name][] = $value;
	}

	function stack_push($type, $name, $block, $data = null){
		$this->stack[] = array(
				'type' => $type,
				'name' => $name,
				'block' => $block,
				'line' => $this->_current_token['line'],
				'file' => $this->_current_token['file'],
				'data' => $data,
			);

//		if($block)
//			$this->_last_block = count($this->stack)-1;

		return count($this->stack) - 1;
	}

	function stack_pop(){
//		$last = count($this->stack) - 1;

//		if($this->stack[$last]['block']){
//			for($this->_last_block = $last - 1; $this->_last_block >= 0; $this->_last_block--)
//				if($this->stack[$this->_last_block]['block'])
//					break;
//		}

		array_pop($this->stack);
	}
	
	function stack_check(){
		return $this->stack[count($this->stack) - 1];
	}
	
	function get_line(){
		return $this->_line;
	}
	
	function set_line($line){
		$this->_line = $line;
	}
	
	function get_template_name(){
		return $this->_template_name;
	}
	
	function set_template_name($template_name){
		$this->_template_name = $template_name;
	}

	function _parse_params($str){
		$params = array();

		if(preg_match_all("~(([^'\" \t][^\s=,]*|'[^']*'|\"[^\"]*\")([ \t]*=[ \t]*([^'\" \t][^ \t,]*|'[^']*'|\"[^\"]*\")|\s)?)[ \t,]*~", $str, $m)){
			foreach ($m[1] as $_) {
				$t = explode('=', $_, 2);
				$name = $t[0];
				$arg = isset($t[1]) ? $t[1] : null;
				
				if(isset($arg) && strlen($name)){
					$name = trim($name);
					$arg = trim($arg);

					if(strlen($arg) > 0){
						if($arg[0] == '"' || $arg[0] == "'")
							$arg = substr($arg, 1, -1);
					}

					if(strlen($name) > 0){
						if($name[0] == '"' || $name[0] == "'")
							$name = substr($name, 1, -1);
					}

					$params[$name] = $arg;
				}
				elseif(!isset($arg)){
					$_ = trim($_);
					if(strlen($_) > 0)
						if($_[0] == '"' || $_[0] == "'")
							$_ = substr($_, 1, -1);

					$params[] = $_;
				}
				else{
					$_ = trim($arg);
					if(strlen($_) > 0)
						if($_[0] == '"' || $_[0] == "'")
							$_ = substr($_, 1, -1);

					$params[] = $_;
				}
			}
		}
		return $params;
	}

	function _compose_params($params){
		$str = '';
		foreach ($params as $k => $v) {
			if($str !== '') $str .= ' ';
			
			if(strpos($v, '"') !== false)
				$v = "'$v'";
			else
				$v = '"'.$v.'"';
			
			if(is_int($k))
				$str .= $v;
			else
				$str .= "$k=$v";			
		}
		
		return $str;
	}

	function _clean_html($text){
		$text = str_replace("\r", "", $text);
		$text = preg_replace("~[ \t]+~", ' ', $text);
		$text = str_replace("\n ", "\n", $text);
		$text = preg_replace("~(\n\s*)+~", "\n", $text);
		
		return $text;
	}
	
	function _clean_result($text){
		$text = preg_replace("~(?<!\?)>(\n\s*)+<~", ">\n<", $text);
		$text = preg_replace("~\?>(\n\s*){2,}<~", "?>\n\n<", $text);
				
/*		$text = preg_replace_callback('~;?\?>(.*?)'.preg_quote($this->_php_start).'~s', array(&$this, '_clean_php'), $text);*/
		
		return $text;
	}
	
	function _clean_php($matches){
		if(strlen($matches[1]) < 4096)			
			return ";\necho \"".addcslashes($matches[1], "\"\n\\")."\";";
		else
			return $matches[0];
	}

	function _clean_php_tags($text){
		return preg_replace_callback("~".preg_quote($this->_php_end, '~').'(.*?)'.preg_quote($this->_php_start, '~').'~s', array($this, '_clean_php_tags_callback'), $text);
	}
	
	function _clean_php_tags_callback($m){
		if(!strlen($m[1]))
			return '';
		else
			return ";\necho '".addcslashes($m[1], "\n'\\")."';\n";
			
	}
	
	function parse_tokens($text, $base = array(), $file = null, $start_line = 1){
//		$rx = "~[\t ]*({$this->_rx_var}|{$this->_rx_expr}|{$this->_rx_func}|{$this->_rx_comment})~x";
		$rx = "~[\t ]*(
			#var
			{$this->_rx_var}
			| #expression
			{$this->_rx_expr}
			| #function
			{$this->_rx_func}
			| #comment
			{$this->_rx_comment}
		)~x";
//		$rx = "~[\t ]*($rx_var|$rx_expr|$rx_func|$rx_comment)~";

		if($file === null)
			$file = $this->get_template_name();
		
		if(!is_array($this->_parsed_files))
			$this->_parsed_files = array();
			
		$file_index = array_search($file, $this->_parsed_files);
		
		if($file_index === false){
			$this->_parsed_files[] = $file;
			$file_index = count($this->_parsed_files) - 1;
		}

		$pos = 0;
		$m = array();
		$tokens = array();
		$line = $start_line;

		while(preg_match($rx, $text, $m, PREG_OFFSET_CAPTURE, $pos)){			
			$t = $base;
			$t['type'] = TOK_TEXT;
			$t['text'] = substr($text, $pos, $m[0][1] - $pos);
			$t['line'] = $line;
			$t['file'] = $file_index;
			
			$line += substr_count($t['text'], "\n");
			
			$tokens[] = $t;
			
			$t = $base;

			$s = ltrim($m[0][0]);
			if($s[1] == '$'){
				$t['type'] = TOK_VAR;
			}
			elseif($s[1] == '@'){
				$t['type'] = TOK_EXPR;
			}
			elseif($s[1] == '*'){
				$t['type'] = TOK_COMMENT;
			}
			elseif($s[1] == '/'){
				$t['type'] = TOK_FUNC_CLOSE;
				if(preg_match('~{/([^ }]+)~', $s, $matches))
					$t['name'] = $matches[1];
			}
			else{
				$t['type'] = TOK_FUNC;
				if(preg_match('~{([^ }]+)([^}]*)}~', $s, $matches)){
					$t['name'] = $matches[1];
					$t['params'] = $matches[2];
				}
				if(substr($s, 0, 7) == '{pragma')
					$t['special'] = true;
					
			}
			
			$t['text'] = $m[0][0];
			
			$t['line'] = $line;
			$t['file'] = $file_index;
			$line += substr_count($t['text'], "\n");
			
			$tokens[] = $t;

			$pos = $m[0][1] + strlen($m[0][0]);
		}

		$t = $base;
		$t['type'] = TOK_TEXT;
		$t['text'] = substr($text, $pos);
		$tokens[] = $t;
		
		return $tokens;
	}
	
	function create_token($type, $name = null, $params = null, $text = null, $file = null, $line = null){
		$token = array(
				'file' => is_null($file) ? $this->_current_token['file'] : $file,
				'line' => is_null($line) ? $this->_current_token['line'] : $line,
				'type' => $type,
			);
		
		if($type == TOK_FUNC){
			$token['name'] = $name;
			$token['params'] = $params;
			$token['text'] = is_null($text) ? "{"."$name $params}" : $text;
		}
		elseif($type == TOK_FUNC_CLOSE){
			$token['name'] = $name;
			$token['text'] = is_null($text) ? "{/$name}" : $text;			
		}
		elseif($type == TOK_EXPR){
			$token['text'] = is_null($text) ? $this->error("Text must be specified for TOK_EXPR") : $text;
		}
		elseif($type == TOK_VAR){
			$token['text'] = is_null($text) ? $this->error("Text must be specified for TOK_EXPR") : $text;			
		}
		
		return $token;
	}

	function parse_token_block(&$tokens, $look_for = false, $shift = true){
		if($look_for === false){
			$look_for = current($tokens);
			$look_for = $look_for['name'];
			if($shift)
				array_shift($tokens);
			else
				next($tokens);
		}
		
		$a = 1;
		$txt = '';
		$tt = array();
		while(count($tokens) and current($tokens)){
			if($shift)
				$t = array_shift($tokens);
			else{
				$t = current($tokens);
				next($tokens);
			}
			
			if($t['type'] == TOK_FUNC && $t['name'] == $look_for)
				$a++;
			elseif($t['type'] == TOK_FUNC_CLOSE && $t['name'] == $look_for) 
				$a--;
			if($a == 0)
				break;
				
			$txt .= $t['text'];
			$tt[] = $t;
		}		
		
		if($a > 0){
			return false;
		}
		else{
			return array('tokens' => $tt, 'text' => $txt);
		}
	}
	
	function make_fn_rt_hash($func, $params){
		$t = 'array( ';
		foreach ($params as $k => $v) {
			$t .= $this->make_simple_param($k).' => '.$this->make_simple_param($v).', ';
		}
		$t .= ')';
		
		foreach ($func['include'] as $_) {
			$this->_initial_php["inc_$_"] = "include_once '$_';";
		}
		
		return $this->_php_start."{$func['callback']}($t);".$this->_php_end;
	}
	
	function make_fn_rt_param($func, $params){
		$t = '';
		$t = implode(', ', array_map(array(&$this, 'make_simple_param'), $params));
		
		foreach ($func['include'] as $_) {
			$this->_initial_php["inc_$_"] = "include_once '$_';";
		}
		
		return $this->_php_start."{$func['callback']}($t);".$this->_php_end;
	}
	
	function compile($text, $mtime = 0, $template_name = ''){
		$time = microtime(true);
		
		$this->_template_name = $template_name;
		$this->_compile_ok = true;

		$texts = array();
		$tokens = array();

		$rx_ident = $this->_rx_ident;
		$rx_fn_name = $this->_rx_fn_name;

		$rx_var_name = $this->_rx_var_name;
		$rx_modifier = $this->_rx_modifier;
		$rx_var = $this->_rx_var;
		$rx_expr = $this->_rx_expr;
		$rx_func = $this->_rx_func;
		$rx_comment = $this->_rx_comment;

		foreach ($this->_pre_steps as $_) {
			if(call_user_func_array($_[0], array($_[1], &$this)))
				return false;
		}

		$tokens = $this->parse_tokens($text, array(), $this->get_template_name(), 1 + $this->_line_offset);
		
		$this->_meta = array(
				'vars' => array(),
				'name' => '__tmpl_global',
				'counter' => 0,
			);

		for($this->_step = 1; $this->_step <= 2; $this->_step++){
			$this->_pragma = array(
					'clean' => (isset($this->_params['clean']) && $this->_params['clean'] === 'clean')?'on':'off',
					'parse' => 'on',
					'block_stop' => 'off',
				);

			foreach ($this->functions as $name => $_) {
				if(is_array($_['callback']) && is_object($_['callback'][0]) && method_exists($_['callback'][0], 'reset')){
					call_user_func_array(array(&$_['callback'][0], 'reset'), array($this->_step, &$this));
				}
			}

			$this->_initial_php = array();
//			reset($texts);
			$cnt = count($tokens);

			$out = '';
//			$this->_line = 1;
//			$this->_dependencies = array();
			
			$work_tokens = $tokens;
			$this->_work_tokens = &$work_tokens;

			////////
			// TOKEN LOOP
			/////////
//			for ($i = 0; $i < $cnt; $i++) {
			while($work_tokens){
//				$tok = $tokens[$i];
				$tok = array_shift($work_tokens);
				$this->_current_token = &$tok;

//				if(!$tok['no_count'])
//					$this->_line += substr_count($tok['text'], "\n");
					
				if(isset($tok['type']) && $tok['type'] == TOK_TEXT){
					if($this->_pragma['clean'] !== 'off')
						$token_text = $this->_clean_html($tok['text']);
					else
						$token_text = $tok['text'];
						
					if(substr($out, -2) == $this->_php_end && substr($token_text, 0, 1) == "\n"){
						$out .= "\n";
					}
					
					$out .= $token_text;
						
					continue;
				}

				preg_match('#^([\t ]*)(.*)$#s', $tok['text'], $m);

				if($this->_pragma['clean'] !== 'off')
					$this->_space = $m[1]?' ':'';
				else
					$this->_space = $m[1];

				$token = $m[2];
				
//				if($this->_pragma['block_stop'] == 'on' && strpos($token, '{/block') !== 0){
//					print $this->_step.' '.$token.'<br>';
//					next($texts);
//					continue;
//				}

				if($this->_pragma['parse'] !== 'off' || (strpos($token, '{pragma') === 0)){
					if($token[1] == '$'){ //var
						$var_expr = substr($token, 1, strlen($token) - 2);

						list($var_name) = explode('|', $var_expr, 2);
						$var_name = rtrim($var_name);

						$expr = $this->var_name($var_name);

						if($modifier_pos = strpos($var_expr, '|'))
							$modifier_expr = substr($var_expr, $modifier_pos).$this->_templ_end;
						else
							$modifier_expr = false;
							
							
						if($modifier_expr && preg_match_all("~{$this->_rx_modifier}~x", $modifier_expr, $m)){
							foreach ($m[1] as $_) {
								$t = explode('=', $_, 2);
								$name = $t[0];
								$args = isset($t[1]) ? $t[1] : null;
								
								$name = trim($name);
								$args = trim($args);
								if(strlen($args)){
									if($args[0] == '"' || $args[0] == "'")
										$args = substr($args, 1, -1);
								}

								if(isset($this->modifiers[$name])){
									$expr = call_user_func_array($this->modifiers[$name], array($expr, $args, &$this));
								}
								else{
									$mod_found = false;
									if($this->ext_dirs){
										foreach ($this->ext_dirs as $dir) {
											@include_once($dir."/mod_$name.php");
											if(function_exists("dt_mod_$name")){
												$mod_found = true;
												$this->modifiers[$name] = "dt_mod_$name";
												break;
											}
										}
									}

									if($mod_found){
										$expr = call_user_func_array("dt_mod_$name", array($expr, $args, &$this));
									}
									elseif(function_exists($name)){
										$args_array = array_map('trim', explode(',', $args));
										$args_string = '';
										foreach ($args_array as $_) {
											if($_ === '')
												continue;
											$args_string .= ', ' . $this->make_simple_param($_);
										}
										
										$expr = "$name($expr$args_string)";
									}
									else
										$this->error("Unknown modifier <b>$name</b>");
								}
							}
						}

						$out .= $this->_space.$this->_echo_start.$expr.$this->_echo_end;
					}
					elseif($token[1] == '@'){ //expr
						$expr = $this->make_expr(substr($token, 2, -1));
						$out .= $this->_space.$this->_echo_start.$expr.$this->_echo_end;
					}
					elseif($token[1] == '*'){
						// comment
					}
					else{ // function
						if($token[1] != '/')
							$list = explode(' ', substr($token, 1, -1), 2);
						else
							$list = explode(' ', substr($token, 2, -1), 2);
							
						$func = $list[0];
						$raw_params = isset($list[1]) ? $list[1] : null;

						$params = $this->_parse_params($raw_params);

						if(isset($this->functions[$func])){
							$func_info = $this->functions[$func];
						}
						else{
							$func_info = false;

							if($this->ext_dirs){
								foreach ($this->ext_dirs as $dir) {									
									@include_once($dir."/fn_$func.php");

									if(function_exists("dt_fninfo_$func")){
										$func_info = call_user_func("dt_fninfo_$func");
										if(empty($func_info['include']))
											$func_info['include'] = array($dir."/fn_$func.php");
											
										break;
									}
									elseif(function_exists("dt_fn_$func")) {
                                        $func_info = array(
                                                'callback' => "dt_fn_$func",
                                                'type' => DTC_FUNC_SIMPLE,
                                            );
                                        break;
									}
								}
							}

							/*
						    if (!$func_info and
                                ($func_name = substr($func, strpos($func,'('))) and
                                function_exists($func_name)
                            ) {
                                $func_info = [
                                    'type' => DTC_FUNC_TOKEN,
                                    'callback' => [[]],
                                ];
                            }
							*/

							if (!$func_info) {
                                $this->error("Unknown function {".$func."}");
                            }
								
							$this->functions[$func] = $func_info;
							
						}

						if($func_info){
							$out .= $this->_space;
							if($token[1] != '/'){
								if($func_info['type'] == DTC_FUNC_PAIRED){
									reset($work_tokens);
									
									$result = $this->parse_token_block($work_tokens, $func);
									
									if(!$result){
										$this->error("{/$func} not found");
									}
									else{
										$out .= call_user_func_array($func_info['callback'], array($params, true, $raw_params, &$this, $result['text'], $result['tokens']));
									}
								}
								elseif($func_info['type'] === DTC_FUNC_RT_HASH){
									$out .= $this->make_fn_rt_hash($func_info, $params);
								}
								elseif($func_info['type'] === DTC_FUNC_RT_PARAM){
									$out .= $this->make_fn_rt_param($func_info, $params);
								}
                                elseif($func_info['type'] === DTC_FUNC_TOKEN){
                                    $out .= $token;
                                }
								else{
									$out .= call_user_func_array($func_info['callback'], array($params, true, $raw_params, &$this));
								}
							}
							else{
								if($func_info['type'] == DTC_FUNC_PAIRED)
									$this->error("Extra termination for paired function found {{$func}}");
								elseif($func_info['type'] == DTC_FUNC_BOTH){
									$out .= call_user_func_array($func_info['callback'], array($params, false, $raw_params, &$this));
								}
								else{
									$this->error("Function {{$func}} doesn't allow closing tag");
								}
							}
						}
					}
				}
				else{
					$out .= $this->_space.$token;
				}
			}
			$out .= current($texts);

			if(($c = count($this->stack)) != 0){
				$item = $this->stack[$c - 1];
				$this->error("Unterminated {$item['type']} {$item['name']} started at line <b>{$item['line']}</b>");
			}

			foreach ($this->_post_steps as $_) {
				if(call_user_func_array($_[0], array($_[1], &$this)))
					return false;
			}

			if(!$this->_compile_ok){
			    if (!$this->_log_errors)
				    trigger_error("Errors accured during compilation of template '{$this->_template_name}'", E_USER_ERROR);

				return false;
			}
			
			if($this->_pragma['clean'] !== 'off'){
				$out = preg_replace('~^\s+~', '', $out);
			}
			
			$out = "{$this->_php_start} /* $mtime  DOK_Templates, generated at ".date('d.m.Y H:i:s')." */\n".implode("\n", $this->_initial_php)."\n?>\n".$out;

			if($this->_pragma['clean'] !== 'off'){
				$out = $this->_clean_result($out);
			}			

//			$out = $this->_clean_php_tags($out);
		}
		
		if(!empty($this->_params['debug'])) {
            $out = $this->add_debug($out);
            //print $template_name.': '.(microtime(true) - $time).'<br>';
        }


		return $out;
	}
	
	function add_debug($string){
		$string = preg_replace('~<!--template debug data-->.*?<!--end of template debug data-->~s', '', $string);
		
		global $DTC_DEBUG_CNT;
		if(!isset($DTC_DEBUG_CNT))
			$DTC_DEBUG_CNT = 0;

		$debug = <<<HTML
<div style="position: relative; top: 0px; width: 100%; height: 0; text-align: left;">
	<div style="
		position: absolute; 
		top: 0px; 
		width: 16px; 
		height: 16px; 
		background-color: #afa; 
		text-align: center; 
		font-size: 12px; 
		border: 2px solid green; 
		color: green; 
		font-family: Tahoma;
		-moz-opacity: 0.2;
		cursor: pointer" onclick="dtcShow$DTC_DEBUG_CNT()" onmouseover="this.style.MozOpacity=1.0;check_debugger_layer_overlap(this, 0)" id="debugger_link$DTC_DEBUG_CNT" class="debugger_link_layer" onmouseout="this.style.MozOpacity=0.2" title="$this->_template_name">
	&nbsp;<strong>!</strong>&nbsp;
	</div>
</div>
HTML;

        $debug = '
    <script type="text/javascript" src="/js/a/yui/yahoo/yahoo.js"></script>
    <script type="text/javascript" src="/js/a/yui/event/event.js"></script>
    <script type="text/javascript" src="/js/a/yui/dom/dom.js"></script>
    <script type="text/javascript">
    if (eval("typeof check_debugger_layer_overlap!= \'function\'")) {
    function check_debugger_layer_overlap(elem, level){
    if(level == 10) return;
    level++;
        elements = YAHOO.util.Dom.getElementsByClassName("debugger_link_layer");
        curRegion = YAHOO.util.Dom.getRegion(elem);
        for(element in elements){
            if(elements[element].id != elem.id){
                region = YAHOO.util.Dom.getRegion(elements[element]);
                if(curRegion.intersect(region)){
    //				console.log(Number(elements[element].offsetLeft));
    //				console.log(elements[element].id);
                    left = Number(elements[element].offsetLeft) + 30;
    //				console.log(left);
                    elements[element].style.left = left;
                    check_debugger_layer_overlap(elem, level);
                    setTimeout("", 50);
                }
            }
        }
    }
}
</script>
' . $debug;

		$out = <<<HTML
<html>
<head>
	<title>Debug window</title>
<style>

body {
	margin: 0;
	padding: 10px;
}

body, table {
	font-family: Tahoma, Arial, sans-serif;
	font-size: 12px;
}

.dtDebug {
	border-right: 1px solid #ddd;
	border-bottom: 1px solid #ddd;
}

.dtDebug td{
	border-top: 1px solid #ddd;
	border-left: 1px solid #ddd;
}

.dtDebug .head{
	background-color: #f0f0f0;
}

.dtDebug table.none td, .dtDebug table.none{border: 0}

</style>
</head>
<body>
HTML;
		$out .= '<table class="dtDebug" cellspacing="0" id="mainTable">'."\n";
		$out .= '<tr><td class="head"><strong>Template:</strong></td><td>'.$this->_template_name.'</td></tr>'."\n";
				
		$out .= '<tr><td colspan="2" class="head"><strong>Assigned variables:</strong></td></tr>'."\n";
		
		// Внимание!! здесь пхп прямо внутри прописан - это специфика того, что потом
		// этот код будет в пхп исполняться
		$out .= '<tr><td colspan="2"><pre>{$this->_get_debug_data()}</pre></td></tr>'."\n";

		$out .= '<tr><td colspan="2" class="head"><strong>Context information:</strong></td></tr>'."\n";
		include_once('DOK_Template_Debugger.php');
		$debugger = new DOK_Template_Debugger($this->_caller->_data, $this->_meta);
		$debug_data = $debugger->run();		
		$debug_data = str_replace("\r", "", $debug_data);
		$out .= '<tr><td colspan="2">'.$debug_data.'</td></tr>'."\n";
		
		$out .= '<tr><td colspan="2" class="head"><strong>Template params:</strong></td></tr>'."\n";
		$out .= '<tr><td colspan="2"><pre>'.nl2br(htmlspecialchars(print_r($this->_params, true))).'</pre></td></tr>'."\n";
		
		$bt = "<table cellspacing=0 cellpadding=3 bgcolor=#f7f7f7 class=\"none\" width=\"100%\"><tr bgcolor=#f0f0f0><td colspan=2><b>Call trace:</b></td></tr>\n";
	
		$a = debug_backtrace();
		$backtrace = array_slice($a, 2);
		
		for($i=1; $i<count($backtrace); $i++){
			$_ = $backtrace[$i];
			$bt .= @("<tr><td>".$_['file'].'(<b>'.$_['line'].'</b>)</td><td>'.$_['class'].$_['type'].$_['function']."</td></tr>\n");
		}
		$bt .= "</table>";
	
		$out .= '<tr><td colspan="2" style="padding: 0">'.$bt.'</td></tr>';
		$out .= "</table>";
		
		$out .= <<<HTML
	</body>
</html>
HTML;

		$out2 = 
	'<script type="text/javascript">
	var DTC_DEBUG_DATA'.$DTC_DEBUG_CNT.' = "<?=preg_replace(array("~\\r?\\n~",), array("\\\\n\\" + \\n\\t\\""), str_replace("</script>", "</scr\"+\"ipt>", addcslashes("'.addcslashes($out, "\"\\").'", "\"\\\\")))?>";
	function dtcShow'.$DTC_DEBUG_CNT.'(){
		var wnd = window.open("about:blank", "wnd'.uniqid().'","toolbar=no,status=no,directories=no,menubar=no,resizable=yes,width=600,height=600,scrollbars=yes,top=0,left=0");
		wnd.document.write(DTC_DEBUG_DATA'.$DTC_DEBUG_CNT.');
		var x = wnd.document.getElementById("mainTable").offsetWidth;
		if(x > 800)
			x = 800;
		var dx = x + 20 - wnd.document.body.offsetWidth;
  		wnd.resizeBy(dx, 0);
	}
	</script>';
		$DTC_DEBUG_CNT++;
		
		$debug .= $out2;
		$debug = "<!--template debug data-->\n$debug\n<!--end of template debug data-->";

		if(preg_match('~<body[^>]*>~', $string, $m)){
			$string = str_replace($m[0], $m[0].$debug, $string);
		}
		else{
			$string = $debug.$string;
			$string = '<!--template debug data--><div style="border: 1px dotted red;"><!--end of template debug data-->'.$string.'<!--template debug data--></div><!--end of template debug data-->';
		}
		
		return $string;
	}


	/**
	 * loop function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_loop($params, $open, $raw_params, &$self){
		if($open){
			$self->_initial_php['loop'] = "\$__tmpl_vars = array();\n\$__tmpl_keys = array();";

			$allowed_vars = array();
			if($params[0] == 'call'){
				$p = explode(':', $raw_var_name = substr($params[1], 1));
				$loop_name = $p[count($p) - 1];
				$var_name = $self->var_name($params[1]);

				$allowed_vars = array('', 'value', 'num', 'empty', 'false');
			}
			elseif($params[0] == 'fetch'){
				$p = explode(':', $raw_var_name = substr($params[1], 1));
				$loop_name = $p[count($p) - 1];
				$var_name = $self->var_name($params[1]);

				$allowed_vars = array('', 'value', 'num', 'empty', 'false');
			}
			elseif($params[0] == 'object'){
				if(version_compare(phpversion(), '5.1.0') < 0){
					return $self->error("Object loops are only supported with PHP 5.1.0 and greater.");
				}
				
				$p = explode(':', $raw_var_name = substr($params[1], 1));
				$loop_name = $p[count($p) - 1];
				$var_name = $self->var_name($params[1]);
			}
			elseif($params[0] == 'iterator'){
				if(version_compare(phpversion(), '5.1.0') < 0){
					return $self->error("Object loops are only supported with PHP 5.1.0 and greater.");
				}
				
				$p = explode(':', $raw_var_name = substr($params[1], 1));
				$loop_name = $p[count($p) - 1];
				$var_name = $self->var_name($params[1]);
			}                        
			elseif ($params[0][0]=='@') {
			    $m = [];
			    preg_match('~\@(\w+)\(([^\)]*)\)~', $raw_params, $m);
			    $func_name = $m[1];
			    $func_args = strlen($m[2])?explode(',', $m[2]):[];

			    foreach ($func_args as &$arg) {
			        if ($arg[0]=='$')
			            $arg = $this->var_name($arg);
                }

				$loop_name = $raw_params;
				$func_call = $func_name.'('.implode(',', $func_args).')';

                if (!function_exists($func_name)) {
                    $this->error("Unknown function {$func_name}");
                }

				$var_name = '$'.$func_name;
                $raw_var_name = $var_name;

				$allowed_vars = array('', 'value', 'num', 'empty', 'false', 'first', 'last');
			} else {	
				$p = explode(':', $raw_var_name = substr($params[0], 1));
				$loop_name = $p[count($p) - 1];
				$var_name = $self->var_name($params[0]);
			}

			$var_path = $self->_last_var_path;

			if(isset($params['name']))
				$loop_name = $params['name'];

			$var_name_q = addslashes($var_name);

			if(isset($params['num']))
				$num_offset = $self->make_expr($params['num']);
			else
				$num_offset = 1;

			$num = $self->stack_push('loop', $loop_name, true);

			$vars = array(
				'' => '$__tmpl_vars['.$num.']',
				'value' => '$__tmpl_vars['.$num.']',
				'key' => '$__tmpl_keys['.$num.']',
				'num' => "\$__tmpl_l_nums[$num]",
				'parity' => "(\$__tmpl_l_nums[$num]%2)",
				'first_key' => "\$__tmpl_l_first_key[$num]",
				'first' => "(\$__tmpl_l_first_key[$num] == \$__tmpl_keys[$num])",
				'last_key' => "\$__tmpl_l_last_key[$num]",
				'last' => "(\$__tmpl_l_last_key[$num] == \$__tmpl_keys[$num])",
				'empty' => 'empty($__tmpl_vars['.$num.'])',
				'false' => '($__tmpl_vars['.$num.']) === false',
				);

			if($allowed_vars){
				$t = array();
				foreach ($allowed_vars as $_) {
					$t[$_] = $vars[$_];
				}
				$vars = $t;
			}

			$meta = $self->enter_context($loop_name, $vars, true, $raw_var_name, $var_path);

			if($self->_step == 2){
                if ($params[0][0]=='@')
                    $var_name .= $num;

				if(isset($meta['data']['init']))
					$loop_init = $meta['data']['init'];
				else
					$loop_init = array();

				if(isset($meta['data']['step']))
					$loop_step = $meta['data']['step'];
				else
					$loop_step = array();


				if(isset($meta['vars']['#num']) || isset($meta['vars']['#parity'])){
					$loop_init[] = "\$__tmpl_l_nums[$num] = ($num_offset) - 1;";
					$loop_step[] = '++$__tmpl_l_nums['.$num.'];';
					$self->_initial_php['loop_num'] = '$__tmpl_l_nums = array();';
				}

				if(isset($meta['vars']['#first_key']) || isset($meta['vars']['#first'])){
					$self->_initial_php['loop_first_key'] = '$__tmpl_l_first_key = array();';
					$loop_init[] = "reset($var_name);";
					$loop_init[] = "\$__tmpl_l_first_key[$num] = key($var_name);";
				}

				if(isset($meta['vars']['#last_key']) || isset($meta['vars']['#last'])){
					$self->_initial_php['loop_last_key'] = '$__tmpl_l_last_key = array();';
					$loop_init[] = "end($var_name);";
					$loop_init[] = "\$__tmpl_l_last_key[$num] = key($var_name);";
				}

				if(isset($meta['vars']['#key']) || isset($meta['vars']['#last_key']) || isset($meta['vars']['#last']) || isset($meta['vars']['#first_key']) || isset($meta['vars']['#first'])){
					$key_str = "\$__tmpl_keys[$num] => ";
				}
				else
					$key_str = '';


				$l_init = '';
				foreach ($loop_init as $_) {
					$l_init .= $self->_space.$_."\n";
				}

				$l_step = '';
				foreach ($loop_step as $_) {
					$l_step .= $self->_space."\t$_\n";
				}


				if($params[0] == 'call') {
					if (!isset($out)) $out = '';
					$out .=
						"{$self->_space}{$self->_php_start} /* loop: $loop_name */\n" .
						"{$self->_space}if($var_name)\n" .
						"{$self->_space}if(!is_callable($var_name))\$this->_error(\"Can't call {$var_name_q} (template var: \\{$params[1]})\"); else{\n" .
						$l_init .
						"{$self->_space}while(false !== (\$__tmpl_vars[$num] = call_user_func($var_name))){{$l_step}{$self->_php_end}";
				} elseif($params[0] == 'fetch') {
					if (!isset($out)) $out = '';
					$out .=
						"{$self->_space}{$self->_php_start} /* loop: $loop_name */\n" .
						"{$self->_space}if($var_name)\n" .
						"{$self->_space}if(!method_exists({$var_name}, 'fetch'))\$this->_error(\"Can't fetch from {$var_name_q} (template var: \\{$params[1]})\"); else{\n" .
						$l_init .
						"{$self->_space}while(false !== (\$__tmpl_vars[$num] = {$var_name}->fetch())){{$l_step}{$self->_php_end}";
				} elseif($params[0] == 'object'){
					if(!isset($out)) $out = '';
					$out .=
						"{$self->_space}{$self->_php_start} /* loop: $loop_name */\n".
						"{$self->_space}if($var_name instanceof Traversable and $var_name instanceof Countable and count($var_name)){\n".
						$l_init."{$self->_space}foreach($var_name as $key_str\$__tmpl_vars[$num]){{$l_step}{$self->_php_end}";
				}
				elseif($params[0] == 'iterator'){
					if(!isset($out)) $out = '';
					$out .=
						"{$self->_space}{$self->_php_start} /* loop: $loop_name */\n".
						"{$self->_space}if($var_name instanceof Iterator and $var_name instanceof Countable){\n".
						$l_init."{$self->_space}for({$var_name}->rewind(); {$var_name}->valid(); {$var_name}->next()){".
                                                "{$l_step}$key_str\$__tmpl_vars[$num] = {$var_name}->current();{$l_step}{$self->_php_end}";
				} elseif ($params[0][0]=='@') {
					if(!isset($out)) $out = '';
					$out .=
						"{$self->_space}{$self->_php_start} /* loop: $loop_name */\n".
						"{$self->_space}{$var_name} = {$func_call};\n".
						"{$self->_space}if(is_array({$var_name}) and count({$var_name})){\n".
						$l_init."{$self->_space}foreach({$var_name} as $key_str\$__tmpl_vars[$num]){{$l_step}{$self->_php_end}";
				} else{
					if(!isset($out)) $out = '';
					$out .=
						"{$self->_space}{$self->_php_start} /* loop: $loop_name */\n".
						"{$self->_space}if(is_array($var_name) and count($var_name)){\n".
						$l_init."{$self->_space}foreach($var_name as $key_str\$__tmpl_vars[$num]){{$l_step}{$self->_php_end}";
				}
			}
			else{
				$out = '';
			}

			return $out;
		}
		else{
			$cnt = count($self->stack);
			if(!in_array($type = $self->stack[$cnt - 1]['type'], array('loop', 'elseloop')))
				return $self->error('wrong {/loop}');

			$name = $self->stack[$cnt - 1]['name'];

			$meta = $self->leave_context();

			$self->stack_pop();

			$bracket = $type == 'loop'?'}':'';
			return "{$self->_space}{$self->_php_start} $bracket} /* /loop: $name */ {$self->_php_end}";
		}
	}

	/**
	 * elseloop function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_elseloop($params, $open, $raw_params, &$self){
		$cnt = count($self->stack);
		if($self->stack[$cnt - 1]['type'] != 'loop')
			return $self->error('wrong {elseloop}');

		$name = $self->stack[$cnt - 1]['name'];
		$self->stack_pop();

		$self->stack_push('elseloop', $name, false);

		return
			"{$self->_space}{$self->_php_start} /* elseloop: $name */\n".
			"{$self->_space}}}else{{$self->_php_end}";
	}


	/**
	 * context function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_context($params, $open, $raw_params, &$self){
		if($open){
			$self->_initial_php['context'] = "\$__tmpl_contexts = array();";

			if(!isset($params['name'])){
				$p = explode(':', $raw_var_name = substr($params[0], 1));
				$context_name = $p[count($p) - 1];
			}
			else
				$raw_var_name = $context_name = $params['name'];
			$var_name = $self->var_name($params[0]);
			$var_path = $self->_last_var_path;

			$var_name_q = addslashes($var_name);

			$num = $self->stack_push('context', $context_name, true);

			$vars = array(
				'' => $var_name,
				'value' => $var_name,
//				'value' => '$__tmpl_vars['.$num.']',
				);

			$meta = $self->enter_context($context_name, $vars, false, $raw_var_name, $var_path);

			if($self->_step == 2){
				$out =
					"{$self->_space}{$self->_php_start} /* context: $context_name */\n".
					"{$self->_space}if(!is_array($var_name)){echo $var_name;}\n".
					"{$self->_space}elseif(count($var_name)){{$self->_php_end}";
			}
			else{
				$out = '';
			}

			return $out;
		}
		else{
			$cnt = count($self->stack);
			if(!in_array($type = $self->stack[$cnt - 1]['type'], array('context', 'elsecontext')))
				return $self->error('wrong {/context}');

			$name = $self->stack[$cnt - 1]['name'];

			$meta = $self->leave_context();

			$self->stack_pop();

			return "{$self->_space}{$self->_php_start} } /* /context: $name */ {$self->_php_end}";
		}
	}

	/**
	 * elsecontext function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_elsecontext($params, $open, $raw_params, &$self){
		$cnt = count($self->stack);
		if($self->stack[$cnt - 1]['type'] != 'context')
			return $self->error('wrong {elsecontext}');

		$name = $self->stack[$cnt - 1]['name'];
		$self->stack_pop();

		$self->stack_push('elsecontext', $name, false);

		return
			"{$self->_space}{$self->_php_start} /* elsecontext: $name */\n".
			"{$self->_space}}else{{$self->_php_end}";
	}

	/**
	 * tmpl function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_tmpl($params, $open, $raw_params, &$self){
		if($open){
			$self->_initial_php['loop'] = "\$__tmpl_vars = array();\n\$__tmpl_keys = array();";

			$p = explode(':', $raw_var_name = substr($raw_params, 1));
			$tmpl_name = $p[count($p) - 1];
			$var_name = $self->var_name($raw_params);
			$var_path = $self->_last_var_path;

			$num = $self->stack_push('tmpl', $tmpl_name, true);

			$meta = $self->enter_context($tmpl_name, array(
				'value' => '$__tmpl_vars['.$num.']',
				'key' => '$__tmpl_keys['.$num.']',
				), true, $raw_var_name, $var_path);

			return
				"{$self->_space}{$self->_php_start} /* tmpl: $tmpl_name */\n".
				"{$self->_space}if(is_array($var_name) and count($var_name)){\n".
				"{$self->_space}reset($var_name);\n".
				"{$self->_space}if(!is_int(key($var_name))) $var_name = array($var_name);\n".
				"{$self->_space}foreach($var_name as \$__tmpl_keys[$num] => \$__tmpl_vars[$num]){{$self->_php_end}";
		}
		else{
			$cnt = count($self->stack);
			if($self->stack[$cnt - 1]['type'] != 'tmpl')
				return $self->error('wrong {/tmpl}');

			$name = $self->stack[$cnt - 1]['name'];

			$self->stack_pop();
			$self->leave_context();
			return "{$self->_space}{$self->_php_start} }} /* /tmpl: $name */ {$self->_php_end}";
		}
	}

	/**
	 * elseif function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_if($params, $open, $raw_params, &$self){
		if($open){
			$expr = $self->make_expr($raw_params);

			$num = $self->stack_push('if', $raw_params, false);
			return "{$self->_space}{$self->_php_start}if($expr /* $raw_params */){ {$self->_php_end}";
		}
		else{
			$cnt = count($self->stack);
			if($cnt <= 0 || !in_array($self->stack[$cnt - 1]['type'], array('if', 'else', 'elseif')))
				return $self->error('wrong {/if}');

			$self->stack_pop();
			return "{$self->_space}{$self->_php_start} } {$self->_php_end}";
		}
	}

	/**
	 * else function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_else($params, $open, $raw_params, &$self){
		if(!in_array($self->stack[count($self->stack) - 1]['type'], array('if', 'elseif')))
			return $self->error('wrong {else}');

		$self->stack_pop();
		$num = $self->stack_push('else', '', false);
		return
			"{$self->_space}{$self->_php_start} /* else */\n".
			"{$self->_space}}else{ {$self->_php_end}\n";
	}

	/**
	 * elseif function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_elseif($params, $open, $raw_params, &$self){
		if(!in_array($self->stack[count($self->stack) - 1]['type'], array('if', 'elseif')))
			return $self->error('wrong {elseif}');

		$expr = $self->make_expr($raw_params);
		$self->stack_pop();
		$num = $self->stack_push('elseif', $raw_params, false);
		return
			"{$self->_space}{$self->_php_start} /* elseif: $raw_params */\n".
			"{$self->_space}}elseif($expr){ {$self->_php_end}\n";
	}

	/**
	 * cycle function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_cycle($params, $open, $raw_params, &$self){
		if(!isset($self->_compile_context['cycle_i']))
			$self->_compile_context['cycle_i'] = 0;
		$i = &$self->_compile_context['cycle_i'];

		foreach ($params as $k => $v) {
			if($v[0] != '$')
				$params[$k] = "'".str_replace("'", "\'", trim($v, "\t \n\r\"'"))."'";
		}

		$val_cnt = count($params);
		$values = implode(', ', $params);

		$self->_initial_php['cycle'] = "\$__tmpl_cycle_values = array();\n\$__tmpl_cycle_pos = array();";
		$self->_initial_php[] = "\$__tmpl_cycle_values[$i] = array($values);";
		$self->add_context_var('init', "\$__tmpl_cycle_pos[$i] = 0;");

		$out = "{$self->_space}{$self->_echo_start}\$__tmpl_cycle_values[$i][\$__tmpl_cycle_pos[$i]++ % $val_cnt]{$self->_echo_end}";
		$i++;

		return $out;
	}

	/**
	 * base_url function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_base_url($params, $open, $raw_params, &$self){
		$level = (int)$raw_params;

		$self->_initial_php['base'.$level] = "\$__tmpl_base_$level = get_base($level);";

		$out = "{$self->_space}{$self->_echo_start}\$__tmpl_base_$level{$self->_echo_end}";

		return $out;
	}

	/**
	 * qs function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_qs($params, $open, $raw_params, &$self){
		$remove = array();

		$add = '';
		if(!empty($params['add'])){
			$a = explode('&', $params['add']);
			foreach ($a as $_) {
				$t = explode('=', $_, 2);
				$key = $t[0];
				$val = isset($t[1]) ? $t[1] : null;
				$key = trim($key);

				if($add !== '')
					$add .= '&';

				if(preg_match("~{$self->_rx_var_name}~", $val))
					$add .= "$key=".$self->_echo_start.$self->make_expr($val).$self->_echo_end;
				else
					$add .= "$key=".urlencode($val);

				$remove[] = $key;
			}
		}

		if(!empty($params['encode'])){
			$a = explode('&', $params['encode']);
			foreach ($a as $_) {
				$t = explode('=', $_, 2);
				$key = $t[0];
				$val = isset($t[1]) ? $t[1] : null;
				
				$key = trim($key);

				if($add !== '')
					$add .= '&';

				if(preg_match("~{$self->_rx_var_name}~", $val))
					$add .= "$key=".$self->_echo_start.'urlencode('.$self->make_expr($val).')'.$self->_echo_end;
				else
					$add .= "$key=".urlencode($val);

				$remove[] = $key;
			}
		}

		$remove_str = 'array(';
		if(!empty($params['remove']) || $remove){
			if(!empty($params['remove'])){
				$a = explode(',', $params['remove']);
				array_walk($a, 'trim');
			}
			else
				$a = array();
			$a = array_unique(array_merge($a, $remove));
			sort($a);

			$remove_str .= "'".implode("', '", $a)."'";
		}
		$remove_str .= ')';

		if($self->_step != 2)
			return '';

		if(!isset($self->_compile_context['qs_map']))
			$self->_compile_context['qs_map'] = array();
		$qs_map = &$self->_compile_context['qs_map'];
		$num = isset($qs_map[$remove_str]) ? $qs_map[$remove_str] : null;
		if(!$num){
			$num = $qs_map[$remove_str] = count($qs_map)+1;
			$self->_initial_php[] = "\$__tmpl_qs[$num] = get_qs_prefix(array(), $remove_str);";
		}

		$out = "{$self->_space}{$self->_echo_start}\$__tmpl_qs[$num]{$self->_echo_end}$add";

		return $out;
	}

	/**
	 * qs function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_qs2($params, $open, $raw_params, &$self){
		$remove = array();

		if(isset($params['_remove'])){
			$remove = array_map('trim', explode(',', $params['_remove']));
			unset($params['_remove']);
		}

        $sep = '&';
        if (isset($params['_sep'])) {
            $sep = $params['_sep'];
            unset($params['_sep']);
        }

		$add = '';
		$enc_flag = false;
		foreach ($params as $k => $v) {
			if(is_int($k)){
				if($v == 'enc')
					$enc_flag = true;
				continue;
			}
			else{
				if($add !== '')
					$add .= $sep;

				if(preg_match("~{$self->_rx_var_name}~", $v)){
					if($enc_flag)
						$add .= "$k=".$self->_echo_start.'urlencode('.$self->make_expr($v).')'.$self->_echo_end;
					else
						$add .= "$k=".$self->_echo_start.$self->make_expr($v).$self->_echo_end;
				}
				else
					$add .= "$k=".urlencode($v);

				$remove[] = $k;
			}
			$enc_flag = false;
		}

		$remove_str = 'array(';
		if($remove){
			sort($remove);
			$remove_str .= "'".implode("', '", $remove)."'";
		}
		$remove_str .= ')';

		if($self->_step != 2)
			return '';

		if(!isset($self->_compile_context['qs2_map']))
			$self->_compile_context['qs2_map'] = array();
		$qs_map = &$self->_compile_context['qs2_map'];		
		if(isset($qs_map[$remove_str . $sep]))
			$num = $qs_map[$remove_str . $sep];
		else{
			$num = count($qs_map);
			$qs_map[$remove_str . $sep] = $num;
			$self->_initial_php[] = "\$__tmpl_qs2[$num] = get_qs_prefix(array(), $remove_str, null, '$sep');";
		}

		$out = "{$self->_space}{$self->_echo_start}\$__tmpl_qs2[$num]{$self->_echo_end}$add";

		return $out;
	}

	/**
	 * qs_old function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_qs_old($params, $open, $raw_params, &$self){
		$add = 'array(';
		if(isset($params['add'])){
			$a = explode('&', $params['add']);
			foreach ($a as $_) {
				$t = explode('=', $_, 2);
				$key = $t[0];
				$val = isset($t[1]) ? $t[1] : null;
				
				$key = trim($key);
				$add .= "'$key' => ".$self->make_expr($val).",";
			}
		}
		$add .= ')';

		$remove = 'array(';
		if(isset($params['remove'])){
			$a = explode(',', $params['remove']);
			array_walk($a, 'trim');
			$remove .= "'".implode("', '", $a)."'";
		}
		$remove .= ')';

		$out = "{$self->_space}{$self->_echo_start}get_qs($add, $remove){$self->_echo_end}";

		return $out;
	}

	/**
	 * qs_prefix function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_qs_prefix($params, $open, $raw_params, &$self){
		$add = 'array(';
		if(isset($params['add'])){
			$a = explode('&', $params['add']);
			foreach ($a as $_) {
				$t = explode('=', $_, 2);
				$key = $t[0];
				$val = isset($t[1]) ? $t[1] : null;
				
				$key = trim($key);
				$add .= "'$key' => ".$self->make_expr($val).",";
			}
		}
		$add .= ')';

		$remove = 'array(';
		if(isset($params['remove'])){
			$a = explode(',', $params['remove']);
			array_walk($a, 'trim');
			$remove .= "'".implode("', '", $a)."'";
		}
		$remove .= ')';
		// It's placed here to allow var_name() (make_expr()) to be executed at step 1
		if($self->_step == 1) return;

		if(!isset($self->_compile_context['qs_prefix_num']))
			$self->_compile_context['qs_prefix_num'] = 0;
		$qs_prefix_num = &$self->_compile_context['qs_prefix_num'];
		
		if(!isset($self->_compile_context['qs_prefix_map']))
			$self->_compile_context['qs_prefix_map'] = array();
		$qs_prefix_map = &$self->_compile_context['qs_prefix_map'];
		
		if(!isset($qs_prefix_map[$raw_params])){
			$self->_initial_php[] = "\$__tmpl_qs_prefix[$qs_prefix_num] = get_qs_prefix($add, $remove);";
			$qs_prefix_map[$raw_params] = $num = $qs_prefix_num++;
		}
		else
			$num = $qs_prefix_map[$raw_params];		

		$out = "{$self->_space}{$self->_echo_start}\$__tmpl_qs_prefix[$num]{$self->_echo_end}";

		return $out;
	}

	/**
	 * form_qs function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_form_qs($params, $open, $raw_params, &$self){
		$add = 'array(';
		if(isset($params['add'])){
			$a = explode('&', $params['add']);
			foreach ($a as $_) {
				$t = explode('=', $_, 2);
				$key = $t[0];
				$val = isset($t[1]) ? $t[1] : null;
				
				$key = trim($key);
				$add .= "'$key' => ".$self->make_expr($val).",";
			}
		}
		$add .= ')';

		$remove = 'array(';
		if(isset($params['remove'])){
			$a = explode(',', $params['remove']);
			$a = array_map('trim', $a);
			$remove .= "'".implode("', '", $a)."'";
		}
		$remove .= ')';

		$out = "{$self->_space}{$self->_echo_start}get_form_qs($add, $remove){$self->_echo_end}";

		return $out;
	}

	/**
	 * selected function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_selected($params, $open, $raw_params, &$self){
		$expr = $self->make_expr($raw_params);
		return "{$self->_space}{$self->_php_start}if($expr) echo 'selected=\"selected\"';{$self->_php_end}";
	}

	/**
	 * checked function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_checked($params, $open, $raw_params, &$self){
		$expr = '';
		if(count($params) == 3){
			if($params[1] == 'key_of' || $params[1] == 'in'){
				if(preg_match("~{$self->_rx_var_name}~", $params[0])){
					$first = $self->var_name($params[0]);
				}
				else{
					$first = "'".$params[0]."'";
				}
			}
			
			if($params[1] == 'key_of')
				$expr = 'array_key_exists('.$first.', '.$self->var_name($params[2]).')';
			elseif($params[1] == 'in')
				$expr = 'in_array('.$first.', '.$self->var_name($params[2]).')';
		}

		if($expr == '')
			$expr = $self->make_expr($raw_params);

		return "{$self->_space}{$self->_php_start}if($expr) echo 'checked=\"checked\"';{$self->_php_end}";
	}

	/**
	 * include function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_include($params, $open, $raw_params, &$self){
		$file_name = array_shift($params);
		$file_name = trim($file_name, '"\'');
		if(!$file_name){
			return $self->error('{include} - no include file given');
		}

		$data = 'array(';
		foreach ($params as $name => $val) {
			if(is_int($name)){
				if($val[0] == '$')
					$name = substr($val, 1);
				else
					return $self->error("Wrong include param: $val");
			}

			$data.= "'$name'=>".$self->make_expr($val).',';
		}
		$data .= ')';

		return "{$self->_space}{$self->_echo_start}\$this->parse_file('$file_name', $data){$self->_echo_end}";
	}

	/**
	 * file function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_file($params, $open, $raw_params, &$self){
		$paths = array(
				'templ' => 'TMPL_ROOT',
				'doc' => 'DOCUMENT_ROOT',
				'data' => 'DATA_ROOT',
			);

		if(count($params) < 1)
			return $this->error("Not enough params for {file} function");

		if(isset($paths[$params[0]])){
			if(count($params) < 2)
				return $this->error("Not enough params for {file} function");

			$path = $paths[$params[0]].'/'.$params[1];
		}
		else
			$path = $params[0];

		return "{$self->_space}{$self->_echo_start}file_get_contents($path){$self->_echo_end}";
	}

	/**
	 * implode function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_implode($params, $open, $raw_params, &$self){
		$glue = addslashes($params[0]);
		$var = $self->var_name($params[1]);
		return "{$self->_space}{$self->_echo_start}implode('$glue', $var){$self->_echo_end}";
	}

	/**
	 * callback function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_callback($params, $open, $raw_params, &$self){
		if(isset($params['name'])){
			$name = $params['name'];
			unset($params['name']);
		}
		else{
			$name = $params[0];
			unset($params[0]);
		}

		if($params['result']){
			$result = $self->var_name($params['result']).' =';
			unset($params['result']);
		}
		else
			$result = '';

		$call = '';
		foreach ($params as $_) {
			if($call != '') $call .= ', ';
			if($_[0] == '$'){
				$call .= $self->var_name($_);
			}
			else{
				$call .= '"'.addslashes($_).'"';
			}
		}

		return "{$self->_space}{$self->_php_start}$result \$this->_callback($name, $call);{$self->_php_end}";
	}

	/**
	 * hidden function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_hidden($params, $open, $raw_params, &$self){
		if(!$params)
			return $self->error("Not enough params for hidden function");

		$out = '';
		foreach ($params as $name => $var) {
			if(is_int($name)){
				if($var[0] != '$'){
					$name = $var;
					$var = '$'.$var;
				}
				else{
					$name = substr($var, 1);
				}
			}
			if($var[0] == '$')
				$var = $self->var_name($var);
			$out .= "<input type=\"hidden\" name=\"$name\" value=\"{$self->_echo_start}$var{$self->_echo_end}\">";
		}

		return "{$self->_space}$out";
	}

	/**
	 * sort function
	 * usage: {sort name}, {sort name Название}, {sort name "<b>Название</b>" link_}
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_sort($params, $open, $raw_params, &$self){
		if(!isset($params[0]))
			return $self->error("Not enough params for sort function");

		$var_name = $params[0];
		$var_name = str_replace(array('@', '.', '#'), '_', $var_name);
		if(isset($params[1]))
			$name = $params[1];
		else
			$name = $var_name;
			
		$name = $this->make_simple_param($name);

		$sort = '$sort';
		if(isset($params[3]))
			$sort = $params[3];
		elseif(isset($params['sort']))
			$sort = $params['sort'];

		if($self->is_var_name($var_name)){			
			$sort_href = $self->_echo_start.$self->make_expr($sort.'['.$var_name.'."_href"]').$self->_echo_end;
			$sort_img = "{$self->_echo_start}".$self->make_expr($sort.'['.$var_name.'."_img"]').$self->_echo_end;
			if(isset($params[2])){
				$sort_class = ' class="'.$params[2].$self->_echo_start.$self->make_expr($sort.'['.$var_name.'."_status"]').$self->_echo_end.'"';
			}
			else
				$sort_class = '';
		}
		else{
			$sort_href = $self->_echo_start.$self->var_name($sort.'.'.$var_name.'_href').$self->_echo_end;
			$sort_img = "{$self->_echo_start}".$self->var_name($sort.'.'.$var_name.'_img').$self->_echo_end;
			if(isset($params[2])){
				$sort_class = ' class="'.$params[2].$self->_echo_start.$self->var_name($sort.'.'.$var_name.'_status').$self->_echo_end.'"';
			}
			else
				$sort_class = '';
		}


		return $self->_space.'<a href="'.$sort_href.'"'.$sort_class.'>'.$self->_echo_start.$name.$self->_echo_end.'</a> '.'<a href="'.$sort_href.'"'.$sort_class.'>'.$sort_img.'</a>';
	}

	/**
	 * sort_old function
	 * usage: {sort name}, {sort name Название}, {sort name "<b>Название</b>" link_}
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_sort_old($params, $open, $raw_params, &$self){
		if(!isset($params[0]))
			return $self->error("Not enough params for sort function");

		$var_name = $params[0];
		if(isset($params[1]))
			$name = $params[1];
		else
			$name = $var_name;

		$sort_href = $self->_echo_start.$self->var_name('$'.$var_name.'_href').$self->_echo_end;
		$sort_img = "{$self->_php_start}if(".$self->var_name('$'.$var_name.'_img')."){{$self->_php_end}<img src=\"".$self->_echo_start.$self->var_name('$'.$var_name.'_img.href').$self->_echo_end.'" align="absmiddle" alt="" />'.$self->_php_start.'}'.$self->_php_end;
		if($params[2]){
			$sort_class = ' class="'.$params[2].$self->_echo_start.$self->var_name('$'.$var_name.'_status').$self->_echo_end.'"';
		}
		else
			$sort_class = '';


		return $self->_space.'<a href="'.$sort_href.'"'.$sort_class.'>'.$name.' '.$sort_img.'</a>';
	}

	/**
	 * session function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_session($params, $open, $raw_params, &$self){
		return $self->_echo_start.'session_name()."=".session_id()'.$self->_echo_end;
	}

	/**
	 * set function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_set($params, $open, $raw_params, &$self){
		$out = '';
		foreach ($params as $k => $v) {
			if(preg_match("~{$self->_rx_var_name}~", $k)){
				$out .= $self->_space.$self->var_name($k).' = '.$self->make_expr($v)."\n";
			}
			else
				$self->error("$k is not a variable");
		}

		if($out != '')
			return $self->_php_start."\n$out".$self->_php_end;
		else
			return '';
	}

	/**
	 * filter function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_filter($params, $open, $raw_params, &$self){
		if(count($params) == 0 || !$params[0])
			return $this->error("Not enough parameters for <i>filter</i> function");

		$filter_name = $params[0];
		unset($params[0]);

		if(!empty($params['result'])){
			$result = $self->var_name($params['result']).' = ';
			$has_result = 'true';
			unset($params['result']);
		}
		else{
			$has_result = 'false';
			$result = '';
		}

		$vars = 'array(';

		$i = 0;
		foreach ($params as $name => $var) {
			if(is_int($name))
				$name = $i++;
			if(preg_match('~'.$self->_rx_var_name.'~', $var))
				$vars .= $self->_space."\t'$name' => &".$self->var_name($var).",\n";
			else
				$vars .= $self->_space."\t'$name' => \"".addslashes($self->make_expr($var))."\",\n";
		}
		$vars .= ")";

		return $self->_space.$self->_php_start."$result\$this->_call_filter('$filter_name', $vars, $has_result);".$self->_php_end;
	}

	/**
	 * capture function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_capture($params, $open, $raw_params, &$self){
		if($open){
			if(empty($params[0]))
				return $self->error("Not enough params for <i>capture</i> function");
				
			if($params[0] == 'out'){
				$out = true;
				
				if(empty($params[1]))
					return $self->error("Not enough params for <i>capture</i> function");
					
				$var = $self->make_simple_param($params[1]);
			}
			else{
				$var = $self->var_name($params[0]);
				$out = false;
			}
				
			$self->stack_push('capture', $var, false, array('out' => $out, 'var' => $var));
			return $self->_space.$self->_php_start."ob_start();".$self->_php_end;
		}
		else{
			if($self->stack[count($self->stack) - 1]['type'] != 'capture')
				return $self->error("Wrong {/capture}");
			$var = $self->stack[count($self->stack) - 1]['name'];
			$out = $self->stack[count($self->stack) - 1]['data']['out'];
			
			$self->stack_pop();
			
			if(!$out)
				return $self->_space.$self->_php_start."$var = ob_get_clean();".$self->_php_end;
			else
				return $self->_space.$self->_php_start."\$this->_captured[$var] = ob_get_clean();".$self->_php_end;
		}
	}

	/**
	 * pragma function
	 * @author DoK
	 * @return string
	 * @param $params array
	 * @param $open bool
	 * @param $raw_params string
	 * @param $self DOK_Template_Compiler
	 **/
	function fn_pragma($params, $open, $raw_params, &$self){
		switch($params[0]){
			case 'parse':
			case 'clean':
				$self->_pragma[$params[0]] = strtolower($params[1])=='on'?'on':'off';
				return '';
			default:
				return $self->error("Unknown pragma [{$params[0]}]");
		}
	}

//	/**
//	 * block function
//	 * @author DoK
//	 * @return string
//	 * @param $params array
//	 * @param $open bool
//	 * @param $raw_params string
//	 * @param $self DOK_Template_Compiler
//	 **/
//	function fn_block($params, $open, $raw_params, &$self){
//		if($open){
//			for($i = count($self->stack) - 1; $i >= 0; $i--){
//				if($self->stack[$i]['type'] == 'block'){
//					$self->error("Can't make block inside block");
//					return false;
//				}
//			}
//			
//			$self->stack_push('block', current($params), false);
//			if($self->_params['block'] != current($params)){
//				$self->_pragma['block_stop'] = 'on';
//			}
//		}
//		else{
//			if($self->stack[count($self->stack) - 1]['type'] != 'block'){
//				$self->error("Incorrect {block}");
//				return false;
//			}
//			
//			$self->stack_pop();
//			
//			$self->_pragma['block_stop'] = 'off';
//		}
//	}


	/**
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_truncate($expr, $params, &$self){
		$params = explode(',', $params, 2);
		$delimiter = str_replace(array('"', '\\'), array('\\"', '\\\\'), isset($params[1])?$params[1]:'...');
		$length = (int)$params[0];

		return "truncate($expr, $length, \"$delimiter\")";
	}

	/**
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_nbsp($expr, $params, &$self){
		return "((strlen(\$__tmpl_mod_nbsp = $expr)!=0)?(\$__tmpl_mod_nbsp):'&nbsp')";
	}

	/**
	 * include function
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_default($expr, $params, &$self){
		$default = str_replace(array('"', '\\'), array('\\"', '\\\\'), $params);
		return "((strlen(\$__tmpl_mod_default = $expr)!=0)?(\$__tmpl_mod_default):\"$default\")";
	}

	/**
	 * include function
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_switch($expr, $params, &$self){
		$t = explode(':', $params, 2);
		$on = $t[0];
		$off = isset($t[1]) ? $t[1] : null;

		$on = addslashes($on);
		$off = addslashes($off);

		return "($expr?\"$on\":\"$off\")";
	}

	/**
	 * mod_image_width function
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_image_width($expr, $params, &$self){
		$self->_initial_php['mod_image'] = '$__tmpl_image_data = array();';
		return "(\$__tmpl_image_data[\$this->__mod_temp = $expr]?\$__tmpl_image_data[\$this->__mod_temp][0]:((\$__tmpl_image_data[\$this->__mod_temp] = getimagesize(\$this->__mod_temp))?\$__tmpl_image_data[\$this->__mod_temp][0]:0))";
	}

	/**
	 * mod_image_height
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_image_height($expr, $params, &$self){
		$self->_initial_php['mod_image'] = '$__tmpl_image_data = array();';
		return "(\$__tmpl_image_data[\$this->__mod_temp = $expr]?\$__tmpl_image_data[\$this->__mod_temp][1]:((\$__tmpl_image_data[\$this->__mod_temp] = getimagesize(\$this->__mod_temp))?\$__tmpl_image_data[\$this->__mod_temp][1]:0))";
	}

	/**
	 * mod_null
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_null($expr, $params, &$self){
		return '';
	}

	/**
	 * mod_rtrim
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_rtrim($expr, $params, &$self){
		return $params?"rtrim($expr, $params)":"rtrim($expr)";
	}

	/**
	 * mod_nl2br
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_nl2br($expr, $params, &$self){
		return "nl2br($expr)";
	}

	/**
	 * mod_nl2br
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_format_par($expr, $params, &$self){
		return "'<p>'.preg_replace(\"~(\\r?\\n)+~\", \"</p>\\n<p>\", $expr).'</p>'";
	}

	/**
	 * mod_urlencode
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_urlencode($expr, $params, &$self){
		return "urlencode($expr)";
	}

	/**
	 * mod_addslashes
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_addslashes($expr, $params, &$self){
		return "addslashes($expr)";
	}

	/**
	 * mod_print_r
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_print_r($expr, $params, &$self){
		return "print_r($expr, true)";
	}

	/**
	 * mod_pre
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_pre($expr, $params, &$self){
		return "'<pre>'.($expr).'</pre>'";
	}

	/**
	 * mod_round
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_round($expr, $params, &$self){
		return strlen($params)?"round($expr, $params)":"round($expr)";
	}

	/**
	 * mod_htmlspecialchars
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_htmlspecialchars($expr, $params, &$self){
		return "htmlspecialchars($expr)";
	}

	/**
	 * mod_subs
	 * @author DoK
	 * @return string
	 * @param $params string
	 * @param $expr string
	 * @param $self DOK_Template_Compiler
	 **/
	function mod_subs($expr, $params, &$self){
		return "preg_replace('~\\{\\$([a-zA-Z_][a-zA-Z0-9_]*)}~e', '$\\1', $expr)";
	}
}
