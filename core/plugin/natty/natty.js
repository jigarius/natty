/**
 * Natty: The Natty Object
 * @fileOverview Declaration of the Natty JavaScript Object
 * @author JigaR Mehta | Greenpill Productions
 * @namespace Natty
 */
var Natty = window.Natty || {};

/**
 * Base path for Natty
 * @type string
 */
Natty.base = null;

/**
 * Error and Exception Handler
 * @parameter string message The message to be displayed
 * @parameter string reference The object/function which raised the error
 */
Natty.error = function(message, reference) {
    reference = reference || 'Natty';
    throw (reference + ': ' + message);
}

/**
 * Client-side Logger
 * @param mixed message
 * @parameter string reference The object/function which raised the message
 * @returns {undefined}
 */
Natty.log = function(message, reference) {
    reference = reference || 'Natty';
    message += ': ' + reference;
    if ( 'undefined' != typeof window.console )
        console.log(message);
}

Natty.readSetting = function (name, fallback) {
    if ( 'undefined' == typeof fallback )
        fallback = null;
    return ( 'undefined' == typeof Natty.settings[name] )
        ? fallback : Natty.settings[name];
}

Natty.require = function(path, base) {
    
};

Natty.url = function(command, data, options) {
    
    if ( 'undefined' == typeof data )
        data = {};
    data = jQuery.param(data)
    
    if ( 'undefined' == typeof options )
        options = {};
    
    var output = Natty.base;
    
    if ( 1 == Natty.readSetting('system--routeClean') ) {
        output += command + (data.length > 0 ? '?' : '') + data;
    }
    else {
        output += '?_command=' + command + (data.length > 0 ? '&' : '') + data;
    }
    
    return output;
    
};

/**
 * Client side cache
 */
Natty.Cache = Natty.Cache || {};

Natty.Cache.data = {};

/**
 * 
 * @param {type} bin
 * @param {type} key
 * @param {type} fallback
 * @returns {unresolved}
 */
Natty.Cache.read = function(bin, key, fallback) {
    
    fallback = fallback || null;
    
    Natty.Cache.createBin(bin);
    if ( 'undefined' === Natty.Cache.data[bin][key] )
        return fallback;
    
    return Natty.Cache.data[bin][key];
    
};

/**
 * 
 * @param {type} bin
 * @param {type} key
 * @param {type} data
 * @returns {unresolved}
 */
Natty.Cache.write = function(bin, key, data) {
    Natty.Cache.createBin(bin);
    Natty.Cache.data[bin][key] = data;
    return data;
};

/**
 * 
 * @param {type} bin
 * @param {type} key
 * @returns {undefined}
 */
Natty.Cache.delete = function(bin, key) {
    Natty.createBin(bin);
    delete Natty.Cache.data[bin][key];
};

/**
 * 
 * @param {type} bin
 * @returns {undefined}
 */
Natty.Cache.createBin = function(bin) {
    if ( 'undefined' === typeof Natty.Cache.data[bin] )
        Natty.Cache.data[bin] = {};
};

/**
 * 
 * @param {type} bin
 * @returns {undefined}
 */
Natty.Cache.destroyBin = function(bin) {
    delete Natty.Cache.data[bin];
};

/**
 * User-interface initialization scripts
 * ======
 */
Natty.UI = Natty.UI || {};

/**
 * Initializes uninitialized UI components within the given scope.
 * @param string $scope [optional] The DOM Element within which to initialize
 * the UI. Defaults to all elements in document.body.
 */
