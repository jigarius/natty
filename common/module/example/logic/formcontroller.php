<?php

namespace Module\Example\Logic;

class FormController {
    
    public static function pageBasics() {
        
        $form = new \Natty\Form\FormObject(array (
            'id' => 'example-form-standard'
        ));

        // Basic form widgets
        $form->items['basic'] = array (
            '_widget' => 'container',
            '_label' => 'Basic Widgets',
        );
        $form->items['basic']['_data']['basic.textbox'] = array (
            '_widget' => 'input',
            '_label' => 'Textbox',
            '_description' => 'Standard input widget. This would be validated as an email address.',
            'autocomplete' => 'off',
            'required' => TRUE,
            '_validators' => array (
                array ('natty_validate_email')
            )
        );
        $form->items['basic']['_data']['basic.password'] = array (
            '_widget' => 'input',
            '_label' => 'Password',
            '_description' => 'Input box with type "password".',
            'autocomplete' => 'off',
            'type' => 'password'
        );
        $form->items['basic']['_data']['basic.textarea'] = array (
            '_widget' => 'textarea',
            '_label' => 'Textarea',
            '_description' => 'Standard textarea widget.'
        );
        $form->items['basic']['_data']['basic.dropdown'] = array (
            '_widget' => 'dropdown',
            '_label' => 'Dropdown',
            '_description' => 'Standard dropdown widget.',
            '_options' => array (
                'a' => 'Alpha',
                'b' => 'Bravo',
                'c' => 'Charlie',
                // Options can also be passed in as rarrays
                array (
                    'value' => 'd',
                    'disabled' => 1,
                    '_data' => 'Delta',
                )
            ),
        );
        $form->items['basic']['_data']['basic.checkbox'] = array (
            '_widget' => 'input',
            '_label' => 'Checkbox',
            '_description' => 'Standard checkbox widget.',
            'type' => 'checkbox',
            'value' => 1,
        );

        // Advanced form widgets
        $form->items['advanced'] = array (
            '_widget' => 'container',
            '_label' => 'Advanced Widgets',
        );
        $form->items['advanced']['_data']['advanced.markup'] = array (
            '_widget' => 'markup',
            '_label' => 'Markup',
            '_markup' => 'Custom markup widget.',
            'readonly' => TRUE,
            '_value' => 'bunny',
            '_description' => 'This item would always return a fixed value "bunny" specified on server-side.'
        );
        $form->items['advanced']['_data']['advanced.rte'] = array (
            '_widget' => 'rte',
            '_label' => 'Rich Text Editor',
        );
        $form->items['advanced']['_data']['advanced.options'] = array (
            '_widget' => 'options',
            '_label' => 'Options',
            '_description' => 'Standard options widget.',
            '_options' => array (
                'a' => 'Alpha',
                'b' => 'Bravo',
                'c' => 'Charlie',
                // Options can also be passed in as rarrays
                array (
                    'value' => 'd',
                    'disabled' => 1,
                    '_data' => 'Delta',
                )
            ),
        //    'multiple' => 'multiple'
        );
        $form->items['advanced']['_data']['advanced.date'] = array (
            '_widget' => 'input',
            '_label' => 'Date',
            '_description' => 'Standard date widget.',
            'type' => 'date',
        );
        $form->items['advanced']['_data']['advanced.time'] = array (
            '_widget' => 'input',
            '_label' => 'Time',
            '_description' => 'Standard time widget.',
            'type' => 'time',
        );
        $form->items['advanced']['_data']['advanced.datetime'] = array (
            '_widget' => 'input',
            '_label' => 'Date & Time',
            '_description' => 'Standard date and time widget.',
            'type' => 'datetime',
        );
        $form->items['advanced']['_data']['advanced.color'] = array (
            '_widget' => 'input',
            '_label' => 'Color',
            '_default' => '#00ff00',
            'type' => 'color',
        );

        $form->actions['save'] = array (
            'type' => 'submit',
            '_label' => 'Submit',
        );
        $form->actions['back'] = array (
            'type' => 'anchor',
            'href' => \Natty::url('dashboard/features/example/form'),
            '_label' => 'Back'
        );

        // Specify custom callbacks for a particular form-stage
        $form->addListener('\\Module\\Example\Controller::validateExampleFormStandard');

        // Call other modules to alter the form
        $form->onPrepare();

        // Handle submission and check values
        if ( $form->isSubmitted() ):

            // Custom validation (if any)

            // Call other modules to validate the form
            $form->onValidate();

        endif;

        // If the form is valid, process it
        if ( $form->isValid() ):

            // Process form data as required
            $values = $form->getValues();
            natty_debug($values);

            // Call other modules to process the form
            $form->onProcess();

        endif;

        // Prepare output
        $output['form'] = $form->getRarray();
        return $output;
        
    }
    
}