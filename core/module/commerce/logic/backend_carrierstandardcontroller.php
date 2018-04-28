<?php

namespace Module\Commerce\Logic;

use \Module\Commerce\Classes\Commerce\Carriertype_StandardHelper as CarrierTypeHelper;
use \Natty\Helper\FileHelper as FileHelper;

class Backend_CarrierStandardController {
    
    public static function pageConfigure($carrier) {
        
        // Download rate chart?
        if ( isset ($_REQUEST['rate-chart']) )
            self::_pageRateChart();
        
        // Prepare settings
        $default_settings = CarrierTypeHelper::getDefaultSettings();
        $carrier->settings = array_merge($default_settings, $carrier->settings);
        
        // Build a form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'commerce-carrier-standard-form',
            'enctype' => 'multipart/form-data',
        ));
        $form->items['default']['_data']['basisOfCharge'] = array (
            '_label' => 'Basis of charge',
            '_widget' => 'dropdown',
            '_options' => array (
                'weight' => 'Order weight',
                'value' => 'Order value',
            ),
            '_default' => $carrier->settings['basisOfCharge'],
        );
        $form->items['default']['_data']['oorBehavior'] = array (
            '_label' => 'When out of range',
            '_widget' => 'options',
            '_options' => array (
                'disable' => 'Disable carrier',
                'highest' => 'Use highest charge',
            ),
            '_default' => $carrier->settings['oorBehavior'],
            'class' => array ('options-inline'),
        );
        $form->items['default']['_data']['file'] = array (
            '_label' => 'Rate chart',
            '_description' => 'Download <a href="' . \Natty::url(\Natty::getCommand(), array (
                'rate-chart' => 1,
            )) . '" target="_blank">sample rate chart</a>, make your changes and upload them back.',
            '_widget' => 'upload',
            '_extensions' => array ('csv'),
        );
        
        $form->actions['save'] = array (
            '_label' => 'Upload',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => \Natty::url('backend/commerce/carriers'),
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            // Update record
            $form_data = $form->getValues();
            $carrier->settings = $form_data;
            $carrier->save();
        
            // Process upload
            $upload = FileHelper::readUpload('file');
            if ( $upload ):
                
                if ( !$fp = fopen($upload['tmp_name'], 'r') )
                    natty_debug();

                // Start transaction
                $dbo = \Natty::getDbo();
                $dbo->beginTransaction();
                $dbo->delete('%__commerce_carrier_standard', array (
                    'key' => array (
                        'cid' => $carrier->cid,
                    ),
                ));

                $default_record = array (
                    'cid' => NULL,
                    'idCountry' => 0,
                    'idState' => 0,
                    'idRegion' => 0,
                    'basisOfCharge' => $form_data['basisOfCharge'],
                    'till' => 0,
                    'amount' => 0,
                );
                $range_data = array ();

                // Prepare statement for insertion
                $stmt = $dbo->getQuery('insert', '%__commerce_carrier_standard')
                        ->addColumns(array_keys($default_record))
                        ->prepare();

                $line_no = 0;
                while ($line = fgetcsv($fp)):

                    if ( !$line_no++ ):
                        $line_keys = $line;
                        foreach ( $line_keys as &$column ):
                            switch ($column):
                                case 'cid':
                                    $column = 'idCountry';
                                    break;
                                case 'sid':
                                    $column = 'idState';
                                    break;
                                case 'rid':
                                    $column = 'idRegion';
                                    break;
                                default:
                                    if ( in_array($column, array ('country', 'state', 'region')) )
                                        break;
                                    if ( !array_key_exists($column, $default_record) )
                                        $range_data[$column] = $column;
                                    break;
                            endswitch;
                            unset ($column);
                        endforeach;
                        continue;
                    endif;

                    // Interpret record
                    $line = array_combine($line_keys, $line);

                    // Show a message
                    if (!$line['idCountry'] && !$line['idState'] && !$line['idRegion']):
                        \Natty\Console::error('Line ' . $line_no . ': Ignored because data is invalid.');
                        continue;
                    endif;

                    // Prepare and save record
                    $record = natty_array_merge_intersection($default_record, $line);
                    $record['cid'] = $carrier->cid;
                    foreach ($range_data as $range):
                        $record['till'] = $range;
                        $record['amount'] = $line[$range];
                        if ( 0 === strlen($record['till']) )
                            continue;
                        $stmt->execute($record);
                    endforeach;

                endwhile;

                // End transaction
                $dbo->commit();
                
            endif;
            
            \Natty\Console::success();
            
            $form->onProcess();
            
        endif;
        
        // Prepare response
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
    public static function _pageRateChart() {
        
        $dbo = \Natty::getDbo();
        $response = \Natty::getResponse();
        
        // Read enabled countries
        $c_recs = $dbo->getQuery('select', '%__location_country c')
                ->addColumns(array ('cid', 'name'), 'c18')
                ->addJoin('inner', '%__location_country_i18n c18', [
                    ['AND', '{c18}.{cid} = {c}.{cid} AND {c18}.{ail} = :ail']
                ])
                ->addSimpleCondition('c.status', 1)
                ->orderBy('c18.name')
                ->execute(array (
                    'ail' => \Natty::getOutputLangId(),
                ))
                ->fetchAll(\PDO::FETCH_ASSOC);
        $c_data = array ();
        foreach ( $c_recs as $record ):
            $c_data[$record['cid']] = $record;
        endforeach;
        unset ($c_recs, $record);
        
        // Read enabled states
        $s_recs = $dbo->getQuery('select', '%__location_state s')
                ->addColumns(array ('sid', 'cid'), 's')
                ->addColumn('name', 's18')
                ->addJoin('inner', '%__location_state_i18n s18', [
                    ['AND', '{s18}.{sid} = {s}.{sid} AND {s18}.{ail} = :ail']
                ])
                ->addSimpleCondition('s.cid', array_keys($c_data), 'IN')
                ->addSimpleCondition('s.status', 1)
                ->orderBy('s18.name')
                ->execute(array (
                    'ail' => \Natty::getOutputLangId(),
                ))
                ->fetchAll(\PDO::FETCH_ASSOC);
        $s_data = array ();
        foreach ( $s_recs as $record ):
            $cid = $record['cid'];
            if ( !isset ($s_data[$cid]) )
                $s_data[$cid] = array ();
            $s_data[$cid][$record['sid']] = $record;
        endforeach;
        unset ($s_recs, $record);
        
        // Read enabled states
        $r_recs = $dbo->getQuery('select', '%__location_region r')
                ->addColumns(array ('rid', 'sid'), 'r')
                ->addColumn('name', 'r18')
                ->addJoin('inner', '%__location_region_i18n r18', [
                    ['AND', '{r18}.{rid} = {r}.{rid} AND {r18}.{ail} = :ail']
                ])
                ->addSimpleCondition('r.status', 1)
                ->orderBy('r18.name')
                ->execute(array (
                    'ail' => \Natty::getOutputLangId(),
                ))
                ->fetchAll(\PDO::FETCH_ASSOC);
        $r_data = array ();
        foreach ( $r_recs as $record ):
            $sid = $record['sid'];
            if ( !isset ($r_data[$sid]) )
                $r_data[$sid] = array ();
            $r_data[$sid][$record['rid']] = $record;
        endforeach;
        
        // Generate CSV
        $list_head = array (
            array ('_data' => 'cid'),
            array ('_data' => 'sid'),
            array ('_data' => 'rid'),
            array ('_data' => 'country'),
            array ('_data' => 'state'),
            array ('_data' => 'region'),
            array ('_data' => '<= x'),
            array ('_data' => '<= y'),
            array ('_data' => '<= z'),
        );
        $list_body = array ();
        foreach ($c_data as $cid => $country):
            
            $list_body[] = array (
                'cid' => $country['cid'],
                'sid' => 0,
                'rid' => 0,
                'country' => $country['name'],
                'state' => '',
                'region' => '',
                'r1' => '',
                'r2' => '',
                'r3' => '',
            );
            
            if ( !isset ($s_data[$cid]) )
                continue;
            foreach ( $s_data[$cid] as $sid => $state ):
                
                $list_body[] = array (
                    'cid' => $country['cid'],
                    'sid' => $state['sid'],
                    'rid' => 0,
                    'country' => $country['name'],
                    'state' => $state['name'],
                    'region' => '',
                    'r1' => '',
                    'r2' => '',
                    'r3' => '',
                );
                
                if ( !isset ($r_data[$sid]) )
                    continue;
                foreach ( $r_data[$sid] as $rid => $region ):
                    
                    $list_body[] = array (
                        'cid' => $country['cid'],
                        'sid' => $state['sid'],
                        'rid' => $region['rid'],
                        'country' => $country['name'],
                        'state' => $state['name'],
                        'region' => $region['name'],
                        'r1' => '',
                        'r2' => '',
                        'r3' => '',
                    );
                    
                endforeach;
            
            endforeach;
        
        endforeach;
        
        // Render CSV
        $response->header('Content-Type:text/csv');
        $response->header('Content-Disposition:attachment;filename="shipping-rate-chart.csv"');
        echo natty_render_csv(array (
            '_head' => $list_head,
            '_body' => $list_body,
            'border' => 1
        ));
        exit;
        
    }
    
}