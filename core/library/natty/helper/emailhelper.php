<?php

namespace Natty\Helper;

/**
 * @todo Integrate with PHPMailer
 */
abstract class EmailHelper {
    
    protected static function renderHeader($name, $content) {
        return $name . ': ' . $content;
    }
    
    protected static function renderAddress($email, $name = NULL) {
        return empty ($name)
            ? $email : '"' . htmlentities($name) . '" <' . $email . '>';
    }
    
    /**
     * 
     * @param array $email An array defining the message to be sent. It should
     * contain the following indices:<br />
     * subject: Subject of the mail<br />
     * content: Message body of the mail<br />
     * mimeType: [optional] The mime-type of the message content<br />
     * mimeVersion: [optional] The mime-version of the message content<br />
     * @param array $options An associative array of the following options:<br />
     * senderEmail: [optional] Email address of the sender<br />
     * senderName: [optional] Name of the sender<br />
     * recipient: [optional] Name of the sender<br />
     * @return type
     * @throws \BadMethodCallException
     * @throws \RuntimeException
     */
    public static function send($email, array $options = array ()) {
        
        // Convert to array
        $email = (array) $email;
        
        // Merge with defaults
        $email = array_merge(array (
            'subject' => '-',
            'mimeType' => 'text/plain',
            'mimeVersion' => '1.0',
            'content' => NULL,
            'template' => NULL,
        ), $email);
        
        // Prepare options
        $options = array_merge(array (
            'senderEmail' => \Natty::readSetting('system--siteEmail'),
            'senderName' => \Natty::readSetting('system--siteName'),
            'recipientName' => NULL,
            'recipientEmail' => NULL,
            'recipient' => NULL,
            'recipients' => array (),
            'method' => 'sendmail',
        ), $options);
        
        // Set sender email
        if (!$options['senderEmail'])
            $options['senderEmail'] = 'noreply@' . $_SERVER['SERVER_NAME'];
        
        // Determine single recipient
        if ( $options['recipientEmail'] )
            $options['recipient'] = array ($options['recipientEmail'], $options['recipientName']);
        unset ($options['recipientEmail'], $options['recipientName']);
        
        // Treat single recipient as one of multiple recipients
        if ( $options['recipient'] )
            $options['recipients'][] = $options['recipient'];
        unset ($options['recipient']);
        
        // Prepare recipients
        foreach ( $options['recipients'] as &$this_recipient ):
            if ( is_array($this_recipient) ):
                $this_recipient = array_values($this_recipient);
                $this_recipient = call_user_func_array(array (__CLASS__, 'renderAddress'), $this_recipient);
            endif;
        endforeach;
        
        // Must have recipient
        if ( !$options['recipients'] )
            throw new \BadMethodCallException('Required option "recipients" not specified.');
        $options['recipients'] = implode(', ', $options['recipients']);
        
        // Must have sender
        $options['sender'] = self::renderAddress($options['senderEmail'], $options['senderName']);
        unset ($options['senderName'], $options['senderEmail']);
        
        // Prepare mail headers
        $email['headers'] = array ();
        $email['headers'][] = self::renderHeader('Content-Type', $email['mimeType']);
        $email['headers'][] = self::renderHeader('MIME-Version', $email['mimeVersion']);
        $email['headers'][] = self::renderHeader('From', $options['sender']);
        
//        // Finalize subject
//        if ($options['data'])
//            $email['subject'] = natty_replace($options['data'], $email['subject']);
//        
//        // Finalize content
//        if ($email['template']):
//            $email['content'] = natty_render_twig(array (
//                '_template' => $email['template'],
//                '_data' => $options['data'],
//            ));
//        endif;
        
        // Determine and call the sender method
        $method = 'sendVia' . ucfirst($options['method']);
        if ( !$method || !method_exists(__CLASS__, $method) )
            throw new \RuntimeException('Required option "method" has an invalid value.');
        
        return self::$method($email, $options);
        
    }
    
    protected static function sendViaSendmail($email, $options) {
        
        // Merge all headers
        $email['headers'] = implode("\r\n", $email['headers']);
        
        // Wrap message content
        $email['content'] = wordwrap($email['content'], 70, "\n");
        
        return @mail($options['recipients'], $email['subject'], $email['content'], $email['headers']);
        
    }
    
}