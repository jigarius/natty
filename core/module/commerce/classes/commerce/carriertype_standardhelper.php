<?php

namespace Module\Commerce\Classes\Commerce;

class Carriertype_StandardHelper
extends \Module\Commerce\Classes\CarrierTypeHelperAbstract {
    
    public static function getDefaultSettings() {
        return array (
            'basisOfCharge' => 'weight',
            'oorBehavior' => 'disable',
        );
    }
    
    public static function computeCost($carrier, array $options) {
        
        // Determine the value which will be looked up in the rate charge.
        // Value of parameter based on which cost will be computed.
        $boc = $carrier->settings['basisOfCharge'];
        $boc_key = 'order' . ucfirst($boc);
        $lookup_value = $options[$boc_key];
        
        // Lookup charge from the rate chart (if any)
        $dbo = \Natty::getDbo();
        $stmt = $dbo->getQuery('select', '%__commerce_carrier_standard cs')
                ->addColumn('cs.*')
                ->addSimpleCondition('cid', ':cid')
                ->addSimpleCondition('basisOfCharge', ':boc')
                ->addComplexCondition('AND', array (
                    array ('OR', '{cs}.{idCountry} = :idCountry AND {cs}.{idState} = 0 AND {cs}.{idRegion} = 0'),
                    array ('OR', '{cs}.{idState} = :idState AND {cs}.{idRegion} = 0'),
                    array ('OR', '{cs}.{idRegion} = :idRegion'),
                ))
                ->orderBy('idRegion', 'desc')
                ->orderBy('idState', 'desc')
                ->orderBy('idCountry', 'desc')
                ->orderBy('till', 'asc')
                ->execute(array (
                    'cid' => $carrier->cid,
                    'boc' => $boc,
                    'idCountry' => $options['idCountry'],
                    'idState' => $options['idState'],
                    'idRegion' => $options['idRegion'],
                ));
        
        // Re-arrange slab data logically
        $slab_data = array ();
        $slab_fallback = array ();
        while ($slab = $stmt->fetch()):
            
            // We need only the data for the slab closest to the lookup value
            // at each level
            $slab_key = $slab['idCountry'] . ':' . $slab['idState'] . ':' . $slab['idRegion'];
            if (!isset ($slab_data[$slab_key]) && $slab['till'] >= $lookup_value)
                $slab_data[$slab_key] = $slab;
            
            // Detect a fallback slab - a higher slab than lookup value
            $slab_fallback[$slab_key] = $slab;
            
        endwhile;
        
        $output_amount = FALSE;
        
        // Get cost as per closest matching slab
        if ( sizeof($slab_data) > 0 ) {
            $slab_key = each($slab_data);
            $slab_key = $slab_key['key'];
            $slab = $slab_data[$slab_key];
            $output_amount = $slab_data[$slab_key]['amount'];
        }
        // Get cost for the highest slab (if allowed)
        elseif (sizeof($slab_fallback) > 0 || TRUE || 'disable' !== $carrier->settings['oorBehavior']) {
            $slab_key = each($slab_fallback);
            $slab_key = $slab_key['key'];
            $slab = $slab_fallback[$slab_key];
            $slab['isFallback'] = TRUE;
            $output_amount = $slab_fallback[$slab_key]['amount'];
        }
        
//        $debug_data = array (
//            'Lookup data' => print_r($options, 1),
//            'Output slab' => print_r($slab, 1),
//            'Carrier settings' => print_r($carrier->settings, 1),
//        );
//        \Natty\Console::debug($debug_data);
        
        return $output_amount;
        
    }
    
}