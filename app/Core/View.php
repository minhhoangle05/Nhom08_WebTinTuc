<?php
namespace App\Core;

class View
{
    public function render(string $template, array $data = []): void
    {
        $layout = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main.php';
        $content = $this->renderPartial($template, $data);
        include $layout;
    }

    public function renderPartial(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        $viewFile = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $template . '.php';
        ob_start();
        include $viewFile;
        return ob_get_clean();
    }
}


