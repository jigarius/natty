<?php

namespace Module\Commerce\Logic;

class Backend_TaxRuleController {
    
    public static function pageManage($taxgroup) {
        
        // Load dependencies
        $taxrule_handler = \Natty::getHandler('commerce--taxrule');
        
        // List head
        $list_head = array (
            array ('_data' => '', 'class' => array ('system-ooa')),
            array ('_data' => 'Description'),
            array ('_data' => 'Applicable to'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // List data
        $query = $taxrule_handler->getQuery()
                ->addColumn('name country_name', 'country_i18n')
                ->addColumn('name tax_name', 'tax_i18n')
                ->addJoin('inner', '%__commerce_tax_i18n tax_i18n', array (
                    array ('AND', '{tax_i18n}.{tid} = {taxrule}.{tid}'),
                    array ('AND', '{tax_i18n}.{ail} = :ail'),
                ))
                ->addJoin('left', '%__location_country_i18n country_i18n', array (
                    array ('AND', '{country_i18n}.{cid} = {taxrule}.{idCountry}'),
                    array ('AND', '{country_i18n}.{ail} = :ail'),
                ));
        $list_data = $taxrule_handler->execute($query, array (
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
            ),
        ));
        
        // List body
        $list_body = array ();
        foreach ( $list_data as $taxrule ):
            
            $taxrule_applicability = $taxrule->country_name;
            
            $row = array ();
            $row[] = '<div class="form-item">'
                        . '<input type="number" name="items[' . $taxrule->trid . ']" value="' . $taxrule->ooa . '" class="prop-ooa" />'
                    . '</div>';
            $row[] = '<div class="prop-title">' . $taxrule->tax_name . '</div>'
                    . '<div class="prop-description">' . $taxrule->description . '</div>';
            $row[] = $taxrule_applicability;
            $row['context-menu'] = $taxrule->call('buildBackendLinks');
            
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare response
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                'create' => '<a href="' . \Natty::url('backend/commerce/tax-groups/' . $taxgroup->tgid . '/tax-rules/create') . '" class="k-button">Create</a>'
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
            '_form' => array (
                '_actions' => array (
                    'save' => '<input type="submit" name="submit" value="Save" class="k-button k-primary" />',
                ),
            ),
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $taxrule, $taxgroup) {
        
        // Load dependencies
        $tax_handler = \Natty::getHandler('commerce--tax');
        $taxrule_handler = \Natty::getHandler('commerce--taxrule');
        $country_handler = \Natty::getHandler('location--country');
        $response = \Natty::getResponse();
        
        // Create
        if ( 'create' === $mode ) {
            $taxrule = $taxrule_handler->create(array (
                'tgid' => $taxgroup->tgid,
            ));
            
            $max_ooa = $taxrule_handler->getDbo()
                    ->read('%__commerce_taxrule', array (
                        'columns' => array ('ooa'),
                        'key' => array ('tgid' => $taxgroup->tgid),
                        'ordering' => array (
                            'ooa' => 'asc',
                        ),
                        'unique' => 1,
                    ));
            $taxrule->ooa = $max_ooa ? $max_ooa['ooa'] : 5;
            
            $response->attribute('title', 'Create tax rule');
        }
        // Edit
        else {
            $response->attribute('title', 'Edit tax rule');
        }
        
        // Bounce url
        $bounce_url = \Natty::getCommand();
        $bounce_url = dirname($bounce_url);
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'commerce-taxrule-form',
        ), array (
            'etid' => 'commerce--taxrule',
            'entity' => &$taxrule,
        ));
        $form->items['default']['_data']['tgid'] = array (
            '_label' => 'Tax group',
            '_widget' => 'markup',
            '_markup' => $taxgroup->name,
            '_default' => $taxrule->tgid,
        );
        $form->items['default']['_data']['tid'] = array (
            '_label' => 'Tax to levy',
            '_widget' => 'dropdown',
            '_options' => $tax_handler->readOptions(),
            '_default' => $taxrule->tid,
        );
        $form->items['default']['_data']['behavior'] = array (
            '_label' => 'Behavior',
            '_widget' => 'options',
            '_options' => array (
                $taxrule_handler::BEHAVIOR_REPLACE => 'Replace: Ignore other taxes and apply this tax only.',
                $taxrule_handler::BEHAVIOR_COMBINE => 'Combine: Add tax rates and then apply. Example: 10% + 2% = 12%',
                $taxrule_handler::BEHAVIOR_SUCCEED => 'Succeed: Apply other taxes, then apply this tax. Example: Apply 10% then 2%.',
            ),
            '_default' => $taxrule->behavior,
        );
        $form->items['default']['_data']['idCountry'] = array (
            '_label' => 'Country applicable',
            '_widget' => 'dropdown',
            '_options' => $country_handler->readOptions(array (
                'key' => array ('status' => 1),
            )),
            '_default' => $taxrule->idCountry,
        );
        $form->items['default']['_data']['idState'] = array (
            '_widget' => 'markup',
            '_markup' => 'State options will be added later.',
            '_default' => $taxrule->idState,
        );
        $form->items['default']['_data']['description'] = array (
            '_widget' => 'input',
            '_description' => 'A small administrative note.',
            '_default' => $taxrule->description,
        );
        
        $form->actions['save'] = array (
            '_label' => 'Save',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => \Natty::url($bounce_url),
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form_data = $form->getValues();
        
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $taxrule->setState($form_data);
            $taxrule->save();
            
            \Natty\Console::success();
            $form->redirect = $bounce_url;
            
            $form->onProcess();
            
        endif;
        
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
    public static function pageAction() {
        
        natty_debug();
        
    }
    
}