Natty.UI.init = function( scope ) {
    
    // Determine lookup scope
    scope = 'undefined' == typeof $scope
        ? document.body : scope;
    var $scope = jQuery(scope);
    
    // Look for elements with pending initializations
    $scope.find('[data-ui-init]').each(function() {
        var $el = jQuery(this);
        var widgets = $el.attr('data-ui-init').split(' ');
        for ( var i in widgets ) {
            var widget = widgets[i];
            var callback = 'init' + Natty.Func.toCamelCase(widget, true);
            if ( 'function' === typeof Natty.UI[callback] ) {
                Natty.UI[callback].call(null, $el);
            }
            else {
                Natty.log('Could not initialize unrecognized UI widget "' + widget + '"', 'Natty.UI');
            }
        }
        $el.removeAttr('data-ui-init');
    });
    
};

Natty.UI.initConfirmation = function($el) {
    
    var message = $el.attr('data-confirmation') || 'This action might be irreversible in nature. Are you sure you wish to continue?';
    $el.click(function() {
        
        if ( !confirm(message) )
            return false;
        
        if ( 'a' == this.nodeName.toLowerCase() ) {
            var href = this.getAttribute('href');
            var glue = href.indexOf('?') > -1 ? '&' : '?';
            href += glue + 'confirmed=1';
            setTimeout(function() {
                window.location = href;
            }, 250);
            return false;
        }
        
        return true;
        
    });
    
};

Natty.UI.initDropdown = function($el, options) {
    
    // Is it already initialized?
    if ( $el.data('kendoDropDownList') )
        return;
    
    // Default options
    var nOptions = Natty.Func.readPluginOptions($el, 'dropdown');
    
    // Overriding options
    options = jQuery.extend({
        dataValueField: 'id',
        dataTextField: 'name',
        filter: 'startswith',
        itemTemplate: null,
        serverFiltering: false,
        sourceData: {}
    }, nOptions, options);
    
    // Kendo UI options
    var kOptions = {
        autoBind: false,
        dataTextField: options.dataTextField,
        dataValueField: options.dataValueField,
        delay: 500,
        filter: options.filter,
        minLength: 1
    };
    
    if ( $el.attr('placeholder') )
        kOptions.optionLabel = $el.attr('placeholder');
    
    // Prepare data-source
    if ( options.source ) {
        kOptions.dataSource = {
            dataType: "jsonp",
            transport: {
                read: {
                    url: options.source,
                    type: 'POST',
                    data: options.sourceData
                }
            },
            schema: {
                data: function(r) {
                    var r = eval('(' + r + ')');
                    return r.data;
                }
            },
            serverFiltering: options.serverFiltering ? true : false
        };
    }
    
    // Standard text completion
    var oWidget = $el.kendoDropDownList(kOptions).data('kendoDropDownList');
    
    // Determine values
    if ( !options.selections ) {
        options.selections = [];
    }
    else {
        options.selections = $el.attr('multiple')
            ? options.selections.split(/[;][\s]?/g) : options.selections = [options.selections];
    }
    
    // Load pre-selection data
    if ( options.selections.length > 0 ) {
        oWidget.value(options.selections);
    }
    
    return oWidget;
    
};

Natty.UI.initForm = function($el) {
    return;
    // Read all headings
    var $fset_coll = $el.find('fieldset');
    var $menu = jQuery('<ul></ul>');
    
    $fset_coll.each(function() {
        
        var $fset = jQuery(this);
        var legend = $fset.find('legend:first').text();
        
        if ( !legend )
            return;
        
        var $fset_cont = jQuery('<div />');
        $fset.before($fset_cont);
        $fset_cont.append($fset);
        
        $menu.append('<li>' + legend + '</li>');
        
    });
    
    $menu.find('li:first').addClass('k-state-active');
    
    $el.prepend($menu);
    $el.kendoTabStrip({
        tabPosition: 'left'
    });
    
};

