<?php


namespace Infr\Template;

use Lib\Infr\Utility\Encoding;

ini_set('short_open_tag', 'Off');

class XML extends  \Infr\Template {

    public function parse()
    {
        self::$UTF8 = true;
        self::$ALLOW_HTML = false;
        self::$loadImages = [];

        $result =  parent::parse();

        self::$UTF8 = false;
        self::$ALLOW_HTML = true;

        return $result;
    }
    
    public function needLoadImage() {
        return count(self::$loadImages)>0?true:false;
    }
    
    public function loadImages(){
        return self::$loadImages;
    }
}