<?php 
use Edalicio\EngineTemplate\Core\Template;

if(!function_exists('dd')){
    function dd($data = null) {
        echo '<pre>';
        die(var_dump($data));
    }
}

if(!function_exists('base_path')){
    function base_path() {
        return dirname(__FILE__, 3);
    }
}


if(!function_exists('view')){
    function view(string $file, $data = null, array $options = []) {

        $template = new Template();

        $template->view( $file,  $data );
    }
}


if(!function_exists('config')){
    function config(string $key):mixed {
        $configPath = base_path() . "/src/config/";
        if(!is_dir($configPath)){
            throw new Exception("Path invalida: " . $configPath);
        }
        $files = array_values(array_filter(scandir($configPath), fn($p) => ($p != '..') && ($p != '.')  )) ;
        $itens = [];
        for($i=0; $i <= count($files) - 1; $i++) {
            $keyEx = explode('.',$files[$i])[0];
            $itens[ $keyEx] =  require $configPath. $files[$i];
        }

        $key = explode('.', $key);
        
        foreach ($key as $segment) {
            if (!is_array($itens) || !array_key_exists($segment, $itens)) {
                continue;
            }

            $itens = &$itens[$segment];
        }

        return $itens;
    }
}
