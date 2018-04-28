<?php

namespace Module\Commerce\Classes;

class CheckoutHelper {
    
    public static function initCheckoutData($rebuild = FALSE) {
        
        $key = 'user.checkout';
        $checkout_data =& $_SESSION[$key];
        
        static $init_done;
        
        if (!$init_done || $rebuild):
            
            $init_done = TRUE;
            
            if (!is_array($checkout_data))
                $checkout_data = array ();
            $auth_user = \Natty::getUser();

            // Prefill default values
            $checkout_data = array_merge(array (
                'bes' => 1,
                // Billing and shipping data
                'shippingName' => $auth_user->name,
                'idShippingAddress' => NULL,
                'billingName' => NULL,
                'idBillingAddress' => NULL,
                // Location data for shipping estimation
                'destination' => array (
                    'idCountry' => NULL,
                    'idState' => NULL,
                    'idRegion' => NULL,
                ),
                'idCarrier' => NULL,
            ), $checkout_data);

            // Attempt to read user location
            if ( is_null($checkout_data['idShippingAddress']) ):

                // Read location data from previous order, if user is signed in
                if ( $auth_user->uid > 0 ) {

                    $dbo = \Natty::getDbo();

                    // Read previous order data (if any)
                    $prev_order = $dbo->read('%__commerce_order', array (
                        'columns' => array ('idShippingAddress', 'idBillingAddress'),
                        'key' => array ('idCreator' => $auth_user->uid),
                        'ordering' => array ('dtCreated' => 'DESC'),
                        'limit' => 1,
                        'unique' => 1,
                    ));

                    if ( $prev_order ) {
                        $checkout_data['idShippingAddress'] = $prev_order['idShippingAddress'];
                        $checkout_data['idBillingAddress'] = $prev_order['idBillingAddress'];
                        $checkout_data['bes'] = (int) ($checkout_data['idShippingAddress'] == $prev_order['idBillingAddress']);
                    }
                    else {
                        $checkout_data['idShippingAddress'] = FALSE;
                        $checkout_data['idBillingAddress'] = FALSE;
                    }

                }
                // Read country, state and region fallbacks
                else {

                    if (is_null($checkout_data['destination']['idCountry'])):

                        $country_coll = \Natty::getHandler('location--country')->readByKeys(array (
                            'status' => 1,
                        ));
                        if ( 1 === sizeof($country_coll) ) {
                            $country = array_pop($country_coll);
                            $checkout_data['destination']['idCountry'] = $country->cid;
                        }
                        else {
                            $checkout_data['destination']['idCountry'] = FALSE;
                        }

                    endif;
                    $cid = $checkout_data['destination']['idCountry'];

                    if ($cid && is_null($checkout_data['destination']['idState'])):

                        $state_coll = \Natty::getHandler('location--state')->readByKeys(array (
                            'cid' => $cid,
                            'status' => 1,
                        ));
                        if ( 1 === sizeof($state_coll) ) {
                            $state = array_pop($state_coll);
                            $checkout_data['destination']['idState'] = $state->sid;
                        }
                        else {
                            $checkout_data['destination']['idState'] = FALSE;
                        }

                    endif;
                    $sid = $checkout_data['destination']['idState'];

                    if ($sid && is_null($checkout_data['destination']['rid'])):

                        $region_coll = \Natty::getHandler('location--region')->readByKeys(array (
                            'sid' => $state->sid,
                            'status' => 1,
                        ));
                        if ( 1 === sizeof($region_coll) ) {
                            $region = array_pop($region_coll);
                            $checkout_data['destination']['idRegion'] = $state->rid;
                        }
                        else {
                            $checkout_data['destination']['idRegion'] = FALSE;
                        }

                    endif;

                }

            endif;

            // Determine default carrier for the destination
            if (!$checkout_data['idCarrier']):

                $destination = $checkout_data['destination'];
                if ($checkout_data['idShippingAddress']):
                    $uaddress = \Natty::getEntity('location--useraddress', $checkout_data['idShippingAddress']);
                    $destination['idCountry'] = $uaddress->cid;
                    $destination['idState'] = $uaddress->sid;
                    $destination['idRegion'] = $uaddress->rid;
                endif;
                $carrier_coll = self::readCarrierOptions($destination);
                
                if (sizeof($carrier_coll) > 0):
                    $record = each($carrier_coll);
                    $checkout_data['idCarrier'] = $record['key'];
                endif;

            endif;
            
        endif;
        
        return $key;
        
    }
    
