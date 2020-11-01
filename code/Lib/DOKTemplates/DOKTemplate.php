<?php

namespace Lib\DOKTemplates;


class DOKTemplate {

    public $_validation = false;

	public $short_tags = true;
	public $recompile = false;
	public $use_simple_mtime = true;
	public $_debug = false;
	public $skip_dependencies = false;
	
	public $_data = array();
	public $_common_data = array();
	
//	public $_current;
	public $_template;
	
	public $_compile_dir;
	public $_template_dir;
	public $_filters_dir = false;
	public $_params = array();
	public $_ext_params;
		
//	public $_mtime;
	
	public $_compiler;
	
	public $_pre_filters = array();
	
	public $_t_data;
	public $_file;

	public $_mod_temp;
	
	public $_errors;
	public $_ext_dirs;
	public $config_path;
	
	public $_callbacks = array();
	
	public $_captured;
	
	function __construct($template = NULL){
		$this->_template = $template;
//		$this->_current = &$this->data;
        $this->_ext_dirs = [__DIR__.'/ext'];
		$this->_compiler = array('include' => 'DOKTemplateCompiler.php', 'class' => '\Lib\DOKTemplates\DOKTemplateCompiler');
		$this->config_path = (defined('CODE_ROOT')?CODE_ROOT:'').'/template.conf';
		if($this->_filters_dir === false){
			static $dirname = false;			
			if($dirname === false) $dirname = dirname(__FILE__);
			
			$this->_filters_dir = $dirname.'/filters/';
		}
	}
	
	function assign($var, $value = null){
		if(is_array($var)){
			foreach ($var as $k => $v) {
				$this->_data[$k] = $v;
			}
		} 
        else{
			$this->_data[$var] = $value;
		}
	}	
	
	function assign_ref($var, &$value){
		$this->_data[$var] = &$value;
	}

	function append($var, $value = null, $merge = false){
		if (is_array($var)) {
			// $var is an array, ignore $value
			foreach ($var as $k => $v){
				if(!@is_array($this->_data[$k]))
					settype($this->_data[$k],'array');

				if($merge && is_array($v)) {
					foreach($v as $key => $_) {
						$this->_data[$k][$key] = $_;
					}
				} else {
					$this->_data[$k][] = $v;
				}
			}
		}
		else {
			if($var != '' && isset($value)) {
				if(!@is_array($this->_data[$var])) {
					settype($this->_data[$var],'array');
				}
				if($merge && is_array($value)) {
					foreach($value as $key => $_) {
						$this->_data[$var][$key] = $_;
					}
				} else {
					$this->_data[$var][] = $value;
				}
			}
		}
	}
	
	function clean(){
		$this->_data = array();
	}
	
	function display_template($data){
		$this->_captured = array();
		
		$this->_t_data = $data;
		$this->_errors = '';
		unset($data);		
		extract($this->_t_data, EXTR_SKIP);
		extract($this->_common_data, EXTR_SKIP);
		unset($this->_t_data);
		
		$this->__temp_error_level = error_reporting(E_ALL & ~E_NOTICE);
		
		ob_start();
		include($this->_file);
		$content = ob_get_contents();
		ob_end_clean();
		
		error_reporting($this->__temp_error_level);
		
		if($this->_errors)
			print $this->_errors;
		
		return $content;
	}
	
	function _get_compiled_path($template, $debug){
		// Cherck whether we have compiled template
		if(!$template){
			trigger_error('No template', E_USER_ERROR);
			return array(false, false);
		}
		
		if($template[0] != '/' && $template[1] != ':')
			$template = $this->_template_dir.'/'.$template;
		$template = str_replace('//', '/', $template);
		
		if(!file_exists($template)){
			trigger_error("Template {$template} not found", E_USER_ERROR);
			return array(false, false);
		}
		
		$file = basename($template);
		
		$params = $this->_params;
		if($debug)
			$params['debug'] = 'debug';
			
		$c_file = '';
		ksort($params);
		if($params)
			$c_file .= join('_', $params).'_';
			
		$c_file .= $file;
		
		if(!empty($this->_ext_params)){
			$c_file .= '_'.md5($template.serialize($this->_ext_params));
		}
		else
			$c_file .= '_'.crc32($template);

//		Could be used on UNIX systems and it is more reliable 
//		BUT it doesn't work on Windows :(
//		$c_file .= fileinode($template);
		$c_path = $this->_compile_dir.'/'.$c_file.'.php';

		return array($c_path, $template);
	}

	function clean_file($template, $debug = false){
		list($c_path, $template) = $this->_get_compiled_path($template, $debug);
		if(file_exists($c_path)){
			unlink($c_path);
		}
	}
	
