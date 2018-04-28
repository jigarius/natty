<?php

namespace Module\Listing\Classes;

class ListObject
extends \Natty\ORM\EntityObject {
    
    public function createVisibility(array $data = array ()) {
        return array_merge(array (
            'id' => NULL,
            'type' => 'page',
            'name' => NULL,
            'description' => NULL,
            'isLocked' => 0,
            'status' => 1,
            'cssClass' => '',
            'renderType' => 'list',
            'renderMode' => 'preview',
            'renderLinks' => 1,
            'pagerStatus' => 0,
            'pagerOffset' => 0,
            'pagerLimit' => 0,
            'pagerLinks' => 0,
            'filterData' => array (),
            'sortData' => array (),
            'settings' => array (),
        ), $data);
    }
    
    public function readVisibility($display_id) {
        $output = FALSE;
        if ( isset ($this->visibility[$display_id]) )
            $output = $this->visibility[$display_id];
        return $output;
    }
    
    public function writeVisibility($display_id, array $definition) {
        $this->visibility[$display_id] = $definition;
        return $this;
    }
    
    public function deleteVisibility($display_id) {
        if ( isset ($this->visibility[$display_id]) )
            unset ($this->visibility[$display_id]);
        return $this;
    }
    
}