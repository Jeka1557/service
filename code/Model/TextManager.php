<?php



namespace Model;


class TextManager {

    public function __construct() { }


    public function clean($text) {
        $text = trim($text);
        $text = strip_tags($text, "<br><p><a><div><i>");

        $text = preg_replace("~(?!<div>)&nbsp;(?!</div>)~", " ", $text);

        $text = preg_replace("~\s*style=([\"']).+(\\1)\s*~U", "", $text);
        $text = preg_replace("~\s*class=([\"']).+(\\1)\s*~U", "", $text);

        // <div> <div></div><p>&nbsp;</p> ...
        $text = preg_replace("~^(<div>|<p>)?[\s\n]*(((<p>(\s+|(&nbsp;)+)?</p>)|(<div>(\s+|(&nbsp;)+)?</div>))(\s|\n)*)+~", "\\1", $text);

        // <div></div><p>&nbsp;</p> ... </div>
        $text = preg_replace("~(((<p>(\s+|(&nbsp;)+)?</p>)|(<div>(\s+|(&nbsp;)+)?</div>))(\s|\n)*)+(</div>|</p>|</i>)?$~", "\\10", $text);

        // <br><br>...</div>
        $text = preg_replace("~(<br\s*\/?>([\s\n]|&nbsp;)*)+(</div>|</p>|</i>)$~", "\\3", $text);

        return $text;
    }

}