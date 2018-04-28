<?php

namespace Module\Eav\Classes\Eav;

class OutputMethod_ValueHelper
extends \Module\Eav\Classes\OutputMethodHelperAbstract {
    
    public static function buildOutput(array $values, array $options) {
        
        $output = '';
        
        foreach ( $values as $vno => $value ):
            $output .= '<div class="attr-value">' . $value . '</div>';
        endforeach;
        
        return $output;
        
    }
    
}