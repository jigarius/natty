<?php

namespace Module\Listing;

class Controller
extends \Natty\Core\PackageObject {
    
    public static function onSystemRouteDeclare(&$data) {
        include 'declare/system-route.php';
    }
    
    public static function onListingDatatypeDeclare(&$data) {
        include 'declare/listing-datatype.php';
    }
    
}