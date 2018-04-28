<?php

defined('NATTY') or die;

/**
 * Renders the given array or array as per specified rendering instructions.
 * This was inspired by the Drupal Render API mixed with some own thoughts.
 * @param array $rarray A renderable array.
 * @return string|false Rendered output or false on failure.
 */
function natty_render( $rarray ) {
    
    // Null items and strings would be returned as is
    if ( !is_array($rarray) )
        return $rarray;
    
    // Empty objects don't need processing
    if ( empty ($rarray) )
        return '';
    
    // The structure is assumed to be an array
    $rarray = (array) $rarray;
    
    // The item is not to be displayed?
    if ( isset ($rarray['_display']) && !$rarray['_display'] )
        return '';
    
    // Determine render format
    $format = isset ($rarray['_render'])
            ? $rarray['_render'] : 'collection';
    unset ($rarray['_render']);
    
    // Call the appropriate renderer
    switch ( $format ):
        // The element is an array of renderable elements
        case 'collection':
            
            $output = '';
            
            foreach ( $rarray as $element ):
                $mu = natty_render($element);
                $output .= natty_render($element);
            endforeach;
            
            break;
        // Renders as markup, returns the passed markup
        case 'markup':
            if ( !isset ($rarray['_markup']) )
                break;
            $output = $rarray['_markup'];
            break;
        default:
            $renderer = 'natty_render_' . $format;
            $output = $renderer($rarray);
            break;
    endswitch;
    
    // Force something as output
    $output = isset ($output)
        ? $output : 'Non-renderable rarray';
    
    return $output;
    
}

function natty_render_callback( array $rarray ) {
    
    if ( !isset ($rarray['_callback']) )
        throw new InvalidArgumentException('Required index "_callback" not defined.');
    
    return call_user_func($rarray['_callback'], $rarray);
    
}

/**
 * Renders an HTML anchor with the specified attributes. Works just like
 * natty_render_element with a few special tricks for anchors.
 * @param array $rarray A renderable array. Required attributes include:<br />
 * _data: Text for the link.<br />
 * Rest of the indexes would be treated as attributes for the link.
 * @return string
 */
function natty_render_anchor(array $rarray) {
    
    // Determine "href" attribute
    if ( !isset ($rarray['href']) )
        $rarray['href'] = '#';
    
    // Convert href to string if an array is provided
    if (is_array($rarray['href'])):
        $rarray['href'] = call_user_func_array('nroute', $rarray['href']);
        $rarray['href'] = $rarray['href']
                ? : '#';
    endif;
    
    // Pass things to the element renderer
    $rarray['_element'] = 'a';
    return natty_render_element($rarray);
    
}

function natty_render_block( array $rarray ) {
    
    $rarray = array_merge(array (
        '_block' => FALSE,
        '_heading' => FALSE,
        '_data' => FALSE,
        'class' => array (),
    ), $rarray);
    
    // Add system classes
    $rarray['class'][] = 'n-block';
    if ( $rarray['_block'] )
        $rarray['class'][] = 'block-' . str_replace('/', '-', $rarray['_block']);
    $rarray['class'][] = $rarray['_heading']
            ? 'has-heading' : 'no-heading';
    
    // Render body
    if ( is_array($rarray['_data']) )
        $rarray['_data'] = natty_render($rarray['_data']);
    $rarray['_data'] = '<div class="content">' . $rarray['_data'] . '</div>';
    
    // Render head
    if ( $rarray['_heading'] ):
        $rarray['_data'] = '<div class="head"><h2 class="heading">' . $rarray['_heading'] . '</h2></div>' 
            . $rarray['_data'];
    endif;
    
    // Render a div element
    $rarray['_element'] = 'div';
    return natty_render_element($rarray);
    
}

/**
 * Renders an entity with the given options. The arguments are passed to
 * EntityHandler::render() method.
 * @param array An associative array of options including:<br />
 * _entity: The fully-loaded entity object to be rendered.<br />
 * _options: Rendering options as mentioned in EntityHandler::render()
 * @return string Markup for the entity
 */
function natty_render_entity(array $rarray = array ()) {
    
    if ( !isset ($rarray['_entity']) )
        throw new InvalidArgumentException('Required index "_entity" not defined');
    
    if ( !isset ($rarray['_options']) )
        $rarray['_options'] = array ();
    
    return $rarray['_entity']->render($rarray['_options']);
    
}

