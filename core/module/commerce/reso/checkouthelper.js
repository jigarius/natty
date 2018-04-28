/**
 * Checkout Helper
 */
var CheckoutHelper = CheckoutHelper || {};
(function(h, $) {
    
    h.$cartForm = null;
    
    h.init = function() {
        
        this.initCartForm();
        
    };
    
    h.initCartForm = function() {
        
        h.$cartForm = $('#commerce-cart-form');
        if ( 1 !== h.$cartForm.length )
            return;
        
    };
    
    jQuery(document).ready(function() {
        CheckoutHelper.init();
    });
    
})(CheckoutHelper, jQuery);