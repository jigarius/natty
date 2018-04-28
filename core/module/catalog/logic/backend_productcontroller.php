<?php

namespace Module\Catalog\Logic;

class Backend_ProductController {
    
    public static function pageManage() {
        
        // Load dependencies
        $product_handler = \Natty::getHandler('catalog--product');
        
        // List head
        $list_head = array (
            'pcode' => array ('_data' => 'Code', 'column' => 'product.pcode', 'width' => 150),
            'name' => array ('_data' => 'Name', 'column' => 'product.name'),
            'cid' => array ('_data' => 'Category', 'column' => 'cc_i18n.name'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );
        
        // Prepare list
        $query = $product_handler->getQuery()
                ->addColumn('name category_name', 'cc_i18n')
                ->addJoin('left', '%__taxonomy_term_i18n cc_i18n', array (
                    array ('AND', '{cc_i18n}.{tid} = {product}.{cid}'),
                    array ('AND', '{cc_i18n}.{ail} = {product_i18n}.{ail}'),
                ));
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $list_data = $paging_helper->execute(array (
            'parameters' => array ('ail' => \Natty::getOutputLangId()),
            'fetch' => array ('entity', 'catalog--product'),
        ));
        
        // List body
        $list_body = array ();
        foreach ( $list_data['items'] as $product ):
            
            $row = array ();
            $row[] = $product->pcode;
            $row[] = '<div class="prop-title">' . $product->name . '</div>';
            $row[] = $product->category_name;
            $row['context-menu'] = $product->call('buildBackendLinks');
            
            $list_body[] = $row;
            
        endforeach;
        
        // Prepare output
        $output = array ();
        $output['toolbar'] = array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/catalog/products/create') . '" class="k-button">Create</a>'
            ),
        );
        $output['table'] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        $output['pager'] = array (
            '_render' => 'pager',
            '_data' => $list_data,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode = NULL, $product = NULL) {
        
        // Load dependencies
        $product_handler = \Natty::getHandler('catalog--product');
        $ptype_handler = \Natty::getHandler('catalog--producttype');
        $tterm_handler = \Natty::getHandler('taxonomy--term');
        $response = \Natty::getResponse();
        
        // Creation?
        if ( 'create' == $mode ) {
            $product = $product_handler->create();
            $response->attribute('title', 'Create product');
        }
        else {
            $response->attribute('title', 'Edit product');
        }
        
        // Read category associations
        $product->call('getEntityCategoryIds');
        
        $bounce_url = \Natty::url('backend/catalog/products');
        if ( isset ($_REQUEST['bounce']) )
            $bounce_url = $_REQUEST['bounce'];
        
        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'catalog-product-form',
            '_tabs' => 1,
        ), array (
            'etid' => $product_handler->getEntityTypeId(),
            'egid' => $product->ptid,
            'entity' => &$product,
        ));
        
        $form->items['default']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            '_default' => $product->name,
            'required' => 1,
        );
        $form->items['default']['_data']['pcode'] = array (
            '_label' => 'Code',
            '_widget' => 'input',
            '_default' => $product->pcode,
        );
        
        // Product type
        $opts_ptype = $ptype_handler->readOptions(array (
            'key' => array ('status' => 1),
        ));
        $form->items['default']['_data']['ptid'] = array (
            '_label' => 'Type',
            '_widget' => 'dropdown',
            '_options' => $opts_ptype,
            '_default' => $product->ptid,
        );
        
        $form->items['default']['_data']['status'] = array (
            '_label' => 'Status',
            '_widget' => 'options',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            '_default' => $product->status,
            'class' => array ('options-inline'),
        );
        
        // Place for attributes
        $form->items['eav'] = array (
            '_widget' => 'container',
        );
        
        // Associations
        $form->items['associations'] = array (
            '_widget' => 'container',
            '_label' => 'Associations',
            '_data' => array (),
        );
        
        $opts_categories = $tterm_handler->readOptions(array (
            'key' => array (
                'gcode' => 'catalog-categories',
                'status' => 1,
            ),
        ));
        $form->items['associations']['_data']['categoryIds'] = array (
            '_label' => 'Categories',
            '_widget' => 'options',
            '_options' => $opts_categories,
            '_default' => $product->categoryIds,
            'multiple' => 1,
        );
        $form->items['associations']['_data']['cid'] = array (
            '_label' => 'Default category',
            '_widget' => 'dropdown',
            '_options' => $opts_categories,
            '_default' => $product->cid,
            'placeholder' => '',
            'required' => 1,
        );
        
        // Pricing
        $form->items['pricing'] = array (
            '_label' => 'Pricing',
            '_widget' => 'container',
            '_data' => array (),
        );
        $form->items['pricing']['_data']['costPrice'] = array (
            '_label' => 'Cost Price',
            '_widget' => 'input',
            '_description' => 'Price at which you bought the product.',
            '_prefix' => \Natty::readSetting('system--currency'),
            '_default' => $product->costPrice,
            'type' => 'number',
            'step' => '.001',
            'class' => array ('widget-small'),
        );
        $form->items['pricing']['_data']['salePrice'] = array (
            '_label' => 'Selling Price',
            '_widget' => 'input',
            '_description' => 'Selling price excluding taxes.',
            '_prefix' => \Natty::readSetting('system--currency'),
            '_default' => $product->salePrice,
            'type' => 'number',
            'step' => '.001',
            'class' => array ('widget-small'),
        );
        
        // Shipping
        $form->items['shipping'] = array (
            '_label' => 'Shipping',
            '_widget' => 'container',
            '_data' => array (),
        );
        $form->items['shipping']['_data']['length'] = array (
            '_label' => 'Package length',
            '_widget' => 'input',
            '_suffix' => 'cm',
            '_default' => $product->length,
            'type' => 'number',
            'step' => '.001',
            'class' => array ('widget-small'),
        );
        $form->items['shipping']['_data']['breadth'] = array (
            '_label' => 'Package breadth',
            '_widget' => 'input',
            '_suffix' => 'cm',
            '_default' => $product->breadth,
            'type' => 'number',
            'step' => '.001',
            'class' => array ('widget-small'),
        );
        $form->items['shipping']['_data']['height'] = array (
            '_label' => 'Package height',
            '_widget' => 'input',
            '_suffix' => 'cm',
            '_default' => $product->height,
            'type' => 'number',
            'step' => '.001',
            'class' => array ('widget-small'),
        );
        $form->items['shipping']['_data']['weight'] = array (
            '_label' => 'Package weight',
            '_widget' => 'input',
            '_suffix' => 'kg',
            '_default' => $product->weight,
            'type' => 'number',
            'step' => '.001',
            'class' => array ('widget-small'),
        );
        $form->items['shipping']['_data']['shippingCharge'] = array (
            '_label' => 'Shipping charge',
            '_widget' => 'input',
            '_prefix' => \Natty::readSetting('system--currency'),
            '_default' => $product->shippingCharge,
            'type' => 'number',
            'step' => '.001',
            'class' => array ('widget-small'),
        );
        
        // Bind attribute form if product type is selected
        if ( $product->ptid ):
            
            $form->items['default']['_data']['ptid']['_ignore'] = 1;
            $form->items['default']['_data']['ptid']['readonly'] = 1;
            
            $form->addListener('\\Module\\Eav\\Classes\\AttributeHandler::entityFormHandle');
            
        endif;
        
        $form->actions['stay'] = array (
            '_label' => 'Save & stay',
            'type' => 'submit',
        );
        $form->actions['save'] = array (
            '_label' => 'Save & leave',
            'type' => 'submit',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => $bounce_url,
        );
        
        $form->scripts[] = array (
            'src' => NATTY_BASE . \Natty::packagePath('module', 'catalog') . '/reso/backend.js',
        );
        
        $form->onPrepare();
        
        // Validate form
        if ( $form->isSubmitted() ):
            
            $form_values = $form->getValues();
            
            if ( !in_array($form_values['cid'], $form_values['categoryIds']) ):
                $form->items['associations']['_data']['cid']['_errors'][] = 'Default category must be one of selected categories.';
                $form->isValid(FALSE);
            endif;
        
            $form->onValidate();
            
        endif;
        
        // Process form
        if ( $form->isValid() ):
            
            $product->setState($form_values);
            $product->save();
            
            // Save Category IDs
            $product->call('setEntityCategoryIds', $form_values['categoryIds']);
            
            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            
            $form->redirect = \Natty::url('backend/catalog/products');
            
            // Request to stay?
            if ( $form->isSubmitted('stay') )
                $form->redirect = \Natty::url('backend/catalog/products/' . $product->pid);
            
            $form->onProcess();
            
        endif;
        
        // Prepare output
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
}