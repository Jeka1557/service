<?php
/**
 * Created by PhpStorm.
 * User: Jeka
 * Date: 21.12.2017
 * Time: 01:46
 */

namespace Infr;

class Controller {

    protected $frame;

    protected $templateRoot = SERVICE_ROOT.'/templ';

    public function notFoundAction() {
        return [];
    }


    static public function run() {
        $urlParts = parse_url($_SERVER['REQUEST_URI']);

        $pathParts = explode('/', trim($urlParts['path'],'/'));

        if (empty($pathParts[0]))
            $pathParts = [];


        switch (count($pathParts)) {
            case 0:
                $class = '\App\Controller';
                $action = 'index';
                break;

            case 1:
                $class = '\App\Controller';
                $action = strtolower($pathParts[0]);
                break;

            case 2:
            default:
                $class = $class = '\App\Controller\\'.ucfirst(strtolower($pathParts[0]));
                $action = strtolower($pathParts[1]);
                break;
        }

        $controller = new $class();

        return $controller->runAction($action);
    }


    protected function runAction($action) {
        $action = str_replace('_', '-', $action);

        $method = $this->getMethodName($action);

        if ($method===false) {
            $action = 'not-found';
            $method = 'notFoundAction';
        }

        $result = $this->$method();

        if (!is_array($result))
            return $result;

        $templateFile = $this->templateRoot.'/'.str_replace('\\', '/', get_class($this)) . "/{$action}.phtml";

        extract($result);
        ob_start();
        require($templateFile);

        $content = ob_get_clean();


        return $this->renderFrame($content);
    }


    protected function renderFrame($content) {
        if (empty($this->frame))
            return $content;

        ob_start();
        require($this->templateRoot."/App/Frame/{$this->frame}.phtml");
        $html = ob_get_clean();

        return $html;
    }


    protected function getMethodName($action) {

        $parts = explode('-', $action);

        foreach ($parts as &$part) {
            $part = ucfirst($part);
        }

        $method = implode('', $parts).'Action';

        if (method_exists($this, $method))
            return $method;
        else
            return false;
    }
}