Natty.UI.initContextMenuHost = function($el) {
    
    // Find popup object
    var selector = '.context-menu-popup';
    var $popup = $el.find(selector).first();
    if ( 1 != $popup.length )
        return;
    
    // Prepare popup object
    $popup
            .addClass('context-menu-popup')
            .attr('tabindex', -1)
            .css({
                position: 'absolute',
                display: 'none'
            })
            .blur(function() {
                var $popup = jQuery(this);
                setTimeout(function() {
                    $popup.hide();
                }, 200);
            });
    
    // Create data pocket
    var _d = $el.data('natty') || {};
    $el.data('natty', _d);
    
    // Bind event
    $el.contextmenu(function(e) {
                    if ( !e.ctrlKey )
                        e.preventDefault();
                })
                .mousedown(function(e) {
                    if ( 2 === e.button && !e.ctrlKey ) {
                        e.preventDefault();
                        $popup.show().css({
                            left: e.clientX  + jQuery(window).scrollLeft(),
                            top: e.clientY + jQuery(window).scrollTop() - $popup.height()
                        }).focus();
                    }
                });
    
}

Natty.UI.initContextMenuPopup = function($el) {
    
    // Bind jQuery menu and attach it to the DOM
    var options = {
        animation: {
            open: {effects: 'fadeIn'}
        },
        orientation: 'vertical',
        popupCollision: 'flip'
    };

    $el.kendoMenu(options);
    
    // Add a trigger
    var $trigger = jQuery('<a href="" class="context-menu-trigger k-icon k-i-hbars"></a>')
            .click(function() {
                $el.show().css({
                    left: $trigger.offset().left - $el.width() - 4,
                    top: $trigger.offset().top - $el.height() + $trigger.height()
                }).focus();
                return !1;
            });
    $el.before($trigger);
    
};

Natty.UI.initSystemExistingFormValue = function($el) {
    
    $el.find('.button-delete').click(function() {
        
        var checkbox = $el.find('input[type="checkbox"]')[0];
        
        if ( checkbox.checked ) {
            checkbox.checked = false;
            $el.removeClass('n-state-disabled');
        }
        else {
            checkbox.checked = true;
            $el.addClass('n-state-disabled');
        }
        
    });
    
};

Natty.UI.initAutocomplete = function($el) {
    
    var optionAttrNames = ['source', 'data-text-field'];
    var options = {
        dataTextField: 'name',
    };
    for ( var i in optionAttrNames ) {
        var attrName = optionAttrNames[i];
        var attrKey = Natty.Func.toCamelCase(attrName, false);
        options[attrKey] = $el.attr('data-autocomplete-' + attrName) || options[attrKey];
        $el.removeAttr('data-autocomplete-' + attrName);
    }
    
    // Prepare data-source
    var dataSource = {
        autoBind: false,
        dataType: "jsonp",
        transport: {
            read: {
                url: options.source,
                type: 'POST',
            }
        },
        schema: {
            data: function(r) {
                return eval('(' + r + ')');
            }
        },
        serverFiltering: true,
    };
    
    // Standard text completion
    $el.kendoAutoComplete({
        dataSource: dataSource,
        dataTextField: options.dataTextField,
        delay: 500,
        minLength: 1,
    });
    
    return $el.data('kendoAutoComplete');
    
};

