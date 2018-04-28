Natty.UI.initStatePicker = function($el) {
    
    var $form = $el.closest('form');
    
    // Natty options
    var nOptions = {
        source: Natty.url('ajax'),
        sourceData: {
            call: 'location--service-state',
            'do': 'read',
            cid: 0
        },
        dataValueField: 'sid',
    };
    
    // Determine country-picker
    var cpExpr = $el.attr('data-state-picker-country-picker');
    var $cpElem = [];
    if ( cpExpr ) {
        var $cpElem = $form.find(cpExpr);
        if ( 1 === $cpElem.length ) {
            nOptions.sourceData.cid = function() {
                return $cpElem.val() || $cpElem.attr('data-dropdown-selections');
            };
        }
    }
    
    // Init state-picker
    var oWidget = Natty.UI.initDropdown($el, nOptions);
    
    // Bind with country-picker
    if ( $cpElem.length ) {
        var cpWidget = $cpElem.data('kendoDropDownList');
        if ( cpWidget ) {
            oWidget.dataSource.read();
        }
        else {
            $cpElem.change(function() {
                oWidget.dataSource.read();
            });
        }
    }
    
};