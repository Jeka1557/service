<?php

namespace Lib\DOKTemplates;

class DOKTemplateDebugger {
	public $data;
	public $meta;
	
	function __construct($data, $meta){
		$this->data = $data;
		$this->meta = $meta;
	}
	
	function run(){
		$skeleton = array();
		$this->make_data_skeleton($skeleton, $this->meta);
		
		$this->fill_map($skeleton, $this->data);
		
		$out = '';
		
		$out .= <<<HEAD
		
			<style type="text/css">
			body, table {
				font-size: 13px;
				font-family: Arial;
			}
			.body{
				text-align: left;
				border-right: 1px solid #cce;
				border-bottom: 1px solid #cce;
			}
/*			.parity0{
				background-color: white;				
			}
			.parity1{
				background-color: #f7f7f7;
			}*/
			.context{
				text-align: left;
				border-left: 1px solid #cce;
				border-top: 1px solid #cce;
				font-size: 13px;
				font-family: Arial;
				clear: both;
			}
			.contexts {
			}
			.contexts .context{
				margin-left: 20px;
			}
			.context .name{
				font-weight: bold;
				border-right: 1px solid #cce;
				border-left: 5px solid #cce;
				border-bottom: 1px solid #cce;
				float: left;
				padding: 3px;
				background-color: #eef0f4;
			}
			.context .name a{
				color: #009;
			}
			.empty{
				clear: both;
				padding: 3px;
			}
			.context .vars{
				padding: 3px;
				clear: both;
			}
			
			.data{
				text-align: left;
				font-size: 13px;
				font-family: Arial;
				clear: both;
				margin-top: 4px;
			}
			
			.data .name{
				text-align: left;
/*				font-weight: bold;*/
				color: #00a;
			}
			
			.data .value{
			}

			.data .data{
				margin-left: 20px;
			}
			</style>
		<div class="body">
HEAD;
		$out .= $this->dump_meta($this->meta, $skeleton);
		
//		$out .= '</div><b>Данные:</b>';
//		$out .= $this->dump_data('global', $this->data, '__tmpl_global');

		$out .= <<<BOTTOM
		</div>
		<script type="text/javascript">
		function context_over(){
			this._oldBorder = this.style.borderColor;
			this.style.borderColor = '#5a5';
			this.style.borderWidth = '2px';
		}
		function context_out(){
			this.style.borderColor = this._oldBorder;
			this.style.borderWidth = '1px';
		}
		var items = document.getElementsByTagName('DIV');
		for(var i in items){
			if(items[i].className && items[i].className.indexOf('context') != -1){			
//				items[i].onmouseover = context_over;
//				items[i].onmouseout = context_out;
			}
		}
		</script>
BOTTOM;
		return $out;
	}
	
	function &find_path(&$data, $path){
		if($path === '' || $path === null)
			return $data;
		$items = explode('/', $path);	
		foreach ($items as $_) {
			$t = &$data['__sub_items'][$_];
			unset($data);
			$data = &$t;
		}
		
		return $data;
	}
	
	function make_data_skeleton(&$data, $context){
		$item = &$this->find_path($data, isset($context['var_path']) ? $context['var_path'] : null);
		if(!empty($context['is_array']))
			$item['context'] = 'array';
		else
			$item['context'] = 'simple';
		$item['struct_only'] = true;
		
		if(!empty($context['contexts']))
			foreach ($context['contexts'] as $_) {
				$this->make_data_skeleton($data, $_);
			}
	}
	
	function fill_map(&$map, $data){
		$map['struct_only'] = false;

		if(is_null($data))
			$map['null'] = true;
		if($data === false)
			$map['false'] = true;
		if(is_int($data))
			$map['int'] = true;
		if(is_string($data))
			$map['string'] = true;
		if(is_object($data))
			$map['object'] = true;
		if(is_bool($data))
			$map['bool'] = true;
		if(is_string($data) and empty($data))
			$map['empty_str'] = true;
			
		if(is_array($data)){
			$map['array'] = true;

			if(isset($map['context']) && $map['context'] == 'array'){
				foreach ($data as $key => $row) {
					if(is_array($row)){
						$map['__sub_items'][$key]['struct_only'] = false;
						$map['__sub_items'][$key]['array'] = true;
						foreach ($row as $k => $value) {
							$this->fill_map($map['__sub_items'][$k], $value);
						}
					}
					else{
						$this->fill_map($map['__sub_items'][$key], $row);
					}
				}
			}
			elseif(isset($map['context']) && $map['context'] == 'simple') {
				foreach ($data as $k => $value) {
					$this->fill_map($map['__sub_items'][$k], $value);
				}
			}
		}
	}
	
	function dump_meta($context, $data, $path = ''){
		if($path)
			$path .= '_';
		$path .= $context['name'];
		static $parity = 0;
		$parity++;
		$items = $this->find_path($data, isset($context['var_path']) ? $context['var_path'] : null);//$data['__sub_items'];
		$items = isset($items['__sub_items']) ? $items['__sub_items'] : null;
		@$out = '<div class="context parity'.($parity%2).'"><div class="name"><a href="#'.$path.'">'.$context['name']." (\${$context['var_name']}, {$context['var_path']})".'</a></div>';
		if(!empty($context['vars'])){
			$out .= '<div class="vars">';
			
			ksort($context['vars']);
			foreach ($context['vars'] as $var => $count) {
				$char = '';
				if($var{0} !== '#'){
					if(isset($items[$var]) && !$items[$var]['struct_only']){
						foreach (array('int', 'bool', 'string', 'array', 'empty_str', 'null', 'false', 'object') as $_) {
							if(isset($items[$var][$_]))
								$char .= "$_, ";
						}
					}
					else{
						$char = '<font color="red">отсутствует</font>, ';
					}
				}
				
				$char .= " вхождений: $count";
				$out .= '<div class="var">'.$var.' : '.$char.'</div>';
			}
			$out .= '</div>';
		}
		if(!empty($context['contexts'])){
			$out .= '<div class="contexts">';
			foreach ($context['contexts'] as $_) {
				$out .= $this->dump_meta($_, $data, $path);
			}
			$out .= '</div>';
		}
		if(empty($context['vars']) && empty($context['contexts'])){
			$out .= '<div class="empty">Пусто</div>';
		}
		$out .= '</div>';
		$parity--;
		return $out;
	}
	
	function dump_data($name, $data, $path = ''){
		$out = '<div class="data">';
		$out .= "<span class=name>$name</span> <span class=type>(".gettype($data).")</span> :";
		if(!is_array($data) && !is_object($data)) $out .=" <span class=value>$data</span>";
		else{
			$out .= '<a name="'.$path.'"></a>';
		}
		
		if(is_array($data)){
			foreach ($data as $k => $_) {
				$out .= $this->dump_data($k, $_, $path.'_'.$k);
			}
		}
		elseif(is_object($data))
			foreach (get_object_vars($data) as $k => $v) {
				$out .= $this->dump_data($k, $v, $path.'_'.$k);
			}
		
		$out .= '</div>';
		return $out;
	}
}
