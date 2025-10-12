<?php
namespace App\Core;

class View
{
    private array $data = [];

    public function render(string $template, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        
        // Get the content first
        $content = $this->renderPartial($template, $this->data);
        
        // Add content to data array
        $this->data['content'] = $content;
        
        // Extract all variables
        extract($this->data, EXTR_SKIP);
        
        // Include the main layout
        include BASE_PATH . '/app/views/layouts/main.php';
    }

    public function renderPartial(string $template, array $data = []): string
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data, EXTR_SKIP);
        
        $viewFile = BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $template . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$template}");
        }

        ob_start();
        include $viewFile;
        return ob_get_clean();
    }
}


