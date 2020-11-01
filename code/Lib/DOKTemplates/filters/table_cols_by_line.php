<?php

/**
 * Форматирует вывод в колонки
 * Входные данные:
 *   $args[0] или $args['data'] - массив входных данных
 *   $args['cols'] - количество колонок
 * Выходные данные:
 *   массив строк таблицы, в них - массивы ячеек (с исходными данными),
 *   пустые ячейки - false
 * 
 * Полностью идентичен фильтру table_cols (см пример использованию в нем)
 * за одним исключением - форимирует таблицу по стракам.
 *  
 *  Отличие между table_cols_by_line и table_cols
 *  $menu = array('user', 'area', 'place');
 *  после применения'
 *  {filter table_cols_by_line $menu cols=2}
 *  $menu = array(
 * 		array('user', 'area'), 
 * 		array('place', false)
 *	);
 *  в то время как table_cols вернет
 * {filter table_cols $menu cols=2}
 *  $menu = array(
 * 		array('user', 'place'), 
 * 		array('area', false)
 *	);
 * 
 * 
 * @author wild_honey
 * @return array
 **/
function dt_filter_table_cols_by_line($args, $has_result){
	if(!$cols = $args['cols'])
		return trigger_error("column number is not specified");
	if(!isset($args[0]) && !isset($args['data']))
		return trigger_error("data is not specified", E_USER_ERROR);
	
	if(isset($args['data']))
		$data = $args['data'];
	else 
		$data = $args[0];
		
	$i = 0;
	$out = array();
		
	if(is_array($data) && count($data)){
		foreach ($data as $_){
			$out[$i / $cols][$i % $cols] = $_;
			$i++;
		}
		
		while($i % $cols){
			$out[$i / $cols][$i % $cols] = false;
			$i++;
		}
	}
	
	
	if($has_result)
		return $out;
	else{
		if(isset($args[0]))
			$args[0] = $out;
		else
			$args['data'] = $out;
	}
}
