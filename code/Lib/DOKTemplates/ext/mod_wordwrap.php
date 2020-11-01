<?php
/**
 * Created by JetBrains PhpStorm.
 * User: anton
 * Date: 16.02.12
 * Time: 11:04
 * To change this template use File | Settings | File Templates.
 */

function dt_mod_wordwrap($expr, $params, &$self) {
    @list($width, $break) = explode(',', $params);

    $width = $width ? (int) $width : 75;
    $break = $break ? (string) $break : '<br/>';

    return "wordwrap($expr, $width, '$break', true)";
}