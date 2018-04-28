<?php

namespace Module\Cms\Classes\System;

class Block_MarkupHelper
extends \Module\System\Classes\BlockHelperAbstract {
    
    const TABLENAME = '%__cms_block_content';
    
    public static function handleSettingsForm(&$data) {
        
        $form =& $data['form'];
        $entity =& $data['entity'];
        $dbo = \Natty::getDbo();
        
        $fcont_default =& $form->items['default'];
        
        switch ( $form->getStatus() ):
            case 'prepare':

                // Read existing content
                $default_content = $dbo->read(self::TABLENAME, array (
                    'key' => array (
                        'biid' => $entity->biid,
                        'ail' => \Natty::getOutputLangId(),
                    ),
                    'unique' => 1,
                ));
                if ( $default_content )
                    $default_content = $default_content['content'];
                
                $fcont_default['_data']['content'] = array (
                    '_widget' => 'rte',
                    '_label' => 'Content',
                    '_default' => $default_content,
                    'required' => 1,
                );
                
                break;
            case 'process':
                
                $form_data = $form->getValues();
                $record = array (
                    'biid' => $entity->biid,
                    'ail' => \Natty::getOutputLangId(),
                    'content' => $form_data['content'],
                );
                
                $dbo->upsert(self::TABLENAME, $record, array (
                    'keys' => array ('biid', 'ail')
                ));
                
                break;
        endswitch;
        
    }
    
    public static function buildOutput(array $settings) {
        
        // Read content
        $record = \Natty::getDbo()->read(self::TABLENAME, array (
            'key' => array (
                'biid' => $settings['biid'],
                'ail' => \Natty::getOutputLangId(),
            ),
            'unique' => 1,
        ));

        $output = array (
            '_data' => $record ? $record['content'] : FALSE,
        );
        
        return $output;
        
    }
    
    public static function onBeforeDelete(&$entity, array $options = array ()) {
        
        \Natty::getDbo()->delete(self::TABLENAME, array (
            'key' => array ('biid' => $entity->biid)
        ));
        
    }
    
}