/**
 * Renders a twig file with the given variables. Template is first looked
 * for in the skin directory. If not found, it is looked for in the module
 * directory. If not found anywhere, an exception is thrown.
 * @param array $rarray A renderable array containing options:<br />
 * _template: An array of templates to look for. The first template found would
 * be rendered.<br />
 * _data: [optional] An assoc array of data to the sent to the template.
 * @return string Template output.
 */
function natty_render_twig(array $rarray) {
    
    // Prepare twig environment
    $twig = \Natty::getTwig();
    
    // Determine template options
    if ( !isset ($rarray['_template']) )
        throw new InvalidArgumentException('Required index "_template" not defined');
    $template_opts = $rarray['_template'];
    
    // Template options must be an array
    if (!is_array($template_opts))
        $template_opts = array ($template_opts);
    
    // Lookup template
    foreach ($template_opts as $template):
        try {
            $o_template = $twig->loadTemplate($template);
        }
        catch (\Twig_Error_Loader $ex) {}
    endforeach;
    
    // Template not found!
    if (!$o_template)
        throw new RuntimeException('Template file not found for "' . $template . '"');
    
    // Render the template
    return $o_template->render($rarray['_data']);
    
}

/**
 * Renders a template file with the given set of variables.
 * @param array $rarray
 * @return string
 * @throws InvalidArgumentException
 */
function natty_render_template(array $rarray) {
    
    // Determine template
    if ( !isset ($rarray['_template']) )
        throw new InvalidArgumentException('Required index "_template" not defined');
    $template_opts = $rarray['_template'];
    
    // Template supports fallbacks
    if ( !is_array($template_opts) )
        $template_opts = array ($template_opts);
    
    // Determine the template file
    $skin_root = \Natty::getSkin()->path(NULL, 'real');
    $site_root = \Natty::readSetting('system--siteRoot');
    
    // Lookup templates until one is found
    foreach ( $template_opts as $template ):
        
        // Lookup file in skin
        $_file = $skin_root . DS . $template . '.php';
        if ( is_file($_file) )
            break;
        
        // Lookup file in instance
        $_file = $site_root . DS . $template . '.php';
        if ( is_file($_file) )
            break;
        
        // Lookup file in common
        $_file = NATTY_ROOT . '/common/' . $template . '.php';
        if ( is_file($_file) )
            break;
        
        // Lookup file in core
        $_file = NATTY_ROOT . '/core/' . $template . '.php';
        if ( is_file($_file) )
            break;
        
    endforeach;

    // If no template exists, do nothing!
    if ( !is_file($_file) ):
        throw new RuntimeException('Template file not found for "' . $template . '"');
    endif;
    
    // Pass variables to the template (if any)
    if ( !isset ($rarray['_data']) )
        $rarray['_data'] = array ();
    
    $variables = $rarray['_data'];
    extract($variables);
    
    ob_start();
    include $_file;
    $output = ob_get_clean();
    return $output;
    
}

/**
 * Renders DOM element attribute string from an associative array.
 * @param array $rarray An associative array of attribute data.
 * @return string Attribute markup.
 */
function natty_render_attributes(array $rarray) {
    
    $output = '';
    
    foreach ( $rarray as $attr_name => $attr_value ):
        
        // Ignore attributes starting with '_'
        if ( 0 === strpos($attr_name, '_') )
            continue;
        
        switch ( $attr_name ):
            case 'autocomplete':
                $attr_value = ($attr_value && 'off' != $attr_value)
                    ? 'off' : 'on';
                break;
            case 'required':
            case 'disabled':
            case 'readonly':
            case 'selected':
            case 'checked':
            case 'multiple':
                $attr_value = ( $attr_value ) ? $attr_name : FALSE;
                break;
            case 'name':
                
                // Nested POST name?
                if ( FALSE !== strpos($attr_value, '.') ):
                    $parts = explode('.', $attr_value);
                    $attr_value = array_shift($parts);
                    $attr_value .= '[' . implode('][', $parts) . ']';
                    unset ($parts);
                endif;
                
                if ( isset ($rarray['multiple']) )
                    $attr_value .= '[]';
                
                break;
            case 'data-ui-init':
            case 'class':
                
                // Use only unique classnames
                $attr_value = array_unique($attr_value);
                $attr_value = implode(' ', $attr_value);
                
                // Ignore empty class name
                if ( 0 == sizeof($attr_value) )
                    $attr_value = '';
                
                break;
        endswitch;

        // Ignore the value if it is empty
        if ( is_array($attr_value) )
            throw new InvalidArgumentException('Invalid non-string attribute "' . $attr_name . '"!');
        if ( 0 === strlen($attr_value) && 'value' != $attr_name )
            continue;
        
        $output .= ' ' . $attr_name . '="' . $attr_value . '"';

    endforeach;
    
    return $output;
    
}

