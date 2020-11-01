<?php


namespace Infr\Template;



class DB extends \Infr\Template {

    protected static $ASSIGNED_VARS = [];

    function __construct($entityName, $entityId, $updated, $body){
        $templFile = SERVICE_ROOT."/cache/templ-db/{$entityName}/{$entityId}_{$updated}.html";

        if (!file_exists($templFile)) {
            $this->deleteEntity($entityName, $entityId);
            file_put_contents($templFile, $body);
        }

        parent::__construct($templFile);
    }


    protected function deleteEntity($entityName, $entityId) {
        $dir = SERVICE_ROOT."/cache/templ-db/{$entityName}";
        $files = scandir($dir);

        foreach ($files as $file) {
            if (preg_match("/{$entityId}_\d+\.html/", $file)) {
                unlink($dir.'/'.$file);
            }
        }
    }

}