var RegionFormHelper = RegionFormHelper || {};

(function(h) {
    
    h.$form = null;
    
    h.init = function() {
        
        this.$form = jQuery('#location-regionitem-form');
        this.$form.find('.form-item-type input')
                .change(RegionFormHelper.handleNatureChange)
                .filter(':checked')
                .trigger('change');
        
    };
    
    h.handleNatureChange = function() {
        
        var $this = jQuery(this);
        var val = $this.val();
        
        var $fi_cid = RegionFormHelper.$form.find('.form-item-cid');
        var $fi_sid = RegionFormHelper.$form.find('.form-item-sid');
        var $fi_fromPC = RegionFormHelper.$form.find('.form-item-fromPostCode');
        var $fi_tillPC = RegionFormHelper.$form.find('.form-item-tillPostCode');
        
        switch ( val ) {
            case 'c':
                $fi_cid.show();
                $fi_sid.hide();
                $fi_fromPC.hide();
                $fi_tillPC.hide();
                break;
            case 's':
                $fi_cid.show();
                $fi_sid.show();
                $fi_fromPC.hide();
                $fi_tillPC.hide();
                break;
            case 'p':
                $fi_cid.show();
                $fi_sid.show();
                $fi_fromPC.show();
                $fi_tillPC.show();
                break;
        }
        
    };
    
})(RegionFormHelper);

jQuery(document).ready(function() {
    
    RegionFormHelper.init();
    
});