Natty.UI.initMultiselect = function($el, options) {
    
    // Determine options
    var nOptions = Natty.Func.readPluginOptions($el, 'multiselect');
    options = jQuery.extend({
        autoClose: false,
        dataValueField: 'id',
        dataTextField: 'name',
        filter: 'startswith',
        maxSelectedItems: null,
        minLength: 0,
        itemTemplate: null,
        tagMode: 'multiple',
        serverFiltering: true,
        sourceData: {}
    }, nOptions, options);
    
    // Is it a multiselect?
    options.multiple = $el.attr('multiple');
    
    // Determine maximum allowed selections
    if ( !options.multiple ) {
        options.maxSelectedItems = 1;
        $el.addClass('n-singleselect');
    }
    
    // Prepare template
    if ( options.itemTemplate )
        options.itemTemplate = kendo.template(options.itemTemplate);
    
    var kOptions = {
        autoBind: false,
        autoClose: options.autoClose,
        dataValueField: options.dataValueField,
        dataTextField: options.dataTextField,
        delay: 500,
        filter: options.filter,
        itemTemplate: options.itemTemplate,
        maxSelectedItems: options.maxSelectedItems,
        minLength: options.minLength,
        tagMode: options.tagMode
    };
    
    // Prepare data-source
    if ( options.source ) {
        kOptions.dataSource = new kendo.data.DataSource({
            dataType: "jsonp",
            transport: {
                read: {
                    url: options.source,
                    type: 'POST',
                    data: options.sourceData
                }
            },
            schema: {
                data: function(r) {
                    var r = eval('(' + r + ')');
                    return r.data;
                }
            },
            serverFiltering: options.serverFiltering
        });
    }
    
    // Standard text completion
    var oWidget = $el.kendoMultiSelect(kOptions).data('kendoMultiSelect');
    
    // Determine values
    options.value = $el.attr('data-dropdown-selections') || '';
    if ( 0 === options.value.length ) {
        options.value = [];
    }
    else {
        options.value = ( options.multiple )
            ? options.value.split(/[;][\s]?/g) : options.value = [options.value];
    }
    
    // Load pre-selection data
    if ( options.value.length > 0 ) {
        
        oWidget.value(options.value);
        console.log('Test case');
        return;
        
        // Disable the widget and show loader until values are loaded
        options.readonlyInitially = $el.is('[readonly]');
        oWidget.readonly(true);
        oWidget._loading.removeClass('k-loading-hidden');
        
        jQuery.ajax({
            url: options.source,
            type: 'post',
            context: oWidget,
            data: {
                filter: {
                    logic: 'and',
                    filters: [
                        {
                            field: options.dataValueField,
                            operator: 'equals',
                            value: options.value
                        }
                    ]
                }
            },
            dataType: 'json',
            success: function(r) {
                
                // Add values to datasource to avoid a repeat request
                var ds = this.dataSource;
                ds.data(r);
                
                // Assign values to multiselect
                this.value(options.value);
                
                // Disable readonly mode
                if ( !options._readonlyInitially ) {
                    this.readonly(false);
                }
                
            }
        });
    }
    
    return oWidget;
    
};

Natty.UI.initDatePicker = function($el) {
    
    // See if the browser supports date pickers
    if ( 'date' == $el.attr('type') )
        return;
    
    // Clone the original field
    $el.removeAttr('natty-widget');
    if ( !$el.attr('id') ) {
        $el.attr('id', Natty.Func.domid());
    }
    var id = $el.attr('id');
    var $clone = $el.clone()
            .attr('aria-cloneof', id)
            .removeAttr('id')
            .removeAttr('name');

    // Replace the original field
    $el.hide().before($clone);

    // Initialize the datepicker
    $clone.kendoDatePicker({
        format: "yyyy-MM-dd",
        parseFormats: ["yyyy-MM-dd"],
        change: function() {
            // Populate the original hidden field
            var id = this.element.attr('aria-cloneof');
            var $ofield = jQuery('#' + id);
            var value = this.value();
            value = Natty.Func.dateToString(value);
            $ofield.val(value).change();
        }
    });
    
};

Natty.UI.initTimePicker = function( $el ) {
    
    // See if the browser supports date pickers
    if ( 'time' == $el.attr('type') )
        return;
    
    // Clone the original field
    $el.removeAttr('natty-widget');
    if ( !$el.attr('id') ) {
        $el.attr('id', Natty.Func.domid());
    }
    var id = $el.attr('id');
    var $clone = $el.clone()
            .attr('aria-cloneof', id)
            .removeAttr('id')
            .removeAttr('name');

    // Replace the original field
    $el.hide().before($clone);

    // Initialize the datepicker
    $clone.kendoTimePicker({
        format: "hh:mm tt",
        parseFormats: ["HH:mm"],
        change: function() {
            // Populate the original hidden field
            var id = this.element.attr('aria-cloneof');
            var $ofield = jQuery('#' + id);
            var value = this.value();
            value = Natty.Func.timeToString(value);
            $ofield.val(value).change();
        }
    });
    
};

