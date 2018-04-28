<?php

namespace Module\Commerce\Classes;

class CarrierHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'commerce--carrier',
            'tableName' => '%__commerce_carrier',
            'modelName' => array ('carrier', 'carriers'),
            'keys' => array (
                'id' => 'cid',
            ),
            'properties' => array (
                'cid' => array (),
                'ctid' => array ('required' => 1),
                'name' => array ('isTranslatable' => 1),
                'description' => array ('isTranslatable' => 1),
                'ooa' => array ('default' => 0),
                'isFree' => array ('default' => 0),
                'settings' => array ('serialized' => 1, 'default' => array ()),
                'status' => array ('default' => 1),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function buildBackendLinks(&$entity, array $options = array()) {
        
        $user = \Natty::getUser();
        $output = array ();
        
        if ( $user->can('commerce--manage carrier entities') ):
            $output['configure'] = '<a href="' . \Natty::url('backend/commerce/carriers/' . $entity->cid . '/configure') . '">Configure</a>';
            $output['scope'] = '<a href="' . \Natty::url('backend/commerce/carriers/' . $entity->cid . '/scope') . '">Scope</a>';
            $output['edit'] = '<a href="' . \Natty::url('backend/commerce/carriers/' . $entity->cid) . '">Edit</a>';
            $output['delete'] = '<a href="' . \Natty::url('backend/commerce/carriers/' . $entity->cid . '/delete') . '" data-ui-init="confirmation">Delete</a>';
        endif;
        
        return $output + parent::buildBackendLinks($entity, $options);
        
    }
    
    /**
     * 
     * @param type $carrier
     * @param array $options An assoc of data required for the calculation.
     * Parameters include:<br />
     * idCountry: Destination Country ID<br />
     * idState: Destination State ID<br />
     * idRegion: Destination Region ID<br />
     * orderValue: Order amount<br />
     * orderWeight: Order weight<br />
     * orderVolume: Order volume<br />
     * @return Shipping charge as per carrier configuration.
     */
    public function computeCost($entity, array $options) {
        
        $options = array_merge(array (
            'idCountry' => NULL,
            'idState' => NULL,
            'idRegion' => NULL,
            'orderValue' => NULL,
            'orderWeight' => NULL,
            'orderVolume' => NULL,
        ), $options);
        
        // Read carrier type
        $ctype = \Module\Commerce\Classes\CarriertypeHandler::readById($entity->ctid);
        
        return call_user_func(array ($ctype->helper, 'computeCost'), $entity, $options);
        
    }
    
}