	function parse_file($template, $data = array()){
		$debug = $this->_debug || $this->_is_global_debug();
		$recompile = $this->recompile || $debug;

		list($c_path, $template) = $this->_get_compiled_path($template, $debug);
		if($c_path === false){
			return null;
		}
		
		if(!$this->skip_dependencies)
			$mtime = filemtime($template);
		else
			$mtime = 0;
		
		$debug_data = '';
		$compile = false;
		if(!$recompile && file_exists($c_path)){
			if(!$this->skip_dependencies){
				if($this->use_simple_mtime)
					$mt = filemtime($c_path);
				else{
					$f = fopen($c_path, 'r');
					// <?php /* \d */
					$mt = (int)substr(fgets($f), 9);
					fclose($f);
				}
				
				if($mt < $mtime)
					$compile = true;
			}
		}
		else
			$compile = true;
			
		if(!$compile && file_exists($c_path.'.dep') && !$this->skip_dependencies){
			foreach (file($c_path.'.dep') as $_) {				
				$_ = rtrim($_);
				if(!file_exists($_) || filemtime($_) > $mt){
					$compile = true;
					break;
				}
			}
		}

		// Здесь именно $this->recompile - в режиме отладки ругаться не надо
		if($this->recompile){
			if(defined('DEBUG') && !DEBUG)
				trigger_error("Recompile enabled for file [$template]", E_USER_WARNING);
				
			$compile = true;
		}
			
		if($compile){
			$compiler = &$this->create_compiler();
			/* @var $compiler DOK_Template_Compiler */
			if($debug)
				$compiler->_params['debug'] = 'debug';
			
			$content = file_get_contents($template);
//			if($debug){
//				$content = '{debug}'.$content;
//			}
			
			foreach($this->_pre_filters as $_){
				$content = call_user_func_array($_, array($content, &$this));
			}
			$r = $compiler->compile($content, $mtime, $template);
			if($r === false){
			    if (!$this->_validation)
				    trigger_error("Template compilation failed", E_USER_ERROR);
				return false;
			}
			
			$this->_save_compiled_file($c_path, $r, $compiler->get_dependencies());
			
			if($debug){
				// Это мы отложим до светлого будушего
//				include_once('DOK_Template_Debugger.php');
//				$debugger = new DOK_Template_Debugger($this->_data, $compiler->_meta);
//				$debug_data = $debugger->run();
			}
			if ($this->_validation)
			    return true;
		}
		
		$this->_file = $c_path;
		return $this->display_template($data).$debug_data;
	}
	
	function _save_compiled_file($compiled_name, $content, $dependencies){
	    if ($this->_validation)
	        return;

		// Это чтобы запретить одновременную запись
		$lock = fopen($this->_compile_dir.'/.lock', 'w');
		if(!$lock){
			trigger_error("Can't open lock file, '{$this->_compile_dir}/.lock' template compilation failed", E_USER_ERROR);
			return false;
		}
		flock($lock, LOCK_EX);
		
		$f = fopen($compiled_name, 'w');
		fwrite($f, $content);
		fclose($f);
		
		if($dependencies){
			$f = fopen($compiled_name.'.dep', 'w');
			foreach ($dependencies as $_) {
				fwrite($f, "$_\n");
			}
			fclose($f);
		}
		else{
			if(file_exists($compiled_name.'.dep'))
				unlink($compiled_name.'.dep');
		}
		
		flock($lock, LOCK_UN);
		fclose($lock);
	}
	
	function &create_compiler(){
		if($this->_compiler['include'])
			include_once $this->_compiler['include'];
			
		$compiler = new $this->_compiler['class']($this->_params, $this);			
		$compiler->short_tags = $this->short_tags;
		$compiler->ext_dirs = $this->_ext_dirs;
		
		$this->setup_compiler($compiler);
		
		return $compiler;
	}
	
	/**
	 * Этот метод вызывается для настройки компилятора
	 *
	 * @param DOK_Template_Compiler $compiler
	 */
	function setup_compiler(&$compiler){
		
	}
	
	function parse_string($string){
		$md5 = md5($string);
		$file = $this->_compile_dir."/$md5.string";
		if(!file_exists($file)){
			$f = fopen($file, 'wb');
			fwrite($f, $string);
			fclose($f);
		}
		
		return $this->parse_file($file, $this->_data);
	}
	
	function parse(){
		return $this->parse_file($this->_template, $this->_data);
	}
	
	function _error($str){
		$this->_errors .= $str."\n";
	}
	
