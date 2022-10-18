<?php
namespace Edalicio\EngineTemplate;
class Template {

	private array $blocks = [];
	private string $cachePath = 'cache/';
	private bool $cacheEnabled  = false;
    private string $viewPath  = '/views/';
    private string $baseExt = '.html';



    function __construct(array $options = []) {
		foreach ($options as $key => $value ){
            $this->$key =$value;
        }
    }

	public function view(string $file, $data = []) {
		$cached_file = $this->cache($file);
		
	    extract((array) $data, EXTR_SKIP);
	   	require $cached_file;
	}

	private function cache($file) {
		if (!file_exists($this->cachePath)) {
		  	mkdir($this->cachePath, 0777);
		}
	    $cached_file = $this->cachePath . str_replace(array('/', $this->baseExt), array('_', ''), $file . '.php');
	    if (!$this->cacheEnabled || !file_exists($cached_file) || filemtime($cached_file) < filemtime($file)) {
			$code = $this->includeFiles($file);
			$code = $this->compileCode($code);
	        file_put_contents($cached_file, '<?php class_exists(\'' . __CLASS__ . '\') or exit; ?>' . PHP_EOL . $code);
	    }
		return $cached_file;
	}

	public function clearCache() {
		foreach(glob($this->cachePath . '*') as $file) {
			unlink($file);
		}
	}

	private function compileCode($code) {
		$code = $this->compileBlock($code);
		$code = $this->compileYield($code);
		$code = $this->compileEscapedEchos($code);
		$code = $this->compileEchos($code);
		$code = $this->compilePHP($code);
		return $code;
	}

	private function includeFiles($file) {
		$file = str_replace('.', '/' , $file );
		$code = file_get_contents($this->viewPath . $file.$this->baseExt);
		preg_match_all('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $value) {
			$code = str_replace($value[0], $this->includeFiles($value[2]), $code);
		}
		$code = preg_replace('/{% ?(extends|include) ?\'?(.*?)\'? ?%}/i', '', $code);
		return $code;
	}

	private function compilePHP($code) {
		return preg_replace('~\{%\s*(.+?)\s*\%}~is', '<?php $1 ?>', $code);
	}

	private function compileEchos($code) {
		return preg_replace('~\{{\s*(.+?)\s*\}}~is', '<?php echo $1 ?>', $code);
	}

	private function compileEscapedEchos($code) {
		return preg_replace('~\{{{\s*(.+?)\s*\}}}~is', '<?php echo htmlentities($1, ENT_QUOTES, \'UTF-8\') ?>', $code);
	}

	private function compileBlock($code) {
		preg_match_all('/{% ?block ?(.*?) ?%}(.*?){% ?endblock ?%}/is', $code, $matches, PREG_SET_ORDER);
		foreach ($matches as $value) {
			if (!array_key_exists($value[1], $this->blocks)) $this->blocks[$value[1]] = '';
			if (strpos($value[2], '@parent') === false) {
				$this->blocks[$value[1]] = $value[2];
			} else {
				$this->blocks[$value[1]] = str_replace('@parent', $this->blocks[$value[1]], $value[2]);
			}
			$code = str_replace($value[0], '', $code);
		}
		return $code;
	}

	private function compileYield($code) {
		foreach($this->blocks as $block => $value) {
			$code = preg_replace('/{% ?yield ?' . $block . ' ?%}/', $value, $code);
		}
		$code = preg_replace('/{% ?yield ?(.*?) ?%}/i', '', $code);
		return $code;
	}

}
?>