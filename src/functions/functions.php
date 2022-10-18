<?php 
use Edalicio\EngineTemplate\Template;

if(!function_exists('dd')){
    function dd($data = null) {
        echo '<pre>';
        die(var_dump($data));
        echo '</pre>';
    }
}


if(!function_exists('view')){
    function view(string $file, $data = null, array $options = []) {

        // string $viewPath = '/' , string $cachePath = 'cache/' , bool $cacheEnabled = false, string $baseExt = 'html'

        $opt = [
            'viewPath' => __DIR__ . '/../../views/'
        ];

        $opt = array_merge($options , $opt);    

        $template = new Template($opt);

        $template->view( $file,  $data );
    }
}

