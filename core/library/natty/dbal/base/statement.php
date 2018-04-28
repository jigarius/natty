<?php

namespace Natty\DBAL\Base;

class Statement
extends \PDOStatement {
    
    public function execute($input_parameters = NULL) {
        $output = parent::execute($input_parameters);
        if ( TRUE !== $output )
            return $output;
        return $this;
    }
    
    public function preview($return = FALSE) {
        
        $output = '<table cellspacing="0" cellpadding="3" border="1">';
        
        $list_head = FALSE;
        while ($record = $this->fetch(\PDO::FETCH_ASSOC)):
            
            if (!$list_head):
                $list_head = TRUE;
                $output .= '<tr>';
                foreach ($record as $prop => $value):
                    $output .= '<th>' . htmlspecialchars($prop) . '</th>';
                endforeach;
                $output .= '</tr>';
            endif;
            
            $output .= '<tr>';
            foreach ($record as $prop => $value):
                $output .= '<td>' . htmlspecialchars($value) . '</td>';
            endforeach;
            $output .= '</tr>';
            
        endwhile;
        
        $output .= '</table>';
        
        if ($return)
            return $output;
        
        echo $output;
        natty_debug();
        
    }
    
}