/**
 * Renders a DOM Element with the given node value ($data) and the specified
 * attributes. The class attribute must be specified as an array.
 * @param array $rarray An array with indexes:<br />
 * _element: The element to render.<br />
 * _data: The Inner HTML for the element - string or rarray.<br />
 * _close: If set to FALSE, the closing tag would not be rendered.
 * @return string Markup for the element
 */
function natty_render_element( array $rarray ) {
    
    // Element to render
    if ( !isset ($rarray['_element']) )
        throw new InvalidArgumentException('Required index "_element" not defined');
    $element = $rarray['_element'];
    
    // Content of the element
    $data = NULL;
    if ( isset ($rarray['_data']) )
        $data = $rarray['_data'];
    
    // See if its a self-closing tag
    $self_close = in_array($element, array ('input', 'meta', 'link', 'br'));
    
    // If only the opening tag has been requested
    $close = TRUE;
    if ( isset ($rarray['_close']) && FALSE === $raray['_close'] )
        $close = FALSE;
    $close = $close && !$self_close;
    
    // Open the tag
    $output = "<{$element}";
    
    // Render attributes
    $output .= natty_render_attributes($rarray);
    
    // Self-close the tag?
    $output .= ( $self_close ) 
            ? '/>' : '>';
    
    // Render only the opening tag?
    if ( $close )
        $output .= natty_render($data) . "</{$element}>";
    
    return $output;
    
}

/**
 * Renders an ordered, unordered or div list of the given objects.
 * @param array $rarray A renderable array with indexes:<br />
 * _element: The type of list to render - ul, ol or div<br />
 * _items: An array of items in the list as strings or rarray.<br />
 * @return string Markup for the list
 */
function natty_render_list( array $rarray = array () ) {
    
    // Determine list type
    if ( !isset ($rarray['_element']) )
        $rarray['_element'] = 'ul';
    
    // Determine item element
    $item_element = ('div' === $rarray['_element'])
            ? 'div' : 'li';
    
    // Determine class names
    if ( !isset ($rarray['class']) )
        $rarray['class'] = array ('n-list');
    if ( !isset ($rarray['_item_class']) )
        $rarray['_item_class'] = 'n-list-item';
    
    // Determine items
    if ( !isset ($rarray['_items']) )
        $rarray['_items'] = array ();
    
    // Build Inner HTML
    $rarray['_data'] = array ();
    foreach ( $rarray['_items'] as $this_item ):
        
        // Convert string item into rarray
        if ( !is_array($this_item) )
            $this_item = array ('_data' => $this_item);
        
        // Rendering instructions provided? Then this item must be an element
        // within the list index.
        if ( !isset ($this_item['_data']) || isset ($this_item['_render']) )
            $this_item = array ('_data' => $this_item);
        
        // Add default classes
        if ( !isset ($this_item['class']) )
            $this_item['class'] = array ();
        if ( $rarray['_item_class'] )
            $this_item['class'][] = $rarray['_item_class'];
        
        // Finalize item rarray
        $this_item['_render'] = 'element';
        $this_item['_element'] = $item_element;
        $rarray['_data'][] = $this_item;
        
    endforeach;
    
    // List is empty?
    if ( empty ($rarray['_data']) && isset ($rarray['_empty']) ):
        return '<div class="n-emptytext">' . $rarray['_empty'] . '</div>';
    endif;
    
    // Render the list
    return natty_render_element($rarray);
    
}

