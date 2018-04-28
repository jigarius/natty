Natty.UI.initRegionPicker = function($el) {
    
    var $form = $el.closest('form');
    
    // Natty options
    var nOptions = {
        source: Natty.url('ajax'),
        sourceData: {
            call: 'location--service-region',
            'do': 'read',
            sid: 0
        },
        dataValueField: 'rid',
    };
    
    // Determine state-picker
    var spExpr = $el.attr('data-region-picker-state-picker');
    var $spElem = [];
    if ( spExpr ) {
        var $spElem = $form.find(spExpr);
        if ( 1 === $spElem.length ) {
            nOptions.sourceData.sid = function() {
                return $spElem.val() || $spElem.attr('data-dropdown-selections');
            };
        }
    }
    
    // Init region-picker
    var oWidget = Natty.UI.initDropdown($el, nOptions);
    
    // Bind with state-picker
    if ( $spElem.length ) {
        var spWidget = $spElem.data('kendoDropDownList');
        if ( spWidget ) {
            spWidget.bind('change', function() {
                oWidget.dataSource.read();
                console.log(oWidget);
            });
        }
        else {
            $spElem.change(function() {
                oWidget.data('kendoDropDownList').dataSource.read();
            });
        }
    }
    
};