<?php 
namespace Edalicio\EngineTemplate\Core;
use Exception;

trait Component {

    protected string $componentPath;

    protected function component($code)
    {
        $code = $this->compileTagOnClosed($code);
        $code = $this->compileTag($code);

       return $code;
    }

    private function compileTagOnClosed($code) {
		preg_match_all('~\<e-(.*?) (.*?) ?\/>~is', $code, $matches, PREG_SET_ORDER);

        
      
		foreach ($matches as $iKey => $value) {
            if(empty($value)) {
                continue;
            }
            
            $component = $this->getComponent($value[1],$iKey);

            
            $variables = $value[2] ?? null;
            
            if($variables) {
                $component = $this->processVariables($component,$variables, $iKey);
            }            
            $component = $this->compileEchosComponent($component) ;
            $component = $component = $this->compileSlot($component,  '' );            
			$code = str_replace($value[0], $component, $code);
		}

		return $code;
	}
    private function compileTag($code) {
		preg_match_all('~<e-(.*?) (.*?) "?>(.*?)\<\/e-\1 ?>~is', $code, $matches, PREG_SET_ORDER);

        

		foreach ($matches as $iKey => $value) {
            if(empty($value)) {
                continue;
            }
            $iKey .= '_'.$iKey;
            
            $component = $this->getComponent($value[1],$iKey);

            
            $variables = $value[2] ?? false;
            
            if(!empty( $variables)) {
                $component = $this->processVariables($component,$variables, $iKey);
            }            
            
            $component = $this->compileSlot($component,  $value[3] );
            $component = $this->compileEchosComponent($component);
            
			$code = str_replace($value[0], $component, $code);
		}

		return $code;
	}

    protected function getComponent($file,$iKey)
    {
        $file = $this->componentPath . $file .$this->baseExt;

        if(!file_exists($file)){
            return '';
        }

        $code = file_get_contents($file);
        
        return $code ;
       
    }

    private function processVariables($code,$value,$iKey)
    {
        $variablesString = array_values( array_filter(explode('" ', trim($value)), function($v) {
            $v = trim($v);

            if(!empty($v)){
                return $v;
            }
        }));
        foreach($variablesString as $variable) {
            [$var, $value] = explode('=' , $variable);
            if(str_contains( $value, '<?php')) {
                $reg = "~\{{\s*\\$". $var ."\s*\}}~is";
                $code = preg_replace($reg,trim( $value, '"') ,$code);
                continue;
            }
            $reg = "~\{{\s*\\$". $var ."\s*\}}~is";

            $var .=  "_$iKey";

            $rep = "{{ $" .$var ." }}";
            $code = preg_replace($reg, $rep  ,$code);
            $value = trim($value, '"');
            $this->data[$var] = $value;
        }
        return $code;
    }

    protected function compileEchosComponent($code) {
		return preg_replace('~\{{\s*(.+?)\s*\}}~is', '<?php echo $1 ?>', $code);      
	}

    private function compileSlot($component, $content)
    {
        return preg_replace('~\<slot \/\>~is', $content, $component);   
    }
    
}