function natty_render_table(array $rarray) {

    // Specify attributes
    $rarray = array_merge(array (
        'class' => array ('n-table', 'n-table-striped', 'n-table-border-outer'),
        '_head' => array (),
        '_body' => array (),
        '_foot' => array (),
    ), $rarray);

    // Body defaults to items
    if ( !isset ($rarray['_body']) && isset ($rarray['items']) )
        $rarray['_body'] = $rarray['items'];
    
    $output = '';
    
    // Wrap the list in a form?
    if ( isset ($rarray['_form']) ):
        $rarray['_form'] = array_merge(array (
            'action' => '',
            'method' => 'post',
        ), $rarray['_form']);
        $output .= '<form' . natty_render_attributes($rarray['_form']) . '>';
    endif;

    $output .= '<table' . natty_render_attributes($rarray) . '>';

    // Generate headings (if any)
    if ( sizeof($rarray['_head']) ):

        $output .= '<thead><tr>';
        foreach ( $rarray['_head'] as $key => $cell ):

            if (!is_array($cell))
                $cell = array ('_data' => $cell);
            
            // If no data, do not render
            if (!isset ($cell['_data']))
                continue;

            $cell['_render'] = 'element';
            $cell['_element'] = 'th';
            $output .= natty_render($cell);

        endforeach;
        $output .= '</tr></thead>';

    endif;
    
    // Generate rows (if any)
    $output .= '<tbody>';

    // Generate empty list message!
    if ( empty ($rarray['_body']) ):
        $output .= '<tr><td' . (isset ($rarray['_head']) ? ' colspan="' . sizeof($rarray['_head']) . '"' : '') . '><div class="n-emptytext">' . (isset ($emptytext) ? $emptytext : 'No items could be found to display here.') . '</div></td></tr>';
    endif;

    // Render rows markup
    foreach ( $rarray['_body'] as $row ):

        // Prepare row rarray
        if ( !isset ($row['_data']) )
             $row = array ('_data' => $row);

        foreach ( $row['_data'] as $key => &$cell ):

            // Action container?
            if ( 'context-menu' === $key ):

                // Prepare row context menus
                if ( !isset ($row['data-ui-init']) )
                    $row['data-ui-init'] = array ();
                $row['data-ui-init'][] = 'context-menu-host';

                // Prepare cell items as popup menu
                $cell = array (
                    '_data' => array (
                        '_render' => 'list',
                        '_items' => $cell,
                        'class' => array ('context-menu-popup'),
                        'data-ui-init' => array ('context-menu-popup')
                    ),
                );

            endif;

            // Prepare cell rarray
            if ( !isset ($cell['_data']) || isset ($cell['_render']) )
                $cell = array ('_data' => $cell);

            $cell['_render'] = 'element';
            $cell['_element'] = 'td';

            unset ($cell);

        endforeach;

        $row['_element'] = 'tr';
        $output .= natty_render_element($row);

    endforeach;

    $output .= '</tbody>';

    $output .= '</table>';

    // Close wrapping form, if any
    if ( isset ($rarray['_form']) ):

        if ( isset ($rarray['_form']['_actions']) ):
            $output .= '<div class="system-actions">' . natty_render($rarray['_form']['_actions']) . '</div>';
        endif;

        $output .= '</form>';

    endif;
    
    return $output;
    
}

/**
 * 
 * @param array $rarray
 */
function natty_render_csv(array $rarray) {
    
    $line_data = array ();
    $search = array ('"', "'");
    $replace = array ('""', "''");
    
    if ( isset ($rarray['_head']) ):
        $line = array ();
        foreach ( $rarray['_head'] as $th ):
            if ( !is_array($th) || !isset ($th['_data']) )
                $th = array ('_data' => $th);
            $line[] = '"' . str_replace($search, $replace, $th['_data']) . '"';
        endforeach;
        $line_data[] = implode(',', $line);
    endif;
    
    if ( isset ($rarray['_body']) ):
        
        foreach ( $rarray['_body'] as $tr ):
            
            if ( !is_array($tr) || !isset ($tr['_data']) )
                $tr = array ('_data' => $tr);
            
            $line = array ();
            foreach ($tr['_data'] as $td):
                
                if ( !is_array($td) || isset ($td['_data']) )
                    $td = array ('_data' => $td);
                
                $line[] = '"' . str_replace($search, $replace, $td['_data']) . '"';
                
            endforeach;
            $line_data[] = implode(',', $line);
            
        endforeach;
        
    endif;
    
    return implode("\r\n", $line_data);
    
}

/**
 * Renders a list pager.
 * @param array $rarray
 * @return string Markup for the pager.
 */
