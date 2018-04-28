<?php

namespace Module\Taxonomy\Logic;

class Backend_TermController {
    
    public static function pageBrowse($tgroup, $parent = NULL) {
        
        // Load dependencies
        $term_handler = \Natty::getHandler('taxonomy--term');

        // Read data
        $read_options = array (
            'key' => array (),
            'parameters' => array (),
            'ordering' => array (
                'ooa' => 'asc',
            ),
        );
        
        if ( $tgroup )
            $read_options['key']['gid'] = $tgroup->gid;
        
        $read_options['key']['parentId'] = $parent
            ? $parent->tid : 0;
        
        $term_coll = $term_handler->read($read_options);

        // List body
        $list_body = array ();
        foreach ( $term_coll as $tid => $term ):

            $item = array (
                '_render' => 'entity',
                '_entity' => $term,
                '_options' => array (
                    'viewMode' => 'preview',
                    'page' => FALSE,
                ),
            );

            $list_body[] = $item;

        endforeach;

        // Prepare response
        $output[] = array (
            '_render' => 'list',
            '_items' => $list_body,
            'class' => array ('taxonomy-term-list'),
        );
        
        return $output;
        
    }
    
}