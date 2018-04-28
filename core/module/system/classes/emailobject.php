<?php

namespace Module\System\Classes;

class EmailObject
extends \Natty\ORM\EntityObject {
    
    public function send(array $options = array ()) {
        return $this->getHandler()->send($this, $options);
    }
    
}