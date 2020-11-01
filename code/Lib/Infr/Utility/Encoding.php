<?php

namespace Lib\Infr\Utility;


final class Encoding {

    protected static $jsonErrors = array(
        JSON_ERROR_NONE => null, //'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
    );

    /**
     * Конвертер кодировок для всех типов.
     *
     * @static
     * @param string $inCharset
     * @param string $outCharset
     * @param mixed $data
     * @return mixed
     * @access public
     */
    public static function mixedIconv($inCharset, $outCharset, $data) {
        if ($inCharset == $outCharset) {
            return $data;
        }

        if (is_array($data)) {
            $new = array();
            foreach ($data as $k => $v) {
                $new[self::mixedIconv($inCharset, $outCharset, $k)] = self::mixedIconv($inCharset, $outCharset, $v);
            }
            $data = $new;

        } elseif (is_object($data)) {
            $vars = get_object_vars($data);
            foreach ($vars as $m => $v) {
                $data->$m = self::mixedIconv($inCharset, $outCharset, $v);
            }

        } elseif (is_string($data)) {
            $data = iconv($inCharset, $outCharset, $data);
        }

        return $data;
    }

    /**
     * base64 кодирование строки для передачи в URL.
     *
     * @static
     * @param string $str
     * @return string
     * @access public
     */
    public static function base64UrlEncode($str) {
        return @strtr(@base64_encode($str), '+/=', '-_,');
    }

    /**
     * base64 декодирование строки, переданной в URL.
     *
     * @static
     * @param string $str
     * @return string
     * @access public
     */
    public static function base64UrlDecode($str) {
        return @base64_decode(@strtr($str, '-_,', '+/='));
    }

    /**
     * @static
     * @param string $str
     * @return string
     * @access public
     */
    public static function unhtmlentities($str) {
        $trans = get_html_translation_table(HTML_ENTITIES);
        $trans = array_flip($trans);
        return strtr($str, $trans);
    }

    /**
     * @static
     * @param string $data
     * @return string
     * @access public
     */
    public static function htmlEncode($data) {
        return self::unhtmlentities(htmlentities($data, ENT_COMPAT, 'cp1251'));
    }

    public static function fixCyrillicArrayContent(&$value, &$key, $inUnicode) {
        if ($inUnicode) {
            $inCharset = 'cp1251';
            $outCharset = 'utf-8';
        } else {
            $inCharset = 'utf-8';
            $outCharset = 'cp1251';
        }
        if (is_string($value)) {
            $value = iconv($inCharset, $outCharset, $value);
        }
        $key = iconv($inCharset, $outCharset, $key);
    }

    /**
     * При кодировании, помимо того что устанавливаем кодировку (как в fixCyrillicArrayContent),
     * также преобразуем объекты к массиву.
     *
     * @param $data
     * @return array
     */
    public static function objectToArrayRecursive($data) {
        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }
        if (is_array($data)) {
            $new = array();
            foreach($data as $key => $val) {
                $key = iconv('cp1251', 'utf-8//IGNORE', $key);
                $new[$key] = self::objectToArrayRecursive($val);
            }
        } else if (is_string($data)) {
            $new = iconv('cp1251', 'utf-8//IGNORE', $data);
        } else {
            $new = $data;
        }
        return $new;
    }

    /**
     * @static
     * @param mixed $data
     * @param string|null $error
     * @return string
     * @access public
     */
    public static function jsonEncode($data, &$error = null, $prettyPrint = false) {
        $data = self::objectToArrayRecursive($data);
        $data = json_encode($data, $prettyPrint?JSON_PRETTY_PRINT:0);
        $error = self::$jsonErrors[json_last_error()];

        if ($error) {
            return null;
        }

        return $data;
    }

    /**
     * @static
     * @param string $data
     * @param string|null $error
     * @return mixed|null
     * @access public
     */
    public static function jsonDecode($data, &$error = null) {
        $data = self::jsonDecodeOrig($data, $error);

        // только если данные являются массивом мы можем применить к ним исправление кодировки
        if (is_array($data)) {
            array_walk_recursive($data, array('\Lib\Infr\Utility\Encoding', 'fixCyrillicArrayContent'), false);
        }

        if ($error) {
            return null;
        }

        return $data;
    }

    /**
     * @static
     * @param string $data
     * @param string|null $error
     * @return mixed|null
     * @access public
     */
    public static function jsonDecodeOrig($data, &$error = null) {
        $data = @json_decode($data, true, 50);
        $error = self::$jsonErrors[json_last_error()];

        if ($error) {
            return null;
        }

        return $data;
    }




    /**
     * @static
     * @param string $str
     * @return string
     * @access public
     */
    public static function win2uni($str) {
        $str = convert_cyr_string($str, 'w', 'i'); //  win1251 -> iso8859-5
        $len = strlen($str);
        for ($result = '', $i = 0; $i < $len; $i++) {
            $charcode = ord($str[$i]);
            $result .= ($charcode > 175) ? "&#" . (1040 + ($charcode - 176)) . ";" : $str[$i];
        }

        return $result;
    }
}