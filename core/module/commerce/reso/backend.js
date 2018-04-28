var CarrierScopeHelper = CarrierScopeHelper || {};

(function(h) {
    
    h.$table = null;
    
    h.init = function() {
        
        this.$table = jQuery('#commerce-carrier-scope-list');
        if ( 1 != this.$table.length )
            return;
        
        this.$table.find('.prop-status').change(h.handleItemChange);
        
    };
    
    h.handleItemChange = function() {
        
        var $fi = jQuery(this);
        Natty.Func.ajax({
            data: {
                call: 'commerce--carrier-scope',
                cid: CarrierScopeHelper.$table.attr('data-cid'),
                type: $fi.attr('data-type'),
                id: $fi.attr('data-id'),
                status: $fi.val()
            },
            success: function(r) {
                console.log(r);
                if ( r._message ) {
                    Natty.Func.notify({
                        content: 'Data saved successfully.'
                    });
                }
            }
        });
        
    };
    
    jQuery(document).ready(function() {
        CarrierScopeHelper.init();
    });
    
})(CarrierScopeHelper);