Natty.UI.initDatetimePicker = function( $el ) {
    
    // See if the browser supports date pickers
    if ( 'datetime' == $el.attr('type') )
        return;
    
    // Clone the original field
    $el.removeAttr('natty-widget');
    if ( !$el.attr('id') ) {
        $el.attr('id', Natty.Func.domid());
    }
    var id = $el.attr('id');
    var $clone = $el.clone()
            .attr('aria-cloneof', id)
            .removeAttr('id')
            .removeAttr('name');

    // Replace the original field
    $el.hide().before($clone);

    // Initialize the datepicker
    $clone.kendoDateTimePicker({
        format: "yyyy-MM-dd hh:mm tt",
        parseFormats: ["yyyy-MM-dd HH:mm"],
        change: function() {
            // Populate the original hidden field
            var id = this.element.attr('aria-cloneof');
            var $ofield = jQuery('#' + id);
            var value = this.value();
            value = Natty.Func.dateToString(value) + ' ' + Natty.Func.timeToString(value);
            $ofield.val(value).change();
        }
    });
    
};

Natty.UI.initColorPicker = function($el) {
    
    // If the browser supports color pickers, do nothing
    if ('color' === $el.attr('type'))
        return;
    
    $el.kendoColorPicker({

    });
    
};

Natty.UI.initRte = function($el) {
    
    var rte = $el.attr('data-ui-rte') || 'ckeditor';
    var callback = 'initRte' + Natty.Func.toCamelCase(rte, 1);
    
    if ( 'undefined' == typeof Natty.UI[callback] ) {
        Natty.log('RTE "' + rte + '" is not supported!');
        return;
    }
    
    Natty.UI[callback]($el, {
        toolbar: $el.attr('data-rte-toolbar') || 'basic'
    });
    
};

Natty.UI.initRteCkeditor = function($el) {
    
    var tarea = $el[0];
    CKEDITOR.replace(tarea, {
        autoUpdateElement: true,
        height: $el.height()
    });
    
};

Natty.UI.initTooltip = function($el) {
    
    if (0 === $el.attr('title').length)
        return;
    
    $el.kendoTooltip({
//        show: function(e) {
//        }
    });
    
};

/**
 * Utility Functions
 * ======
 */
Natty.Func = Natty.Func || {};

/**
 * Makes an AJAX request with the said options
 * @param object Options for the request
 * @return jQuery promise object.
 */
Natty.Func.ajax = function(options) {
    
    options = jQuery.extend({
        url: Natty.url('ajax'),
        type: 'post',
        dataType: 'json'
    }, options);
    
    return jQuery.ajax(options);
    
};

/**
 * Reads options for a given plugin from the element's attributes.
 * @param jQuery $el
 * @param string Plugin
 * @returns object An object of options found
 */
Natty.Func.readPluginOptions = function($el, plugin) {
    
    // Determine callback options
    var options = {},
        el = $el.get(0),
        prefix = 'data-' + plugin + '-';
    for ( var aI in el.attributes  ) {
        var attr = el.attributes[aI];
        if ( !attr.specified )
            continue;
        if ( 0 !== attr.name.indexOf(prefix) )
            continue;
        var oN = attr.name.replace(prefix, '');
        oN = Natty.Func.toCamelCase(oN);
        options[oN] = attr.value;
    }
    return options;
    
};

/**
 * JQuery plugin call overloader; Calls a JQuery Plugin method in context to
 * a JQuery Object, using the arguments sent as Argument 2
 * @param methods The methods of the plugin in an object literal
 * @param args The arguments to be passed to the method
 * @return Value returned by the called method or the JQuery object itself
 */
