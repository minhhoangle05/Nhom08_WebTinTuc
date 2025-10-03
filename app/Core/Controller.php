<?php
namespace App\Core;

class Controller
{
    protected function view(string $template, array $data = []): void
    {
        $view = new View();
        $view->render($template, $data);
    }
}


