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
 * Пример использования в шаблоне:
 * {filter table_cols $countries cols=4}
 * <table>
 * {loop $countries}
 * <tr>
 * 	{loop $#}
 * 		<td>
 * 		{if $#false}Пусто :(
 * 		{else}
 * 			{$name}
 * 		{/if}
 * 		</td>
 *  {/loop}
 * </tr>
 * {/loop}
 * </table>;
 * в этом случае $countries после вызова будет представлять
 * array(
 *   array(
 *     array( 'name' => 'Азейрбаджан1', 'id' => 123 ),
 *     array( 'name' => 'Азейрбаджан2', 'id' => 1234 ),
 *     array( 'name' => 'Азейрбаджан3', 'id' => 1235 ),
 *     array( 'name' => 'Азейрбаджан4', 'id' => 1236 ),
 *   ),
 *   ...
 *   array(
 * 		array( 'name' => 'Зимбабве', 'id' => 12345),
 * 		false,
 * 		false,
 * 		false,
 *   )
 * )
 * 
 * {filter table_cols $countries cols=4 result=$countries_rows}
 * В этом случае $countries останется неизменным, а данные будут записаны в
 * $countries_rows
 * 
 * @author DoK
 * @return array
 **/
function dt_filter_table_cols($args, $has_result){
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
		$rows = ceil(count($data) / $cols);
		
		foreach ($data as $_){
			$out[$i % $rows][$i / $rows] = $_;
			$i++;
		}
		
		while($i % $rows){
			$out[$i % $rows][$i / $rows] = false;
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
