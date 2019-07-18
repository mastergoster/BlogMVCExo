<?php

namespace Core\Controller;

use Core\Controller\Session\FlashService;
use Core\Extension\Twig\FlashExtension;

class Controller
{

    private $twig;

    private $app;


    protected function render(string $view, array $variables = []): string
    {

        $variables["debugTime"] = $this->getApp()->getDebugTime();
        return $this->getTwig()->render(
            $view . '.twig',
            $variables
        );
    }

    private function getTwig()
    {
        if (is_null($this->twig)) {
            $loader = new \Twig\Loader\FilesystemLoader(dirname(dirname(__dir__)) . '/views/');
            $this->twig = new \Twig\Environment($loader);
            $this->twig->addGlobal('session', $_SESSION);
            $this->twig->addGlobal('constant', get_defined_constants());
            $this->twig->addExtension(new FlashExtension());
        }
        return $this->twig;
    }

    protected function getApp()
    {
        if (is_null($this->app)) {
            $this->app = \App\App::getInstance();
        }
        return $this->app;
    }

    protected function generateUrl(string $routeName, array $params = []): String
    {
        return $this->getApp()->getRouter()->url($routeName, $params);
    }

    protected function loadModel(string $nameTable): void
    {
        $this->$nameTable = $this->getApp()->getTable($nameTable);
    }

    protected function flash(): FlashService
    {
        return $this->getApp()->flash();
    }
}
