<?php

namespace Module\Taxonomy\Logic;

class Backend_TermController {
    
    public static function pageManage($tgroup) {
        
        if ( !is_object($tgroup) )
            $tgroup = \Natty::getEntity('taxonomy--group', $tgroup);
        if ( !$tgroup )
            \Natty::error(400);
        
        // Load dependencies
        $term_handler = \Natty::getHandler('taxonomy--term');

        // List head
        $list_head = array (
            array ('_data' => 'OOA', 'class' => array ('system-ooa')),
            array ('_data' => 'Term'),
            array ('_data' => '', 'class' => array ('context-menu')),
        );

        // Read data
        $term_coll = $term_handler->read(array (
            'key' => array (
                'gid' => $tgroup->gid,
            ),
            'parameters' => array (
                'ail' => \Natty::getOutputLangId(),
            ),
        ));

        $form_submitted = isset ($_POST['save']);
        $form_errors = array ();

        // Validate form
        if ( $form_submitted ):

            $form_values = $_POST;

            foreach ( $term_coll as $term ):

                // Ignore if no POST data exists
                if ( !isset ($form_values['items'][$term->tid]) ):
                    $form_errors[] = $term->name . ': Data is missing.';
                    continue;
                endif;

                $form_item = $form_values['items'][$term->tid];

                // Validate data
                if ( !is_numeric($form_item['ooa']) || !is_numeric($form_item['parentId']) || !is_numeric($form_item['ooa']) ):
                    $form_errors[] = $term->name . ': Data is invalid.';
                    continue;
                endif;

                // Assign data
                $term->parentId = $form_item['parentId'];
                $term->level = $form_item['level'];
                $term->ooa = $form_item['ooa'];

            endforeach;

            // Show error messages
            if ( $form_errors ):
                \Natty\Console::error($form_errors);
                \Natty::getResponse()->refresh();
                // Sort items by ooa so that they can be rendered into a tree?
            endif;

        endif;

        // Sort items into a tree
        $term_coll = natty_sort_tree($term_coll, array (
            'idKey' => 'tid',
            'ooaKey' => 'ooa',
        ));

        // Process form
        if ( $form_submitted && 0 === sizeof($form_errors) ):

            // Save items in the order they were posted
            foreach ( $form_values['items'] as $form_item ):

                if ( !isset ($term_coll[$form_item['tid']]) )
                    continue;

                $term = $term_coll[$form_item['tid']];
                $term->save();

            endforeach;

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
            \Natty::getResponse()->refresh();

        endif;

        // List body
        $list_body = array ();
        foreach ( $term_coll as $tid => $term ):

            $row = array ();

            $row[] = array (
                '_data' => '<div class="form-item"><input type="number" value="' . $term->ooa . '" class="n-ta-ri" /></div>'
                    .'<input type="hidden" name="items[' . $tid . '][tid]" value="' . $term->tid . '" class="prop-id" />'
                    .'<input type="hidden" name="items[' . $tid . '][parentId]" value="' . $term->parentId . '" class="prop-parentId" />'
                    .'<input type="hidden" name="items[' . $tid . '][level]" value="' . $term->level . '" class="prop-level" />'
                    .'<input type="hidden" name="items[' . $tid . '][ooa]" value="' . $term->ooa . '" class="prop-ooa" />',
                'class' => array ('system-ooa'),
            );
            $row[] = str_repeat('<span class="n-indent"></span>', $term->level)
                    . '<div class="prop-title">' . $term->name . ($term->isLocked ? ' <i class="n-icon n-icon-lock"></i>' : '') . '</div>';

            $row['context-menu'] = $term->call('buildBackendLinks');

            $list_body[] = $row;

        endforeach;

        // Prepare response
        $output[] = array (
            '_render' => 'toolbar',
            '_right' => '<a href="' . \Natty::url('backend/taxonomy/' . $tgroup->gcode . '/terms/create') . '" class="k-button">Create</a>',
        );

        $output[] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
            '_form' => array (
                '_actions' => array (
                    '<input name="save" type="submit" value="Save" class="k-button k-primary" />'
                ),
            ),
            'class' => array ('n-table', 'n-table-striped', 'n-table-border-outer'),
            'data-ui-init' => array ('sortable'),
        );

        // Add the sortable plugin
        $response = \Natty::getResponse();
        $response->addScript(\Natty::path('core/plugin/natty/natty.sortable.js'));
        
        return $output;
        
    }
    
    public static function pageForm($mode = NULL, $term = NULL, $tgroup = NULL) {
        
        // Load dependencies
        $term_handler = \Natty::getHandler('taxonomy--term');

        // Creation
        if ( 'create' == $mode ) {

            $term = $term_handler->create(array (
                'gid' => $tgroup->gid,
            ));
            \Natty::getResponse()->attribute('title', $tgroup->name . ': Add term');

        }
        // Modification
        else {

            $tgroup = \Natty::getEntity('taxonomy--group', $term->gid);
            \Natty::getResponse()->attribute('title', $tgroup->name . ': Edit term');

        }

        // Read parent options
        $parent_opts = $term_handler->readOptions(array (
            'conditions' => array (
                array ('AND', array ('term.gid', '=', ':gid')),
                array ('AND', array ('term.tid', '!=', ':tid')),
                array ('AND', array ('term.level', '<', ':maxLevel'))
            ),
            'parameters' => array (
                'gid' => $tgroup->gid,
                'tid' => $term->tid ? $term->tid : '',
                'maxLevel' => $tgroup->maxLevels-1,
            ),
            'ordering' => array ('ooa' => 'asc'),
        ));

        // Prepare form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'taxonomy-term-form',
        ), array (
            'etid' => $term_handler->getEntityTypeId(),
            'egid' => $tgroup->gid,
            'entity' => &$term,
        ));
        $form->addListener('\\Module\\Eav\\Classes\\AttributeHandler::entityFormHandle');
        $form->items['basic'] = array (
            '_widget' => 'container',
            '_label' => 'Basic Info',
        );
        $form->items['basic']['_data']['name'] = array (
            '_label' => 'Name',
            '_widget' => 'input',
            '_default' => $term->name,
            'maxlength' => 128,
            'required' => TRUE,
        );
        $form->items['basic']['_data']['description'] = array (
            '_label' => 'Description',
            '_widget' => 'rte',
            '_default' => $term->description,
        );
        $form->items['basic']['_data']['parentId'] = array (
            '_label' => 'Parent Item',
            '_widget' => 'dropdown',
            '_options' => $parent_opts,
            '_default' => $term->parentId,
            'placeholder' => '',
        );


        $form->actions['save'] = array (
            '_label' => 'Save',
        );
        $form->actions['back'] = array (
            '_label' => 'Back',
            'type' => 'anchor',
            'href' => \Natty::url('backend/taxonomy/' . $tgroup->gcode . '/terms'),
        );
        
        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            $form_values = $form->getValues();
            $term->setState($form_values);
            $term->save();

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);

            $form->redirect = \Natty::url('backend/taxonomy/' . $tgroup->gcode . '/terms');
            $form->onProcess();

        endif;

        // Prepare response
        return $form->getRarray();
        
    }
    
    public static function actionDelete($term) {
        
        $term->delete();
        \Natty\Console::success(NATTY_ACTION_SUCCEEDED);
        
        \Natty::getResponse()->bounce();
        
    }
    
}