	function register_pre_filter($filter){
		$this->_pre_filters[] = $filter;
	}
	
	function register_callback($name, $callback){
		if(!is_callable($callback))
			trigger_error("Wrong callback $callback", E_USER_ERROR);
		else
			return $this->_callbacks[$name] = $callback;
	}
	
	function set_param($param, $value){
		$this->_params[$param] = $value;
	}

	function set_ext_param($name, $value = null){
		if(!isset($this->_ext_params))
			$this->_ext_params = array();
			
		if(is_array($name)){
			foreach ($name as $k => $value) {
				$this->_ext_params[$k] = $value;
			}
		}
		else
			$this->_ext_params[$name] = $value;
		
	}
	
	function set_block($block){
		$this->_params['block'] = $block;
	}
	
	function _callback($name){
		$args = func_get_args();
		unset($args[0]);
		if(isset($this->_callbacks[$name]))
			return call_user_func($this->_callbacks[$name], $args);
		else
			trigger_error("Callback $name not found", E_USER_ERROR);			
	}
	
	function _call_filter($name, $args, $has_result){
		include_once($this->_filters_dir.$name.'.php');
		if(is_callable('dt_filter_'.$name))
			return call_user_func('dt_filter_'.$name, $args, $has_result);
		else
			trigger_error("Filter $name not found", E_USER_ERROR);
	}

	function debug(){
		$this->_debug = true;
	}
	
	function _is_global_debug(){
		return !empty($GLOBALS['DOK_TEMPLATES_DEBUG']);
	}

	function _get_debug_data(){
		return $this->_debug_print_r($this->_data, 1);
	}
	
	function _debug_print_r($data, $open = 100000, $max_string_len = 100){		
		global $PRETTY_PRINT_R_CNT;
		if(is_null($PRETTY_PRINT_R_CNT))
			$PRETTY_PRINT_R_CNT = 0;
			
		if(is_array($data) || is_object($data)){
			if(is_array($data)){
				$out = '<font color="#aaaaaa">array('.count($data).')</font> = ';
				$iterate = $data;
			}
			else{
				$out = '<font color="#aaaaaa">object('.get_class($data).")</font> = ";
				$iterate = get_object_vars($data);
			}
			
			$out .= '<a href="#" onclick="var t = document.getElementById(\'_pretty_print_r_'.$PRETTY_PRINT_R_CNT.'\'); if(t)t.style.display=((t.style.display!=\'none\')?\'none\':\'block\'); return false;">...</a>'."<br>\n";
				
			$out .= '<table class="pretty_print_table none" cellspacing=1 cellpadding=2 width="100%" bgcolor="#dddddd" id="_pretty_print_r_'.$PRETTY_PRINT_R_CNT.'" style="display: '.($open > 0?'block':'none').'">'."\n";
			$PRETTY_PRINT_R_CNT++;
			
			ksort($iterate);
			foreach ($iterate as $k => $v) {
				$out .= '<tr><td valign="top" bgcolor="#f7f7f7" width="1%">'.$k.'</td><td valign="top" bgcolor="#ffffff">'.$this->_debug_print_r($v, $open - 1, $max_string_len).'</td></tr>'."\n";
			}
			
			$out .= '</table>'."\n";			
		}
		elseif(is_string($data)){
			$data = preg_replace('~<!--template debug data-->.*?<!--end of template debug data-->~s', '', $data);
			
			if(strlen($data) > $max_string_len){
				$out = '<font color="#aaaaaa">string</font> = "'.htmlspecialchars(substr(str_replace("\r", "", $data), 0, $max_string_len - 3)).'"';
				$out .= '<a href="#" onclick="var t = document.getElementById(\'_pretty_print_r_'.$PRETTY_PRINT_R_CNT.'\'); if(t)t.style.display=((t.style.display!=\'none\')?\'none\':\'block\'); return false;">...</a>'."<br>\n";
				$out .= '<div class="pretty_print_string" id="_pretty_print_r_'.$PRETTY_PRINT_R_CNT.'" style="display: '.($open > 0?'block':'none').'">'.str_replace("\r", "", $data)."</div>\n";
				$PRETTY_PRINT_R_CNT++;
			}
			else{
				$out = '<font color="#aaaaaa">string</font> = "'.htmlspecialchars($data).'"';
			}
		}
		elseif(is_bool($data)){
			$out = '<font color="#aaaaaa">boolean</font> = '.($data?'true':'false');
		}
		else{
			$out = '<font color="#aaaaaa">'.gettype($data).'</font> = '.$data;
		}
		
		return $out;
	}

	function get_captured(){
		return $this->_captured;
	}
}
