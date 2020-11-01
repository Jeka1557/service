<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 27.09.2016
 * Time: 21:53
 */



function dt_mod_h_rus($expr, $params, &$self){
    return "htmlspecialchars($expr, ENT_COMPAT | ENT_HTML401, 'cp1251')";
}