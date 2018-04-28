<?php

namespace Module\Cms\Logic;

class Backend_ContentController {
    
    public static function pageManage() {
        
        $auth_user = \Natty::getUser();
        $content_handler = \Natty::getHandler('cms--content');

        $list_head = array (
            array ('_data' => 'Title'),
            array ('_data' => 'Type', 'width' => 150),
            array ('_data' => '', 'class' => array ('context-menu')),
        );

        $stmt = $content_handler->getQuery();
        $list = new \Natty\Helper\PagingHelper($stmt);
        $list_data = $list->execute(array (
            'parameters' => array (
                'ail' => \Natty::getOutputLangId()
            ),
        ));

        $list_body = array ();
        foreach ( $list_data['items'] as $item ):

            $content = $content_handler->create($item);

            $row = array (
                '<div class="prop-title">' . $content->name . '</div>'
                . '<div class="prop-description">Url: cms/content/' . $item['cid'] . '</div>',
                $content->ctid,
                'context-menu' => array ()
            );

            $row['context-menu'] = $content->call('buildBackendLinks');

            $list_body[] = $row;
            unset ($item);

        endforeach;

        // Prepare output
        $output[]= array (
            '_render' => 'toolbar',
            '_right' => array (
                '<a href="' . \Natty::url('backend/cms/content/create') . '" class="k-button">Create</a>'
            )
        );
        $output[] = array (
            '_render' => 'table',
            '_head' => $list_head,
            '_body' => $list_body,
        );
        $output[] = array (
            '_render' => 'pager',
            '_data' => $list_data,
        );
        
        return $output;
        
    }
    
    public static function pageForm($mode, $cid, $ctype) {

        // Creation?
        if ( 'create' == $mode ) {
            $content = \Natty::getHandler('cms--content')->create(array (
                'ctid' => $ctype->ctid,
                'ail' => \Natty::getInputLangId(),
            ));
        }
        // Modification?
        else {

            $content = \Natty::getEntity('cms--content', $cid, array (
                'language' => \Natty::getInputLangId(),
            ));
            if ( !$content )
                \Natty::error(404);

            $ctype = \Natty::getEntity('cms--contenttype', $content->ctid);
            if ( !$ctype )
                \Natty::error(500);

        }

        // Bounce back URL
        $bounce_url = \Natty::getRequest()->getString('bounce');
        if ( !$bounce_url )
            $bounce_url = \Natty::url('backend/cms/content');

        // Build form
        $form = new \Natty\Form\FormObject(array (
            'id' => 'cms-content-form',
            'i18n' => TRUE,
        ), array (
            'etid' => 'cms--content',
            'egid' => $ctype->ctid,
            'entity' => &$content,
        ));
        $form->addListener('\\Module\\Eav\\Classes\\AttributeHandler::entityFormHandle');

        // Disable language selection during creation
        if ( 'create' == $mode )
            $form->items['default']['_data']['ilid']['disabled'] = 1;

        $form->items['default']['_data']['name'] = array (
            '_widget' => 'input',
            '_label' => 'Name',
            'required' => TRUE,
            '_default' => $content->name
        );
        $form->items['default']['_data']['status'] = array (
            '_widget' => 'options',
            '_label' => 'Status',
            '_options' => array (
                1 => 'Enabled',
                0 => 'Disabled',
            ),
            '_default' => $content->status,
            'class' => array ('options-inline'),
        );

        $form->actions['save'] = array (
            '_label' => 'Save',
            'type' => 'submit',
        );
        $form->actions['back'] = array (
            'type' => 'anchor',
            'href' => $bounce_url,
            '_label' => 'Back',
        );

        $form->onPrepare();

        // Validate form
        if ( $form->isSubmitted() ):

            $form->onValidate();

        endif;

        // Process form
        if ( $form->isValid() ):

            $form_data = $form->getValues();

            $content->setState($form_data);
            $content->save();

            \Natty\Console::success(NATTY_ACTION_SUCCEEDED);

            $form->redirect = $bounce_url;
            $form->onProcess();

        endif;

        // Prepare response
        $output = $form->getRarray();
        
        return $output;
        
    }
    
    public static function pageAction() {
        
        defined('NATTY') or die;

        $request = \Natty::getRequest();
        $user = \Natty::getUser();

        // Retrieve subjects
        if ( !$eids = $request->getVar('with') )
            return \Natty::error(400);
        if ( !is_array($eids) )
            $eids = array ($eids);

        // Retrieve entities
        $entities = \Natty::getEntity('cms--content', $eids);
        if ( !$entities || sizeof($eids) != sizeof($entities) )
            return \Natty::error(400);

        foreach ( $entities as $entity ):
            switch ( $request->getVar('do') ):
                case 'delete':
                    if ( $user->can('cms/manage content') )
                        $entity->delete();
                    break;
                default:
                    \Natty::error(403);
                    break;
            endswitch;
        endforeach;

        \Natty\Console::success();
        \Natty::getResponse()->bounce('backend/cms/content');
        
    }
    
}