<?php


namespace App\Controller;

use App\Controller;
use \Infr\Config;
use Infr\WordRenderer;


class Test extends Controller {

    const TESTS_DIR = __DIR__.'/../../../../tests';

    protected $frame = 'test';


    public function indexAction() {

        return 'Tests';
    }

    public function funcAction() {

        // var_dump(image_type_to_extension(IMAGETYPE_JPEG2000));

        $url = 'https://fdfdsafds.ru/?fdsafdsf=fdsa';

        $url = 'http://fds.fdsa.ru33333???#fdsa';

        $url = parse_url($url);


        $url['host'] = idn_to_ascii($url['host'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        var_dump($url);

        $url = (isset($url['scheme'])?$url['scheme'].'://':'//').$url['host'].(isset($url['path'])?$url['path']:'/').(isset($url['query'])?'?'.$url['query']:'').(isset($url['fragment'])?'#'.$url['fragment']:'');

        var_dump($url);


        var_dump(filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED));

        return '';

        /*
        $email = iconv('windows-1251','UTF-8', $data);

        if (mb_strpos($email, '@', 0, 'UTF-8')===false)
            return false;

        list($account, $domain) = explode('@', $email);

        $account = idn_to_ascii($account, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        $domain = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        $email = $account.'@'.$domain;

        $result = filter_var($email, FILTER_VALIDATE_EMAIL);

        if ($result===false)
            return false;
        else
            return true;



        return '';
        */
    }



    public function wordRendererAction() {

        return [
        ];
    }


    public function wordRendererFileAction() {

        \Infr\Template::setInfo([
            ['extId' => '3016', 'dataText' => $_POST['url_1'], 'algIds' => 1],
            ['extId' => '3050', 'dataText' => $_POST['url_2'], 'algIds' => 1],

        ]);


        $file = self::TESTS_DIR.'/word-renderer/image-test.docx';


        $renderer  = new WordRenderer(1,103,  self::TESTS_DIR.'/word-renderer/cache');

        $renderer->cache(file_get_contents($file));

        $content = $renderer->render();

        $fileName = "image-test.docx";
        $fileSize = strlen($content);

        ob_clean();
        header($_SERVER["SERVER_PROTOCOL"].' 200 OK');
        header('Cache-Control: public');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Length: '.$fileSize);
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        echo $content;

        return '';
    }

}