    /**
     * Works just like getting "destination" data from checkout data. However,
     * this method also returns a user-readable name for the destination.
     * @param boolean $rebuild If false, cache would be ignored.
     * @return array An array containing shipment destination data.
     */
    public static function getShipmentDestination($rebuild = FALSE) {
        
        $cache = natty_cache(__METHOD__);
        
        if (is_null($cache) || !$rebuild):
            
            $key = self::initCheckoutData();
            $checkout_data = $_SESSION[$key];

            $output = $checkout_data['destination'];
            if ( $checkout_data['idShippingAddress'] ) {
                $uaddress = \Natty::getEntity('location--useraddress', $checkout_data['idShippingAddress']);
                if ( $uaddress ) {
                    $output['idAddress'] = $uaddress->aid;
                    $output['name'] = $uaddress->name;
                    $output['idCountry'] = $uaddress->cid;
                    $output['idState'] = $uaddress->sid;
                    $output['idRegion'] = $uaddress->rid;
                }
            }
            else {
                
                $output = $checkout_data['destination'];
                $name_parts = \Natty::getDbo()->getQuery('select', '%__location_country c')
                        ->addColumn('r18.name regionName')
                        ->addColumn('s18.name stateName')
                        ->addColumn('c18.name countryName')
                        ->addJoin('left', '%__location_country_i18n c18', '{c18}.{cid} = :cid AND {c18}.{ail} = :ail')
                        ->addJoin('left', '%__location_state_i18n s18', '{s18}.{sid} = :sid AND {s18}.{ail} = :ail')
                        ->addJoin('left', '%__location_region_i18n r18', '{r18}.{rid} = :rid AND {r18}.{ail} = :ail')
                        ->limit(1)
                        ->execute(array (
                            'cid' => $output['idCountry'],
                            'sid' => $output['idState'],
                            'rid' => $output['idRegion'],
                            'ail' => \Natty::getOutputLangId(),
                        ))
                        ->fetch();
                
                foreach ($name_parts as $key => $part):
                    if ( !$part )
                        unset ($name_parts[$key]);
                endforeach;
                
                $output['idAddress'] = FALSE;
                $output['name'] = implode(', ', $name_parts);
                
            }
            
            $cache = natty_cache(__METHOD__, $output, TRUE);
            
        endif;
        
        return $cache;
        
    }
    
    public static function readUserCartData($recompute = FALSE) {
        
        $cache = natty_cache(__METHOD__);
        
        if ( is_null($cache) || $recompute ):
            
            $cartitem_handler = \Natty::getHandler('commerce--cartitem');
            $carrier_handler = \Natty::getHandler('commerce--carrier');
            $auth_user = \Natty::getUser();
            $output = array (
                'items' => array (),
                'totalWeight' => 0,
                'amountProduct' => 0,
                'amountDiscount' => 0,
                'amountShipping' => 0,
                'amountTax' => 0,
                'amountFinal' => 0,
            );

            // Read checkout data
            $key = self::initCheckoutData();
            $checkout_data =& $_SESSION[$key];

            // Read items in the cart
            $query = $cartitem_handler->getQuery();
            $parameters = array ();
            if ( $auth_user->uid > 0 ) {
                $query->addSimpleCondition('idCreator', ':idCreator');
                $parameters['idCreator'] = $auth_user->uid;
            }
            else {
                $query->addSimpleCondition('idSession', ':idSession');
                $parameters['idSession'] = session_id();
            }
            $output['items'] = $cartitem_handler->execute($query, array (
                'parameters' => $parameters,
            ));

            foreach ($output['items'] as $cartitem):

                $output['totalWeight'] += $cartitem->totalWeight;
            
                $output['amountProduct'] += $cartitem->amountProduct;
                $output['amountShipping'] += $cartitem->amountShipping;
                $output['amountDiscount'] += $cartitem->amountDiscount;
                $output['amountTax'] += $cartitem->amountTax;
                $output['amountFinal'] += $cartitem->amountFinal;

            endforeach;
            
            // Add shipping carrier cost
            if (sizeof($output['items']) > 0 && $checkout_data['idCarrier']):
                
                $carrier = $carrier_handler->readById($checkout_data['idCarrier']);
                $carrier_inputs = self::getShipmentDestination();
                $carrier_inputs['orderValue'] = $output['amountProduct'];
                $carrier_inputs['orderWeight'] = $output['totalWeight'];
                
                $amount_shipping = $carrier_handler->computeCost($carrier, $carrier_inputs);
                $output['amountShipping'] += $amount_shipping;
                $output['amountFinal'] += $amount_shipping;
                
            endif;
            
            $cache = $output;
            
        endif;
        
        return $cache;
        
    }
    
    /**
     * Returns available shipping carriers for a specified destination.
     * @param array $destination Destination must be an array containing:<br />
     * idCountry: Country ID
     * idState: State ID
     * idRegion: Region ID
     * @return array A collection of available carriers.
     */
    public static function readCarrierOptions(array $destination) {
        
        $dbo = \Natty::getDbo();
        $carrier_handler = \Natty::getHandler('commerce--carrier');
        
        // Read carriers available for the guessed location
        $cid_coll = $dbo->read('%__commerce_carrier_scope cs', array (
            'columns' => array ('{cs}.{cid}'),
            'conditions' => array (
                array ('AND', '{idCountry} = :cid AND {idState} = 0 AND {idRegion} = 0'),
                array ('OR', '{idCountry} = :cid AND {idState} = :sid AND {idRegion} = 0'),
                array ('OR', '{idCountry} = :cid AND {idState} = :sid AND {idRegion} = :rid'),
            ),
            'ordering' => array (
                'idRegion' => 'desc',
                'idState' => 'desc',
                'idCountry' => 'desc',
            ),
            'parameters' => array (
                'cid' => $destination['idCountry'],
                'sid' => $destination['idState'],
                'rid' => $destination['idRegion'],
            ),
            'fetch' => array (\PDO::FETCH_COLUMN),
        ));
        $cid_coll = array_unique($cid_coll);
        
        // Load carrier data
        return $carrier_handler->readById($cid_coll);
        
    }
    
}