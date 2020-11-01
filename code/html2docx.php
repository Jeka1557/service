<?php


class HTML2DOCX {

    static protected $tmpDir;
    static protected $tmpDirs = [];

    static protected $remoteLoad = false;
    static protected $remoteURL;

    static protected $shellScript;

    static public function setRemoteLoad($url) {
        self::$remoteLoad = true;
        self::$remoteURL = $url;
    }


    static public function setTmpDir($dir) {
        self::$tmpDir = $dir;
    }

    static public function setShellScript($script) {
        self::$shellScript = $script;
    }

    static public function clean() {
        try {
            foreach (self::$tmpDirs as $dir) {
                self::deleteDir($dir);
            }
        } catch (\Exception $e) {
            file_put_contents(self::$tmpDir.'/error.log', "\n\n".(string)$e, FILE_APPEND | LOCK_EX);
        }
    }

    static public function convert($html) {

        if (self::$remoteLoad) {
            $postData = http_build_query(['html_file' => $html]);

            $opts = ['http' =>
                [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postData
                ]
            ];

            $context  = stream_context_create($opts);
            $result = file_get_contents(self::$remoteURL.'/htmltodocx.php', false, $context);


            if (!strlen($result))
                throw new Exception("File conversion to Docx failed. Received empty result.");

            return $result;

        } else {

            $uniqid = uniqid('',true);
            $tmpDir = self::$tmpDir.'/'.$uniqid;

            mkdir ($tmpDir, 0777 );
            self::$tmpDirs[] = $tmpDir;

            $htmlFile = "{$tmpDir}/document.html";
            $docxFile = "{$tmpDir}/document.docx";

            file_put_contents($htmlFile, $html);

            // HOME нужно установить т.к. libreoffice используют ее как диру профиля и должен мочь в нее писать
            if (self::$shellScript)
                $command = self::$shellScript.' "'.$tmpDir.'" "'.$htmlFile.'"';
            else
                $command = 'HOME="'.$tmpDir.'" && export HOME && libreoffice --headless --invisible --nocrashreport --nodefault --nofirststartwizard --nologo --norestore --writer --convert-to "docx" --outdir "'.$tmpDir.'" "'.$htmlFile.'"';

            $result = [];

            exec($command, $result);
            $data = file_get_contents($docxFile);

            self::clean();

            return $data;
        }
    }


    static public function convDocx2PDF($docx) {

        if (self::$remoteLoad) {
            $postData = http_build_query(['docx_file' => $docx]);

            $opts = ['http' =>
                [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postData
                ]
            ];

            $context  = stream_context_create($opts);
            $result = file_get_contents(self::$remoteURL.'/docxtopdf.php', false, $context);


            if (!strlen($result))
                throw new Exception("File conversion to PDF failed. Received empty result.");

            return $result;

        } else {

            $uniqid = uniqid('',true);
            $tmpDir = self::$tmpDir.'/'.$uniqid;

            mkdir ($tmpDir, 0777 );
            self::$tmpDirs[] = $tmpDir;

            $docxFile = "{$tmpDir}/document.docx";
            $pdfFile = "{$tmpDir}/document.pdf";

            file_put_contents($docxFile, $docx);

            // HOME нужно установить т.к. libreoffice используют ее как диру профиля и должен мочь в нее писать
            if (self::$shellScript)
                $command = self::$shellScript.' "'.$tmpDir.'" "'.$docxFile.'"';
            else
                $command = 'HOME="'.$tmpDir.'" && export HOME && libreoffice --headless --invisible --nocrashreport --nodefault --nofirststartwizard --nologo --norestore --writer --convert-to "pdf" --outdir "'.$tmpDir.'" "'.$docxFile.'" 2>&1';

            $result = [];

            exec($command, $result);
            $data = file_get_contents($pdfFile);

            self::clean();

            return $data;
        }
    }




    static public function deleteDir($dirPath) {
        $dh = opendir($dirPath);

        if ($dh===false)
            throw new \Exception("Can't open dir: {$dirPath} error: ".error_get_last());

        while (($file = readdir($dh)) !== false) {
            if ($file=='.' or $file=='..')
                continue;

            $file = $dirPath.'/'.$file;

            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                file_put_contents($file, '');
                if (unlink($file)===false)
                    throw new \Exception("Can't unlink file: {$file} error: ".error_get_last());
            }
        }

        @closedir($dh);

        if (rmdir($dirPath)===false)
            throw new \Exception("Can't remove dir: {$dirPath} error: ".error_get_last());
    }

}