function natty_render_pager(array $rarray) {
    
    $command = Natty::getCommand();
    $data = $rarray['_data'];
    $parameters = $data['parameters'];
    $state = $data['state'];
    
    // Determine data to be sent with pager queries
    $query_data = array ();
    foreach ( $state as $param_name => $param_value ):
        if ( $parameters[$param_name]['_isLocked'] )
            continue;
        if ( $parameters[$param_name]['_default'] == $param_value )
            continue;
        $query_data[$param_name] = $param_value;
    endforeach;
    unset ($param_name, $param_value);
    
    $output = '';
    
    // If there is only one page, show nothing
    if ( $data['total_pages'] <= 1 )
        return $output;
    
    // The toolbar
    $output .= '<div class="n-toolbar n-pager">';

    // Page info
    $output .= '<div class="item-info">';
    if ( isset ($data['total_items']) ):
        $output .= natty_text('Items [@first] to [@last] of [@total] items', array (
            'first' => $data['first_item'],
            'last' => $data['last_item'],
            'total' => $data['total_items']
        ));
    endif;
    $output .= '</div>';

    // The navigator
    $output .= '<div class="page-links n-buttongroup">';

    // Jumper to the first page
    if ( 1 == $data['current_page'] ) {
        $first_href = '#';
    }
    else {
        $first_params = $query_data;
        $first_params['si'] = 0;
        $first_href = \Natty::url($command, $first_params);
    }
    $first_class = array ('first', 'k-button');
    if ('#' == $first_href)
        $first_class[] = 'k-state-disabled';

    // Jumper to previous page
    if ( $data['current_page'] <= 1 ) {
        $prev_href = '#';
    }
    else {
        $prev_params = $query_data;
        $prev_params['si'] -= $state['cs'];
        $prev_href = \Natty::url($command, $prev_params);
    }
    $prev_class = array ('prev', 'k-button');
    if ('#' == $prev_href) 
        $prev_class[] = 'k-state-disabled';

    // Jumper to next page
    if ( $data['last_item'] >= $data['total_items'] ) {
        $next_href = '#';
    }
    else {
        $next_params = $query_data;
        $next_params['si'] = $state['si'] + $state['cs'];
        $next_href = \Natty::url($command, $next_params);
    }
    $next_class = array ('next', 'k-button');
    if ('#' == $next_href)
        $next_class[] = 'k-state-disabled';

    // Jumper to the last page
    if ( $data['total_pages'] == $data['current_page'] ) {
        $last_href = '#';
    }
    else {
        $last_params = $query_data;
        $last_params['si'] = $state['cs'] * ($data['total_pages']-1);
        $last_href = \Natty::url($command, $last_params);
    }
    $last_class = array ('last', 'k-button');
    if ('#' == $last_href)
        $last_class[] = 'k-state-disabled';

    // Generating the First button
    $output .= natty_render_anchor(array (
        '_data' => 'First',
        'href' => $first_href,
        'class' => $first_class,
    ));

    // Generating the back button
    $output .= ' ' . natty_render_anchor(array (
        '_data' => 'Prev',
        'href' => $prev_href,
        'class' => $prev_class,
    ));

    // Generating specific page jumpers
    $start_page = ($data['current_page'] > 3) 
        ? ($data['current_page'] - 2) : 1;
    $start_page = $start_page ? : 1;
    for ( $page = $start_page; $page < ($start_page + 5); $page++ ):

        // Page button URL
        if ( $page < 1 || $page > $data['total_pages'] ) {
            $page_href = '#';
        }
        else {
            $page_params = $query_data;
            $page_params['si'] = $state['cs'] * ($page-1);
            $page_href = \Natty::url($command, $page_params);
        }

        // Page button class
        $page_class = array ('page', 'k-button');
        if ('#' == $page_href)
            $page_class[] = 'k-state-disabled';
        if ($data['current_page'] == $page)
            $page_class[] = 'k-state-active';

        $output .= ' ' . natty_render_anchor(array (
            '_data' => $page, 
            'href' => $page_href,
            'class' => $page_class,
        ));

    endfor;

    // Generating the Next button
    $output .= ' ' . natty_render_anchor(array (
        '_data' => 'Next',
        'href' => $next_href,
        'class' => $next_class,
    ));

    // Generating the Last button
    $output .= ' ' . natty_render_anchor(array (
        '_data' => 'Last',
        'href' => $last_href,
        'class' => $last_class,
    ));

    // End navigator
    $output .= '</div>';

    // End toolbar
    $output .= '</div>';
    
    return $output;
    
}

function natty_render_toolbar(array $rarray) {
    
    $rarray = array_merge(array (
        '_element' => 'div',
        '_data' => array (),
        'class' => array ()
    ), $rarray);
    
    // Push toolbar class
    $rarray['class'][] = 'n-toolbar';
    
    if ( isset ($rarray['_left']) ):
        $rarray['_data']['left'] = array (
            '_render' => 'element',
            '_element' => 'div',
            '_data' => $rarray['_left'],
            'class' => array ('n-fl-le')
        );
    endif;
    
    if ( isset ($rarray['_right']) ):
        $rarray['_data']['right'] = array (
            '_render' => 'element',
            '_element' => 'div',
            '_data' => $rarray['_right'],
            'class' => array ('n-fl-ri')
        );
    endif;
    
    return natty_render_element($rarray);
    
}

/**
 * Renders form widgets
 * @param array $rarray A renderable array of widget definition
 */
function natty_render_form_item($rarray) {
    return \Natty\Form\WidgetHelper::render($rarray);
}