<?php

namespace Module\Catalog\Logic;

class Frontend_ProductController {
    
    public static function pageBrowse($term = NULL) {
        
        // Load dependencies
        $product_handler = \Natty::getHandler('catalog--product');
        $term_handler = \Natty::getHandler('taxonomy--term');
        
        // List data
        $query = $product_handler->getQuery();
        
        // Add filter
        if ( $term ):
            switch ( $term->gcode ):
                case 'catalog-categories':
                    $query->addFlag('distinct');
                    $query->addJoin('inner', '%__catalog_product_category_map pcm', array (
                        array ('AND', '{pcm}.{pid} = {product}.{pid}'),
                        array ('AND', '{pcm}.{cid} = "' . $term->tid . '"'),
                    ));
                    break;
            endswitch;
        endif;
        
        $paging_helper = new \Natty\Helper\PagingHelper($query);
        $list_data = $paging_helper->execute(array (
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
            ),
            'fetch' => array ('entity', 'catalog--product'),
        ));
        
        // If we are on the first page, show sub-categories
        $list_params = $paging_helper->getParameters();
        $show_categories = TRUE;

        // If a collection is specified
        if ( $term && 'catalog-collections' === $term->gcode ):
            \Natty\Console::message('Filter by collection');
            $show_categories = FALSE;
        endif;

        // If a category is specified
        if ( $term && 'catalog-categories' === $term->gcode ):
            
        endif;
        
        // Display categories?
        $category_list = array ();
        if (0 === $list_params['si']['_value'] && $show_categories):
            
            $category_coll = $term_handler->read(array (
                'key' => array (
                    'gcode' => 'catalog-categories',
                    'parentId' => ($term ? $term->tid : 0),
                    'status' => 1,
                ),
                'ordering' => array (
                    'ooa' => 'asc',
                ),
            ));
            foreach ( $category_coll as $category ):
                $category_list[] = $category->render(array (
                    'viewMode' => 'preview',
                ));
            endforeach;
            
        endif;
        
        // List body
        $product_list = array ();
        foreach ( $list_data['items'] as $product ):
            $product_list[] = $product->render(array (
                'viewMode' => 'preview',
            ));
        endforeach;
        
        // Prepare output
        $output = array ();
        $output['category_list'] = array (
            '_render' => 'list',
            '_element' => 'div',
            '_items' => $category_list,
            '_display' => sizeof($category_list),
            '_template' => array (
                'module/catalog/tmpl/category.tmpl.php',
            ),
            'class' => array ('n-list', 'catalog-category-list'),
        );
        $output['product_list'] = array (
            '_render' => 'list',
            '_element' => 'div',
            '_items' => $product_list,
            'class' => array ('n-list', 'catalog-product-list'),
        );
        if ( 0 === sizeof($category_list) && 0 === sizeof($product_list) ):
            $output[] = array (
                '_render' => 'element',
                '_element' => 'div',
                '_data' => 'There are no products to display here. If you have added any filters, please try removing them.',
                'class' => array ('n-emptytext'),
            );
        endif;
        $output['pager'] = array (
            '_render' => 'pager',
            '_data' => $list_data,
        );
        return $output;
        
    }
    
    public static function pageView($product) {
        
        // Build order form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'catalog-product-view-form',
        ), array (
            'mode' => 'view',
            'entity' => $product,
        ));
        $form->items['idProduct'] = array (
            '_widget' => 'input',
            '_default' => $product->pid,
            'type' => 'hidden',
        );
        $form->items['rate'] = array (
            '_label' => 'Rate',
            '_widget' => 'markup',
            '_markup' => natty_format_money($product->salePrice, array (
                'symbol' => FALSE,
            )),
            '_ignore' => 1,
            '_ooa' => 999,
        );
        $form->onPrepare();
        
        if ( $form->isSubmitted() ):
            $form->onValidate();
        endif;
        
        if ( $form->isValid() ):
            $form->onProcess();
        endif;
        
        // Build output
        $output = array (
            '_render' => 'entity',
            '_entity' => $product,
            '_options' => array (
                'page' => TRUE,
                'variables' => array (
                    'form' => $form->getRarray(),
                ),
            ),
        );
        
        return $output;
        
    }
    
}