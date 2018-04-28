<?php

/**
 * Form Object
 * @package Natty
 * @author JigaR Mehta <jigar.m1987@live.com>
 */
namespace Natty\Form;

// Import validation helpers
require_once NATTY_ROOT . '/core/include/validators.inc.php';

class FormObject
extends \Natty\StdClass {

    /**
     * An array of attributes for the form element
     * @var array
     */
    public $attributes = array (
        '_eventsEnabled' => 1,
        '_hooks' => array (),
        'method' => 'post',
        'action' => '',
        'class' => array ('n-form'),
    );

    /**
     * A list of items in the form: fieldsets and fields.
     * @var array
     */
    public $items = array ();

    /**
     * A list of actions in the form: buttons and anchors
     * @var array
     */
    public $actions = array ();
    
    /**
     * A list of JavaScript to include or execute.
     * @var array
     */
    public $scripts = array ();
    
    /**
     * An array of arguments to be passed to event callbacks.
     * @var array
     */
    public $eventData = array ();
    
    /**
     * Cached set of current values
     * @var array
     */
    protected $values;
    
    /**
     * Custom event listeners
     * @var array
     */
    protected $listeners = array ();
    
    /**
     * Whether the form is valid or not
     * @var bool
     */
    protected $isValid;

    /**
     * Whether the form was submitted or not
     * @var bool
     */
    protected $isSubmitted;

    /**
     * The current status of the form. One of self::STATUS_* constants.
     * @var int
     */
    protected $status = 0;
    
    /**
     * Whether the form is to be rebuilt after processing is successful
     * @var boolean
     */
    public $rebuild = FALSE;
    
    /**
     * Whether the response is to be redirected after processing is successful
     * @var boolean
     */
    public $redirect = FALSE;

    /**
     * Whether the form supports multiple input languages
     * @var boolean
     */
    protected $i18n = FALSE;
    
    const STATUS_PREPARE = 'prepare';
    protected $isPrepared = FALSE;

    const STATUS_VALIDATE = 'validate';
    protected $isValidated = FALSE;

    const STATUS_PROCESS = 'process';
    protected $isProcessed = FALSE;

    /**
     * Creates a FormHelper object.
     * @param array $attributes [optional] Attributes for the form element
     * @param array $event_data [optional] An array of data which would be
     * passed to event-listeners for the form
     * @return FormObject
     */
    public function __construct( array $attributes = array (), array $event_data = array () ) {
        
        // Prepare attributes
        if ( !isset ($attributes['id']) )
            throw new \InvalidArgumentException('Missing definition for required attribute "id"');
        
        // Determine i18n
        if ( isset ($attributes['i18n']) ):
            $this->i18n = TRUE;
            unset ($attributes['i18n']);
        endif;
        
        // Merge attributes
        $this->attributes = array_merge($this->attributes, $attributes);
        
        // Determine input language ID
        $input_lid = \Natty::getOutputLangId();
        if ( isset ($_POST['natty']) && isset ($_POST['natty']['ilid']) )
            $input_lid = $_POST['natty']['ilid'];
        
        // Add system fields
        $this->items['hidden'] = array (
            '_widget' => 'container',
            'class' => array ('hidden'),
        );
        $this->items['hidden']['_data']['natty.form'] = array (
            '_widget' => 'input',
            '_default' => $attributes['id'],
            '_ignore' => TRUE,
            'type' => 'hidden',
        );
        $this->items['hidden']['_data']['natty.ilid'] = array (
            '_widget' => 'input',
            '_container' => array ('hidden'),
            '_default' => $input_lid,
            '_ignore' => TRUE,
            'type' => 'hidden',
        );
        
        // Add default fieldset
        $this->items['default'] = array (
            '_widget' => 'container',
            '_label' => 'Basic Info',
            '_data' => array (),
        );
        
        if ( $this->i18n ):
            if ( \Natty::readSetting('system--i18n') ) {

                // Build input language picker
                $fitem_ilid = array (
                    '_label' => 'Input language',
                    '_widget' => 'language',
                    '_suffix' => '<input type="button" name="change-ilid" value="Change" class="k-button" onclick="this.form.submit();" />',
                    '_errors' => array (),
                    '_default' => \Natty::getInputLangId(),
                    'class' => array ('widget-small'),
                );

                // Detect language change
                if ( isset ($_REQUEST['ilid']) && $_POST['ilid'] != $input_lid ):

                    $input_lid = $_POST['ilid'];
                    $this->items['hidden']['_data']['natty.ilid']['_value'] = $input_lid;

                    // Reset post data
                    $_POST = array ();

                endif;

                // Validate input language
                if ( !$lang = \Natty::getEntity('system--language', $input_lid) ):
                    $fitem_ilid['_errors'][] = 'Please choose a valid language to continue.';
                    $input_ail = \Natty::readSetting('system--language');
                endif;

                // Attach input language picker
                $fitem_ilid['_description'] = 'You are editing this data in ' . $input_lid . '.';
                $fitem_ilid['_value'] = $input_lid;

            }
            else {

                $fitem_ilid = array (
                    '_widget' => 'markup',
                    '_display' => 0,
                    '_default' => \Natty::getOutputLangId(),
                );

            }
            $this->items['default']['_data']['ilid'] = $fitem_ilid;
            
        endif;
        
        // Add the default form hook
        array_unshift($this->attributes['_hooks'], $attributes['id']);
        
        $event_data['form'] =& $this;
        $this->eventData = $event_data;
        
    }
    
    public function getStatus() {
        return $this->status;
    }

    public function setStatus( $status ) {
        
        $this->status = $status;
        
        switch ( $status ):
            case self::STATUS_PREPARE:
                $this->isPrepared = 1;
                break;
            case self::STATUS_VALIDATE:
                $this->isValidated = 1;
                break;
            case self::STATUS_PROCESS:
                $this->isProcessed = 1;
                break;
        endswitch;
        
    }

    public function addListener($callback) {
        $this->listeners[] = $callback;
    }
    
    public function getLastContainer() {
        $lc = FALSE;
        foreach ( $this->items as $key => $definition ):
            if ( 'container' == $definition['_widget'] )
                $lc = $key;
        endforeach;
        return $lc;
    }
    
    /**
     * Returns an associative array of values in the form, except for items
     * which have an attribute "_ignore".
     * @return array An associative array of values
     */
    public function getValues() {

        // Form must at least be prepared
        if ( !$this->isPrepared )
            throw new \BadMethodCallException('Form::onPrepare must be called before ' . __METHOD__ . '.');

        // Prepare values array
        if ( is_null($this->values) ):
            $this->values = array ();
            foreach ( $this->items as $item_name => $item_definition ):
                
                if ( isset ($item_definition['_ignore']) || !isset ($item_definition['_widget']) )
                    continue;
                
                $item_value = WidgetHelper::getValue($item_definition);
                
                if ( 'container' == $item_definition['_widget'] ) {
                    $this->values = natty_array_merge_nested($this->values, $item_value);
                }
                else {
                    natty_array_set($this->values, $item_name, $item_value);
                }
                
            endforeach;
        endif;
        
        return $this->values;

    }
    
    public function onPrepare() {

        // Update form status
        $this->setStatus(self::STATUS_PREPARE);
        $this->triggerEvent();
        
        foreach ( $this->items as $key => &$defi ):

            // Must specify a widget
            if ( !isset ($defi['_widget']) )
                continue;

            // Set name attribute
            if ( !is_numeric($key) )
                $defi['name'] = $key;

            WidgetHelper::prepare($defi);

            // Set data if form is submitted
            if ( $this->isSubmitted() && !$this->isSubmitted('reset') )
                WidgetHelper::setValue($defi);

            unset ($defi);

        endforeach;
        
        foreach ( $this->scripts as $key => &$defi ):
            
            if ( !is_array($defi) )
                $defi = array ('_data' => $defi);
            
            // Wrap code inside anonymous function
            if ( isset ($defi['_data']) ):
                $defi['_data'] = '(function(){' . $defi['_data'] . '})();';
            endif;
            
            // Convert root relative source to absolute source
            if ( isset ($defi['src']) && !natty_is_abspath($defi['src']) ):
                $defi['src'] = NATTY_BASE . $defi['src'];
            endif;
            
            $defi['_render'] = 'element';
            $defi['_element'] = 'script';
            
            unset ($defi);
            
        endforeach;
        
        // Remove default fieldset if un-used
        if ( 0 === sizeof($this->items['default']['_data']) )
            $this->items['default']['_display'] = 0;
        
        // If input language is readonly, hide the "change" button
        if ( $this->i18n ):
            
            if ( $this->items['default']['_data']['ilid']['readonly'] )
                $this->items['default']['_data']['ilid']['_suffix'] = NULL;
            
        endif;
        
        // Must have at least one action
        if ( 0 == sizeof($this->actions) ):
            $this->actions['submit'] = array (
                '_widget' => 'button',
                '_label' => 'Submit',
                'type' => 'submit',
            );
        endif;
        
        foreach ( $this->actions as $key => &$definition ):

            // Defaults to "button" widget
            if ( !isset ($definition['_widget']) )
                $definition['_widget'] = 'button';

            // Set name attribute
            if ( !is_numeric($key) )
                $definition['name'] = $key;

            WidgetHelper::prepare($definition);

            unset ($definition);

        endforeach;

    }

    public function onValidate() {
        
        // Update form status
        $this->setStatus(self::STATUS_VALIDATE);
        $this->triggerEvent();

        // Assume all fields are valid
        if ( is_null($this->isValid) )
            $this->isValid = TRUE;

        // Call validators for each item
        foreach ( $this->items as $item_key => &$item_definition ):

            $item_valid = WidgetHelper::validate($item_definition, $this);
            if ( FALSE === $item_valid )
                $this->isValid = FALSE;
            
            unset ($item_definition);

        endforeach;
        
        // Show a message with errors
        if ( !$this->isValid ) {
            \Natty\Console::error('One or more errors were found in the form you submitted.', array (
                'unique' => 1
            ));
        }

    }

    public function onProcess() {

        // Update form status
        $this->setStatus(self::STATUS_PROCESS);
        $this->triggerEvent();

        // If the form has errors, display the form again
        if ( !$this->isValid() )
            $this->rebuild = TRUE;
        
        // If the form is not supposed to be rebuilt, redirect the request
        if ( !$this->rebuild ):
            $response = \Natty::getResponse();
            if ( !$this->redirect )
                $response->refresh();
            else
                $response->bounce($this->redirect);
        endif;

    }
    
    /**
     * Renders the form and returns the rendered markup
     * @return string
     */
    public function getRarray() {

        if ( !$this->isPrepared )
            throw new \BadMethodCallException('Cannot call ' . __METHOD__ . ' before ' . __CLASS__ . '::onPrepare()');
        
        $build = $this->items;

        // Add an actions fieldset to the form
        if ( sizeof($this->actions) ):

            // Add an actions fieldset
            $build['actions'] = array (
                '_render' => 'element',
                '_element' => 'fieldset',
                '_data' => array (),
                'class' => array ('system-actions'),
            );

            // See if some fields coincide with actions
            $beyond_first = FALSE;
            foreach ( $this->actions as $key => $definition ):

                // The first action would be marked primary
                if ( !$beyond_first ):
                    $definition['class'][] = 'k-primary';
                    $beyond_first = TRUE;
                endif;

                $build['actions']['_data'][$key] = $definition;

            endforeach;
            unset ($beyond_first);

        endif;
        
        $rarray = array ();
        
        // Prepare the form element
        $rarray_form = $this->attributes;
        $rarray_form['_render'] = 'element';
        $rarray_form['_element'] = 'form';
        $rarray_form['_data'] = $build;
        $rarray[] = $rarray_form;
        
        // Generate script tags for JavaScript
        if ( $this->scripts )
            $rarray[] = $this->scripts;

        return $rarray;

    }

    /**
     * Returns whether the from was submitted or not
     * @param string $action [optional] In case of multi-action forms,
     * helps in test of submission for a particular action. Pass the 
     * "subkey" which you wish to test for, where the action was set as
     * "submit_subkey".
     * @return bool True or false
     */
    public function isSubmitted($action = NULL) {

        if ( is_null($this->isSubmitted) ):
            $this->isSubmitted = FALSE;
            if ( isset ($_POST['natty']) ):
                if ( isset ($_POST['natty']['form']) && $this->attributes['id'] == $_POST['natty']['form'] )
                    $this->isSubmitted = TRUE;
            endif;
        endif;

        if ( !$this->isSubmitted )
            return FALSE;

        return $action ? isset ($_POST[$action]) : TRUE;

    }

    /**
     * Returns validation status of the form. If form has not yet been
     * submitted or validated, it returns NULL. If the form has been
     * submitted it returns a boolean based on whether the data is valid
     * or invalid.
     * @param boolean [optional] $value Validation status to set.
     * @return null|boolean
     */
    public function isValid( $value = NULL ) {

        if ( is_null($value) ) {
            return $this->isValid;
        }
        else {
            $this->isValid = (bool) $value;
        }

    }
    
    protected function triggerEvent() {
        
        // Trigger custom listeners
        $state = $this->getStatus();
        
        foreach ( $this->listeners as $callback ):
            call_user_func_array($callback, array (&$this->eventData));
        endforeach;
        
        // Events disabled?
        if ( !$this->attributes['_eventsEnabled'] )
            return;
        
        // Specific events
        foreach ( $this->attributes['_hooks'] as $hook ):
            \Natty::trigger($hook . 'Handle', $this->eventData);
        endforeach;
        
    }

}