Natty.Func.jQueryPluginOverloader = function(methods, args) {
    
    // Validating methods
    if ( !methods || !methods._ns )
        Natty.error('I expect Argument 1 to be an object', 'jQueryPluginOverloader');
    
    // Validating arguments
    args = args || [];
    
    // Isolate bulk calls
    if ( this.length > 1 ) {
        return this.each(function() {
            var $this = $(this);
            $this[methods.Nattys.split('.').pop()].apply($this, args);
        });
    }

    var func = false;
    
    // Call to a plugin method
    if ( methods[args[0]] ) {
        func = Array.prototype.shift.call(args);
    }
    // Call to constructor by default
    else {
        func = 'constructor';
    }
    if ( methods[func] ) {
        return methods[func].apply(this, args);
    }
    
    Natty.error('Call to un-defined method ' + func + '.' + func + '()', methods.Natty);
    
}

/**
 * Converts a date to database friendly format
 * @param Date date
 * @returns string|false
 */
Natty.Func.dateToString = function( date ) {
    
    var output = false;
    
    try {
        output = date.getFullYear() 
                + '-' + ( '00' + (date.getMonth()+1) ).slice(-2)
                + '-' + ( '00' + date.getDate() ).slice(-2);
    }
    catch (e) {}
    
    return output;
    
}

Natty.Func.timeToString = function( date ) {
    
    var output = false;
    
    try {
        output = ( '00' + date.getHours() ).slice(-2)
                + ':' + ( '00' + date.getMinutes() ).slice(-2);
    }
    catch (e) {}
    
    return output;
    
}

/**
 * Returns a random number between min and max
 * @param int min
 * @param int max
 * @returns Number
 */
Natty.Func.rand = function(min, max) {
    
    if ( 'undefined' == typeof min || 'undefined' == typeof max )
        Natty.error('Expected argument 1 and 2 to be numbers!');
    
    return Math.floor((Math.random()*(max-min))+1) + min;
    
}

/**
 * Returns a unique DOM ID
 * @returns String
 */
Natty.Func.domid = function() {
    
    Natty.Func.uniqidIncrement = Natty.Func.uniqidIncrement || 0;
    var time = (new Date()).getTime();
    
    return 'domid-' + time + '-' + (++Natty.Func.uniqidIncrement);
    
}

Natty.Func.toCamelCase = function(string, pascal) {
    
    if ( !string )
        return;
    pascal = pascal || false;
    
    var parts = string.split(/\W/);
    for ( var i in parts ) {
        if ( 0 == i && false === pascal ) {
            continue;
        }
        parts[i] = parts[i].charAt(0).toUpperCase() + parts[i].substr(1);
    }
    
    return parts.join('');
    
};

Natty.Func.notify = function(message) {
    
    if ( 'string' === typeof message )
        message = {content: message};
    if ( 'object' !== typeof message )
        return;
    
    message = jQuery.extend({
        content: '',
        timeout: 10000
    }, message);
    
    // Create a notification container
    var $cont = jQuery('#n-notifications');
    if ( 0 === $cont.length ) {
        $cont = jQuery('<div id="n-notifications"></div>').appendTo(document.body);
    }
    
    // Render the message
    var $message = jQuery('<div class="item">' + message.content + '</div>')
            .appendTo($cont);
    
    // Setup timeout
    if ( false !== message.timeout && message.timeout > 0 ) {
        setTimeout(function() {
            $message.remove();
        }, message.timeout);
    }
    
};

/**
 * Object definitions
 */
Natty.Prototype = {};

// Register callback
jQuery.noConflict();
jQuery(document).ready(function() {
    Natty.UI.init();
});

/**
 * Event API
 **/
Natty.Event = {};

Natty.Event.attach = function($context, options) {
    
    // Determine context object
    if ( 'string' === typeof $context )
        $context = jQuery($context);
    if ( 0 === $context.length )
        return;
    
};