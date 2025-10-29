<?php
namespace App\Core;

class Controller
{
    protected function view(string $template, array $data = []): void
    {
        $view = new View();
        $view->render($template, $data);
    }

    protected function model(string $modelName): object
    {
        $modelClass = "\\App\\Models\\$modelName";
        if (!class_exists($modelClass)) {
            throw new \Exception("Model class $modelClass not found");
        }
        return new $modelClass();
    }
}


