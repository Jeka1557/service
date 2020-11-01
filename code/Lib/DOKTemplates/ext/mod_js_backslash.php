<?php

function dt_mod_js_backslash($expr, $params, &$tempate){
	return "('\"'. str_ireplace('</script>', '</scr\"+\"ipt>', preg_replace(\"~(\\r?\\n|\\r\\n?)~\", \"\\\\n\\\"+\\n\\\"\", addslashes($expr))).'\"')";
}
