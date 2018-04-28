<?php

namespace Module\System\Classes;

class EmailHandler
extends \Natty\ORM\I18nDatabaseEntityHandler {
    
    public function __construct(array $options = array()) {
        
        $options = array (
            'etid' => 'system--email',
            'modelName' => array ('email', 'emails'),
            'entityObjectClass' => '\\Module\\System\\Classes\\EmailObject',
            'tableName' => '%__system_email',
            'keys' => array (
                'id' => 'eid',
            ),
            'properties' => array (
                'eid' => array (),
                'name' => array ('isTranslatable' => 1),
                'subject' => array ('isTranslatable' => 1),
            ),
        );
        
        parent::__construct($options);
        
    }
    
    public function rebuild() {
        
        $old_coll = $this->read(array (
            'conditions' => '1=1',
            'parameters' => array (
                'ail' => NATTY_LANG_DEFAULT,
            ),
        ));

        // Read fresh declaration
        $new_coll = array();
        \Natty::trigger('system/emailDeclare', $new_coll);
        \Natty::trigger('system/emailRevise', $new_coll);

        // Update existing models
        foreach ($old_coll as $email):

            $id = $email->eid;

            // Entity-type was deleted?
            if (!isset($new_coll[$id])):
                $email->delete();
                unset ($old_coll[$id]);
                continue;
            endif;

            // Entity-type was updated!
            if ( !$email->isCustomized ):
                $record = $new_coll[$id];
                $email->setState($record);
                $email->save();
            endif;

            unset($new_coll[$id]);

        endforeach;

        // Insert new models
        foreach ($new_coll as $id => $record):
            $record['eid'] = $id;
            $email = $this->create($record);
            $email->ail = NATTY_LANG_DEFAULT;
            $email->isNew = TRUE;
            $email->save();
            $old_coll[$id] = $email;
        endforeach;
        
        // Clear static cache
        $this->staticCacheTruncate();
        
    }
    
    /**
     * Sends a pre-formatted email to the recipient mentioned in $options.
     * @param \Natty\ORM\EntityObject $entity
     * @param array $options Supports all options supported by EmailHelper. The
     * following additional options are also supported:<br />
     * recipientUser: User object to which the mail is to be sent.<br />
     * data: [optional] Associative array of data to be replaced in the
     * email subject and content.<br />
     * @see \Natty\Helper\EmailHelper
     */
    public function send($entity, array $options = array ()) {
        
        // Object must have an ID. Email template would be determined using
        // this unique Email Format ID.
        $this->isIdentifiable($entity, TRUE);
        
        // Emails must be enabled
        if ( !\Natty::readSetting('system--emailEnabled') ):
            \Natty\Console::debug('Email "' . $entity->name . '" not sent because email notifications are disabled.');
            return FALSE;
        endif;
        
        if ( !isset ($options['data']) )
            $options['data'] = array ();
        
        // Load user data
        if ( isset ($options['recipientUser']) ):
            $recipient = $options['recipientUser'];
            $options['recipientName'] = $recipient->name;
            $options['recipientEmail'] = $recipient->email;
        endif;
        
        // Email ID parts
        $eid_parts = explode('--', $entity->eid, 2);
        
        // Finalize mail content
        $email = array ();
        $email['subject'] = \Natty::getTwig()->createTemplate($entity->subject)->render($options['data']);
        $email['content'] = natty_render_twig(array (
            '_template' => str_replace(' ', '-', 'module/' . $eid_parts[0] . '/tmpl/email.' . $eid_parts[1]),
            '_data' => $options['data'],
        ));
        
        // Preview the message
        if (isset ($options['preview'])):
            echo '<strong>' . $email['subject'] . '</strong>';
            echo $email['content'];
            natty_debug();
        endif;
        
        // Determine email status
        $email['status'] = \Natty\Helper\EmailHelper::send($email, $options);
        if ( !$email['status'] )
            \Natty\Console::error('Email "' . $entity->name . '" could not be sent due to some technical problems.');
        
        return $email['status'];
        
    }
    
    /**
     * Render method not available
     * @param \Natty\ORM\EntityObject $entity
     * @param array $options
     * @throws \BadMethodCallException
     */
    public function render(&$entity, array $options = array()) {
        throw new \BadMethodCallException();
    }
    
}