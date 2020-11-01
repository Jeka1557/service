<?php

/**
 * debug function
 * @author DoK
 * @return string
 * @param $params array
 * @param $open bool
 * @param $raw_params string
 * @param $self DOK_Template_Compiler
 **/
function dt_fn_debug($params, $open, $raw_params, &$self){
	if($self->_step != 2)
		return '';
		
	$out = '<table class="dtDebug">';
	$out .= '<tr><td>Template:</td><td>'.$self->_template_name.'</td></tr>';
	
	$out .= '<tr><td colspan="2">Assigned variables:</td></tr>';
	$out .= '<tr><td colspan="2"><pre>'.$self->_echo_start.' htmlspecialchars(print_r($this->_data, true));'.$self->_echo_end.'</td></tr>';
	
	$out .= '<tr><td colspan="2">Template params:</td></tr>';
	$out .= '<tr><td colspan="2"><pre>'.htmlspecialchars(print_r($self->_params, true)).'</pre></td></tr>';
	
	$out .= '<tr><td colspan="2">Call trace:</td></tr>';

	$bt = "<table cellspacing=0 cellpadding=3 bgcolor=#f7f7f7><tr bgcolor=#f0f0f0><td colspan=2><b>Backtrace:</b></td></tr>";

	$backtrace = array_slice(debug_backtrace(), 3);
	for($i=1; $i<count($backtrace); $i++){
		$_ = $backtrace[$i];
		$bt .= "<tr><td>".$_['file'].'(<b>'.$_['line'].'</b>)</td><td>'.$_['class'].$_['type'].$_['function']."</td></tr>\n";
	}
	$bt .= "</table>";

	$out .= '<tr><td colspan="2">'.$bt.'</td></tr>';

	
	$out .= "</table>";
	
	global $DTC_DEBUG_CNT;
	if(!isset($DTC_DEBUG_CNT))
		$DTC_DEBUG_CNT = 0;
	
	$out2 = 
'<script type="text/javascript">
var DTC_DEBUG_DATA'.$DTC_DEBUG_CNT.' = "'.preg_replace("~\r?\n~", "asdasd\" + \n\t\"", addslashes($out)).'";
function dtcShow'.$DTC_DEBUG_CNT.'(){
	var wnd = window.open("about:blank");
	wnd.document.write(DTC_DEBUG_DATA'.$DTC_DEBUG_CNT.');
}
</script>';
	
	return "<!-- DEBUG DATA -->\n$out2\n<!-- END DEBUG DATA -->";
}
